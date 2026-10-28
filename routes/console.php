<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
    

Artisan::command('reservasi:auto-cancel-unconfirmed', function () {
    $cutoff = now()->copy()->addDays(3);

    $reservations = \Illuminate\Support\Facades\DB::table('reservasi')
        ->join('transaksi', 'transaksi.id_reservasi', '=', 'reservasi.id_reservasi')
        ->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(transaksi.status)'), ['tervalidasi', 'selesai'])
        ->whereNotIn(\Illuminate\Support\Facades\DB::raw("LOWER(COALESCE(reservasi.status_kehadiran, 'belum_hadir'))"), ['hadir', 'dibatalkan'])
        ->whereDate('reservasi.tanggal', '>=', now()->toDateString())
        ->select('reservasi.id_reservasi', 'reservasi.tanggal', 'reservasi.waktu')
        ->distinct()
        ->get();

    foreach ($reservations as $reservation) {
        $waktuReservasi = \Illuminate\Support\Carbon::parse($reservation->tanggal . ' ' . $reservation->waktu);

        if ($waktuReservasi->lessThanOrEqualTo(now()) || $waktuReservasi->greaterThan($cutoff)) {
            continue;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($reservation) {
            \Illuminate\Support\Facades\DB::table('reservasi')
                ->where('id_reservasi', $reservation->id_reservasi)
                ->whereNotIn(\Illuminate\Support\Facades\DB::raw("LOWER(COALESCE(status_kehadiran, 'belum_hadir'))"), ['hadir', 'dibatalkan'])
                ->update(['status_kehadiran' => 'dibatalkan', 'updated_at' => now()]);

            \Illuminate\Support\Facades\DB::table('transaksi')
                ->where('id_reservasi', $reservation->id_reservasi)
                ->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['tervalidasi', 'selesai'])
                ->update(['status' => 'batal', 'updated_at' => now()]);
        });
    }

    $this->info('Reservasi belum dikonfirmasi yang memasuki batas 3 hari telah diproses.');
})->purpose('Batalkan otomatis reservasi yang belum dikonfirmasi 3 hari sebelum kedatangan');
