<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

/**
 * Centralized availability rules for customer table allocation.
 *
 * The current schema has no per-table capacity column, so capacity is kept
 * as a business rule (4 guests per table) until the cafe defines capacities.
 */
class TableAvailability
{
    public const GUESTS_PER_TABLE = 4;
    public const RESERVATION_HOURS = 2;
    public const PAYMENT_HOLD_MINUTES = 30;

    /**
     * Return enough available table IDs in a single room for the guest count.
     * When $lock is true, candidate table rows are locked for the surrounding
     * database transaction so concurrent checkouts cannot claim the same rows.
     */
    public function selectTableIds(string $room, string $date, string $time, int $guests, bool $lock = false): array
    {
        $required = max(1, (int) ceil($guests / self::GUESTS_PER_TABLE));
        $start = Carbon::parse($date . ' ' . $time);
        $end = $start->copy()->addHours(self::RESERVATION_HOURS);

        $tablesQuery = DB::table('meja')
            ->where('ruangan', $room)
            ->orderBy('id_meja');

        if ($lock) {
            $tablesQuery->lockForUpdate();
        }

        $tables = $tablesQuery->get(['id_meja']);
        if ($tables->isEmpty()) {
            return [];
        }

        $ids = $tables->pluck('id_meja')->all();
        $fromDate = $start->copy()->subDay()->toDateString();
        $toDate = $start->copy()->addDay()->toDateString();

        $bookings = DB::table('reservasi_meja')
            ->join('reservasi', 'reservasi_meja.id_reservasi', '=', 'reservasi.id_reservasi')
            ->leftJoin('transaksi', 'transaksi.id_reservasi', '=', 'reservasi.id_reservasi')
            ->whereIn('reservasi_meja.id_meja', $ids)
            ->whereBetween('reservasi.tanggal', [$fromDate, $toDate])
            ->select(
                'reservasi_meja.id_meja',
                'reservasi.tanggal',
                'reservasi.waktu',
                'reservasi.status_kehadiran',
                'transaksi.status as status_transaksi',
                'transaksi.created_at as transaksi_created_at'
            )
            ->get();

        $blocked = [];
        foreach ($bookings as $booking) {
            $status = strtolower((string) ($booking->status_transaksi ?? ''));
            $attendance = strtolower((string) ($booking->status_kehadiran ?? ''));

            if (in_array($status, ['gagal', 'batal', 'expired'], true)
                || $attendance === 'dibatalkan') {
                continue;
            }

            // Pending invoices hold tables only during the 30-minute Xendit window.
            if ($status === 'menunggu') {
                if (empty($booking->transaksi_created_at)
                    || Carbon::parse($booking->transaksi_created_at)->addMinutes(self::PAYMENT_HOLD_MINUTES)->lessThanOrEqualTo(now())) {
                    continue;
                }
            } elseif (!in_array($status, ['tervalidasi', 'selesai'], true)) {
                // Unknown or missing transaction status does not reserve a table.
                continue;
            }

            if (empty($booking->tanggal) || empty($booking->waktu)) {
                continue;
            }

            $bookingStart = Carbon::parse($booking->tanggal . ' ' . $booking->waktu);
            $bookingEnd = $bookingStart->copy()->addHours(self::RESERVATION_HOURS);

            if ($start->lt($bookingEnd) && $end->gt($bookingStart)) {
                $blocked[] = $booking->id_meja;
            }
        }

        $blocked = array_unique($blocked);
        $available = array_values(array_diff($ids, $blocked));

        return count($available) >= $required ? array_slice($available, 0, $required) : [];
    }
}
