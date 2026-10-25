<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Centralized availability rules for customer table allocation.
 *
 * Table capacity is configured per table in the admin data-meja page.
 */
class TableAvailability
{
    public const RESERVATION_HOURS = 2;
    public const PAYMENT_HOLD_MINUTES = 30;

    /**
     * Return enough available table IDs in a single room for the guest count.
     * When $lock is true, candidate table rows are locked for the surrounding
     * database transaction so concurrent checkouts cannot claim the same rows.
     */
    public function selectTableIds(string $room, string $date, string $time, int $guests, bool $lock = false): array
    {
        if ($guests < 1) {
            return [];
        }

        $availableIds = $this->availableTableIds($room, $date, $time, $lock);
        if (empty($availableIds)) {
            return [];
        }

        // Prefer a combination with less unused capacity by considering smaller
        // available tables first. All selected tables remain in the same room.
        $tables = DB::table('meja')
            ->whereIn('id_meja', $availableIds)
            ->orderBy('kapasitas')
            ->orderBy('id_meja')
            ->get(['id_meja', 'kapasitas']);

        $selected = [];
        $capacity = 0;
        foreach ($tables as $table) {
            $selected[] = $table->id_meja;
            $capacity += (int) $table->kapasitas;

            if ($capacity >= $guests) {
                return $selected;
            }
        }

        return [];
    }

    public function availableTableIds(string $room, string $date, string $time, bool $lock = false): array
    {
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
        return array_values(array_diff($ids, $blocked));
    }
}
