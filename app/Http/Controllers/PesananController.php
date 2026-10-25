<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf; // Pastikan ini ada di bagian paling atas controller

class PesananController extends Controller
{
    public function riwayat()
    {
        // 1. Ambil ID Pelanggan dari session login
        $idPelanggan = session('id_pelanggan');

        if (!$idPelanggan) {
            return redirect()->route('login')->with('gagal', 'Silakan login terlebih dahulu.');
        }

        $idPelanggan = (int) $idPelanggan;

        // ========================================================
        // 2. QUERY TRANSAKSI AKTIF / BERJALAN (Termasuk status 'menunggu')
        // ========================================================
        $pesananSukses = DB::table('transaksi')
            ->leftJoin('reservasi', 'reservasi.id_reservasi', '=', 'transaksi.id_reservasi')
            ->where('transaksi.id_pelanggan', $idPelanggan)
            ->whereIn(DB::raw('LOWER(transaksi.status)'), ['menunggu', 'tervalidasi', 'selesai'])
            ->select(
                'transaksi.*',
                'reservasi.tanggal as tanggal_reservasi',
                'reservasi.waktu as waktu_reservasi',
                'reservasi.status_kehadiran',
                'reservasi.id_reservasi as reservasi_id'
            )
            ->orderBy('transaksi.id_transaksi', 'desc')
            ->get();

        $tanggalHariIni = now()->startOfDay();
        foreach ($pesananSukses as $transaksi) {
            $tanggalReservasi = $transaksi->tanggal_reservasi
                ? \Illuminate\Support\Carbon::parse($transaksi->tanggal_reservasi)->startOfDay()
                : null;
            $transaksi->aksi_reservasi_tersedia = $tanggalReservasi
                && $tanggalReservasi->greaterThanOrEqualTo($tanggalHariIni)
                && $tanggalReservasi->lessThanOrEqualTo($tanggalHariIni->copy()->addDays(3))
                && !in_array(strtolower((string) $transaksi->status_kehadiran), ['dibatalkan', 'hadir'], true);
        }

        // dd($pesananSukses);

        // Ambil data detail item menu untuk setiap transaksi aktif
        foreach ($pesananSukses as $transaksi) {
            $transaksi->items = DB::table('detail_pesanan')
                ->join('menu', 'detail_pesanan.id_menu', '=', 'menu.id_menu')
                ->where('detail_pesanan.id_transaksi', $transaksi->id_transaksi)
                ->select('detail_pesanan.qty', 'menu.nama_menu', 'menu.harga', 'menu.kategori', 'detail_pesanan.catatan')
                ->get();
        }

        // ========================================================
        // 3. QUERY TRANSAKSI GAGAL / BATAL
        // ========================================================
        $pesananGagal = DB::table('transaksi')
            ->leftJoin('reservasi', 'reservasi.id_reservasi', '=', 'transaksi.id_reservasi')
            ->where('transaksi.id_pelanggan', $idPelanggan)
            ->whereIn(DB::raw('LOWER(transaksi.status)'), ['batal', 'gagal', 'expired'])
            ->select(
                'transaksi.*',
                'reservasi.tanggal as tanggal_reservasi',
                'reservasi.waktu as waktu_reservasi',
                'reservasi.status_kehadiran',
                'reservasi.id_reservasi as reservasi_id'
            )
            ->orderBy('transaksi.id_transaksi', 'desc')
            ->get();

        // Ambil data detail item menu untuk setiap transaksi gagal
        foreach ($pesananGagal as $gagal) {
            $gagal->items = DB::table('detail_pesanan')
                ->join('menu', 'detail_pesanan.id_menu', '=', 'menu.id_menu')
                ->where('detail_pesanan.id_transaksi', $gagal->id_transaksi)
                ->select('detail_pesanan.qty', 'menu.nama_menu', 'detail_pesanan.catatan')
                ->get();
        }

        // 4. Kirim data ke view
        return view('User.riwayat-pesanan', compact('pesananSukses', 'pesananGagal'));
    }


    public function konfirmasiKehadiranPelanggan($idTransaksi)
    {
        return $this->ubahStatusReservasiPelanggan($idTransaksi, 'hadir');
    }

    public function batalkanReservasiPelanggan($idTransaksi)
    {
        return $this->ubahStatusReservasiPelanggan($idTransaksi, 'dibatalkan');
    }

    private function ubahStatusReservasiPelanggan($idTransaksi, string $aksi)
    {
        $idPelanggan = session('id_pelanggan');
        if (!$idPelanggan) {
            return redirect()->route('login')->with('gagal', 'Silakan login terlebih dahulu.');
        }

        $transaksi = DB::table('transaksi')
            ->where('id_transaksi', $idTransaksi)
            ->where('id_pelanggan', $idPelanggan)
            ->first();

        if (!$transaksi || !$transaksi->id_reservasi) {
            return redirect()->route('pesanan.riwayat')->with('gagal', 'Data reservasi tidak ditemukan.');
        }

        $reservasi = DB::table('reservasi')
            ->where('id_reservasi', $transaksi->id_reservasi)
            ->where('id_pelanggan', $idPelanggan)
            ->first();

        if (!$reservasi) {
            return redirect()->route('pesanan.riwayat')->with('gagal', 'Reservasi tidak ditemukan.');
        }

        $tanggalReservasi = \Illuminate\Support\Carbon::parse($reservasi->tanggal)->startOfDay();
        $hariIni = now()->startOfDay();

        if ($tanggalReservasi->lt($hariIni) || $tanggalReservasi->gt($hariIni->copy()->addDays(3))) {
            return redirect()->route('pesanan.riwayat')->with('gagal', 'Konfirmasi kehadiran atau pembatalan hanya tersedia mulai 3 hari sebelum tanggal reservasi hingga hari reservasi.');
        }

        if (strtolower((string) $reservasi->status_kehadiran) === 'dibatalkan' || strtolower((string) $transaksi->status) === 'batal') {
            return redirect()->route('pesanan.riwayat')->with('gagal', 'Reservasi ini sudah dibatalkan. Konfirmasi kehadiran tidak dapat dilakukan.');
        }

        if ($aksi === 'dibatalkan') {
            if (strtolower((string) $reservasi->status_kehadiran) === 'hadir') {
                return redirect()->route('pesanan.riwayat')->with('gagal', 'Reservasi yang sudah dikonfirmasi hadir tidak dapat dibatalkan melalui fitur ini.');
            }

            DB::transaction(function () use ($reservasi, $transaksi) {
                DB::table('reservasi')
                    ->where('id_reservasi', $reservasi->id_reservasi)
                    ->update(['status_kehadiran' => 'dibatalkan', 'updated_at' => now()]);

                DB::table('transaksi')
                    ->where('id_reservasi', $reservasi->id_reservasi)
                    ->whereIn(DB::raw('LOWER(status)'), ['menunggu', 'tervalidasi', 'selesai'])
                    ->update(['status' => 'batal', 'updated_at' => now()]);
            });

            return redirect()->route('pesanan.riwayat')->with('success', 'Reservasi berhasil dibatalkan.');
        }

        DB::table('reservasi')
            ->where('id_reservasi', $reservasi->id_reservasi)
            ->update(['status_kehadiran' => 'hadir', 'updated_at' => now()]);

        return redirect()->route('pesanan.riwayat')->with('success', 'Kehadiran Anda berhasil dikonfirmasi.');
    }

    public function downloadNota($id_transaksi)
    {
        $idPelanggan = session('id_pelanggan');
        if (!$idPelanggan) {
            return redirect()->route('login');
        }

        // 1. Ambil data transaksi milik user ini dari database
        $transaksi = DB::table('transaksi')
            ->where('id_transaksi', $id_transaksi)
            ->where('id_pelanggan', $idPelanggan)
            ->first();

        if (!$transaksi) {
            return redirect()->back()->with('gagal', 'Transaksi tidak ditemukan.');
        }

        // Blokir unduhan jika statusnya masih menunggu konfirmasi
        if (strtolower($transaksi->status) == 'menunggu') {
            return redirect()->back()->with('gagal', 'Nota belum bisa diunduh sebelum dikonfirmasi admin.');
        }

        // 2. Ambil data reservasi pendukung (meja/ruangan) berdasarkan id_reservasi yang ada di transaksi
        $reservasi = DB::table('reservasi')->where('id_reservasi', $transaksi->id_reservasi)->first();
        $daftarMeja = collect();
        $meja = null;
        $ruangan = null;

        if ($reservasi) {
            $daftarMeja = DB::table('reservasi_meja')
                ->join('meja', 'reservasi_meja.id_meja', '=', 'meja.id_meja')
                ->where('reservasi_meja.id_reservasi', $transaksi->id_reservasi)
                ->orderBy('meja.kode_meja')
                ->get(['meja.kode_meja', 'meja.ruangan']);

            $meja = $daftarMeja->pluck('kode_meja')->implode(', ');
            $ruangan = $daftarMeja->pluck('ruangan')->unique()->implode(', ');
        }

        // 3. Ambil data menu yang dibeli dari tabel detail_pesanan (bukan session keranjang)
        $items = DB::table('detail_pesanan')
            ->join('menu', 'detail_pesanan.id_menu', '=', 'menu.id_menu')
            ->where('detail_pesanan.id_transaksi', $id_transaksi)
            ->select('detail_pesanan.qty', 'menu.nama_menu', 'menu.harga')
            ->get();

        // Mapping agar struktur variabelnya mirip dengan array $pesanan milikmu
        $pesanan = [];
        foreach ($items as $item) {
            $pesanan[] = [
                'nama'  => $item->nama_menu,
                'harga' => $item->harga,
                'qty'   => $item->qty,
            ];
        }

        $totalBayar = $transaksi->total;

        // 4. Render ke view PDF menggunakan variabel yang mirip dengan fungsi detailTransaksi milikmu
        $pdf = Pdf::loadView('User.nota-pdf', compact(
            'transaksi',
            'reservasi',
            'meja',
            'ruangan',
            'pesanan',
            'totalBayar'
        ));

        return $pdf->download('Nota-Kopi-satu-' . $id_transaksi . '.pdf');
    }


}