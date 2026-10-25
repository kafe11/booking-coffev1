<?php

namespace App\Http\Controllers;

use App\Models\pelangganModel;
use App\Models\reservasi;
use App\Models\detailPesanan;
use App\Models\Transaksi;
use App\Models\Meja;
use App\Models\ReservasiMeja;
use App\Models\admin\menuModel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Support\TransactionId;

use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;


class reservasiController extends Controller
{
    // xendit
    public function __construct()
    {
        $apiKey = (string) config('services.xendit.api_key');
        if ($apiKey !== '') {
            Configuration::setXenditKey($apiKey);
        }
    }

    // Download Transaksi (PDF)
    public function downloadTransaksi($id)
    {
        // Cek apakah dompdf tersedia
        if (!class_exists('Barryvdh\\DomPDF\\Facade\\Pdf')) {
            return response('Fitur download PDF belum tersedia. Silakan install barryvdh/laravel-dompdf.', 501);
        }

        $userId = session('id_pelanggan');
        $cartKey = 'keranjang_' . $userId;

        $reservasi = Reservasi::findOrFail($id);
        $data = Session::get('dataReservasi');
        $meja = Session::get('mejaDipilih');
        $pesanan = Session::get($cartKey, []);

        $totalHarga = 0;
        if ($pesanan) {
            foreach ($pesanan as $item) {
                $totalHarga += $item['harga'] * $item['qty'];
            }
        }

        // dd($pesanan);

        // $pajak = $totalHarga * 0.1;
        $totalBayar = $totalHarga;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('User.detail-transaksi-pdf', compact('data', 'meja', 'pesanan', 'totalHarga', 'totalBayar', 'reservasi'));
        return $pdf->download('transaksi-' . $id . '.pdf');
    }


    public function showResevasi()
    {
        if (session('id_pelanggan') === null) {
            return redirect()->route('login')->with('success', 'untuk melakukan reservasi harap login terlebih dahulu!');
        } else {
            // Mengambil Data Session
            $id_pelanggan = session('id_pelanggan');

            //  WAJIB: definisikan cartKey
            $cartKey = 'keranjang_' . $id_pelanggan;

            // Ambil data pelanggan
            $pelanggan = pelangganModel::where('id_pelanggan', $id_pelanggan)->get();
            $pelanggan = pelangganModel::where('id_pelanggan', '=', $id_pelanggan)
                ->get();

            //  QTY TERBARU SUDAH ADA
            $data = session($cartKey, []);

            $keranjang = collect($data)->sum('qty');

            // dd($data, $keranjang);

            // dd($keranjang);
            return view('User.reservasi', compact('pelanggan', 'keranjang'));
        }
    }


    public function formReservasi(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'no_hp' => 'required',
            'waktu' => 'required',
            'jumlah_tamu' => 'required|numeric',
            // 'jumlah_meja' => 'required|numeric',
            'tanggal' => 'required',

        ]);

        // Mengambil Data Session
        $id_pelanggan = session('id_pelanggan');

        // WAJIB: definisikan cartKey
        $cartKey = 'keranjang_' . $id_pelanggan;

        // keranjang 
        $keranjang = session($cartKey, []);

        // dd($keranjang);

        // total banyak pesanan
        $totalQty = array_sum(array_column($keranjang, 'qty'));

        // dd($totalQty);

        $jumlahTamu = $request->jumlah_tamu;
        // $jumlahMeja = $request->jumlah_meja;

        if ($totalQty < $jumlahTamu) {
            return back()->with('gagal', 'pastikan setiap tamu memesan makanan atau minuman minimal 1 pesanan, total pesanan anda: ' . $totalQty . ' total tamu yang anda inputkan ' . $jumlahTamu);
        }
        if ($jumlahTamu > 10) {
            return back()->with('gagal', 'Jumlah tamu maximal 10.');
        }
        if ($jumlahTamu == 1 && $totalQty < 2) {
            return back()->with('gagal', 'pastikan jika Jumlah tamu 1, maka total pesanan minimal 2');
        }

        // meja max 4 orang
        // $minimalMeja = ceil($jumlahTamu / 4);

        // VALIDASI LOGIKA
        // if ($jumlahMeja < $minimalMeja) {
        //     return redirect()->back()
        //         ->withErrors([
        //             'jumlah_meja' => "Jumlah tamu $jumlahTamu orang membutuhkan minimal $minimalMeja meja"
        //         ])
        //         ->withInput();
        // }

        // Mengambil Data Session
        $data = [
            'nama' => $request->nama,
            'noHp' => $request->no_hp,
            'jumlahTamu' => $request->jumlah_tamu,
            'jumlahMeja' => $request->jumlah_meja,
            'waktu' => $request->waktu,
            'tanggal' => $request->tanggal,
        ];

        session([
            'dataReservasi' => $data,
            'jumlahMejaDipilih' => 0,
            'mejaDipilih' => []
        ]);

        // dd(Session::get('dataReservasi'));
        return redirect()->route('tempat-duduk');
    }

    // ============================
    // PEMILIHAN MEJA
    // ============================
    public function showTempatDuduk()
    {
        $data = session('dataReservasi');

        if (empty($data)) {
            return redirect()->route('reservasi')
                ->with('gagal', 'Silakan isi form reservasi terlebih dahulu.');
        }

        $tanggalReservasi = $data['tanggal'];
        $waktuReservasi = $data['waktu'];
        if (strlen($waktuReservasi) === 5) {
            $waktuReservasi .= ':00';
        }

        $mulaiReservasi = Carbon::parse($tanggalReservasi . ' ' . $waktuReservasi);
        $selesaiReservasi = $mulaiReservasi->copy()->addHours(2);

        // Status ini hanya untuk tampilan. Keputusan final ketersediaan selalu diperiksa lagi saat memilih/checkout.
        $reservasiTerkait = DB::table('reservasi_meja')
            ->join('reservasi', 'reservasi_meja.id_reservasi', '=', 'reservasi.id_reservasi')
            ->leftJoin('transaksi', 'reservasi.id_reservasi', '=', 'transaksi.id_reservasi')
            ->whereBetween('reservasi.tanggal', [
                $mulaiReservasi->copy()->subDay()->toDateString(),
                $mulaiReservasi->toDateString(),
            ])
            ->where(function ($query) {
                $query->whereIn('transaksi.status', ['menunggu', 'tervalidasi', 'selesai'])
                    ->orWhereNull('transaksi.status');
            })
            ->select('reservasi_meja.id_meja', 'reservasi.tanggal', 'reservasi.waktu')
            ->get();

        $mejaTerpakai = [];
        foreach ($reservasiTerkait as $reservasiLama) {
            $mulaiLama = Carbon::parse($reservasiLama->tanggal . ' ' . $reservasiLama->waktu);
            $selesaiLama = $mulaiLama->copy()->addHours(2);

            if ($mulaiLama->lt($selesaiReservasi) && $selesaiLama->gt($mulaiReservasi)) {
                $mejaTerpakai[] = (int) $reservasiLama->id_meja;
            }
        }

        $daftarMeja = Meja::orderBy('id_meja')->get()->map(function ($meja) use ($mejaTerpakai) {
            $meja->status = in_array((int) $meja->id_meja, $mejaTerpakai, true)
                ? 'TERBOOKING'
                : 'KOSONG';
            return $meja;
        });

        $mejaIndoor = $daftarMeja->where('ruangan', 'Indoor')->values();
        $mejaOutdoor = $daftarMeja->where('ruangan', 'Outdoor')->values();
        $jumlahMeja = (int) ceil(((int) ($data['jumlahTamu'] ?? $data['jumlah_tamu'] ?? 1)) / 4);
        $mejaDipilih = session('mejaDipilih', []);

        return view('User.tempat-duduk', compact('jumlahMeja', 'mejaDipilih', 'mejaIndoor', 'mejaOutdoor'));
    }

    public function pilihTempatDuduk(Request $request)
    {
        $request->validate([
            'ruangan' => 'required|in:Indoor,Outdoor',
        ]);

        $id_pengelola = Session::get('id_pengelola');

        if ($id_pengelola != null) {
            // Alur kasir tetap menggunakan satu meja dan waktu saat ini.
            $waktuReservasi = now();
            $daftarBarang = json_decode($request->items, true);
            Session::put('keranjangKasir', $daftarBarang);
            $tanggalReservasi = $waktuReservasi->toDateString();
            $jamReservasi = $waktuReservasi->format('H:i:s');
            $jumlahTamu = 1;
        } else {
            $dataReservasi = Session::get('dataReservasi');
            if (empty($dataReservasi)) {
                return redirect()->route('reservasi')
                    ->with('gagal', 'Silakan isi form reservasi terlebih dahulu.');
            }

            $tanggalReservasi = $dataReservasi['tanggal'];
            $jamReservasi = $dataReservasi['waktu'];
            $jumlahTamu = (int) ($dataReservasi['jumlahTamu'] ?? $dataReservasi['jumlah_tamu'] ?? 1);
            if (strlen($jamReservasi) === 5) {
                $jamReservasi .= ':00';
            }
        }

        // Kapasitas meja mengikuti aturan aplikasi yang sudah ada: maksimal 4 tamu per meja.
        $jumlahMejaDibutuhkan = max(1, (int) ceil($jumlahTamu / 4));
        $mulaiReservasi = Carbon::parse($tanggalReservasi . ' ' . $jamReservasi);
        $selesaiReservasi = $mulaiReservasi->copy()->addHours(2);

        // Status transaksi yang sudah dipakai aplikasi. Status gagal/batal/expired tidak menahan meja.
        // Ambil reservasi pada tanggal target dan sehari sebelumnya untuk menangani reservasi lintas tengah malam.
        $reservasiTerkait = DB::table('reservasi_meja')
            ->join('reservasi', 'reservasi_meja.id_reservasi', '=', 'reservasi.id_reservasi')
            ->leftJoin('transaksi', 'reservasi.id_reservasi', '=', 'transaksi.id_reservasi')
            ->whereBetween('reservasi.tanggal', [
                $mulaiReservasi->copy()->subDay()->toDateString(),
                $mulaiReservasi->toDateString(),
            ])
            ->where(function ($query) {
                $query->whereIn('transaksi.status', ['menunggu', 'tervalidasi', 'selesai'])
                    ->orWhereNull('transaksi.status');
            })
            ->select(
                'reservasi_meja.id_meja',
                'reservasi.tanggal',
                'reservasi.waktu'
            )
            ->get();

        $mejaTerpakai = [];
        foreach ($reservasiTerkait as $reservasiLama) {
            $mulaiLama = Carbon::parse($reservasiLama->tanggal . ' ' . $reservasiLama->waktu);
            $selesaiLama = $mulaiLama->copy()->addHours(2);

            if ($mulaiLama->lt($selesaiReservasi) && $selesaiLama->gt($mulaiReservasi)) {
                $mejaTerpakai[] = $reservasiLama->id_meja;
            }
        }

        $mejaTersedia = DB::table('meja')
            ->where('ruangan', $request->ruangan)
            ->when(!empty($mejaTerpakai), function ($query) use ($mejaTerpakai) {
                $query->whereNotIn('id_meja', array_unique($mejaTerpakai));
            })
            ->orderBy('id_meja')
            ->limit($jumlahMejaDibutuhkan)
            ->pluck('id_meja')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (count($mejaTersedia) < $jumlahMejaDibutuhkan) {
            return back()->with(
                'gagal',
                'Meja di area ' . $request->ruangan . ' tidak mencukupi untuk ' . $jumlahTamu .
                ' tamu pada waktu tersebut. Silakan kurangi jumlah tamu atau pilih waktu lain.'
            );
        }

        Session::put('mejaDipilih', $mejaTersedia);
        Session::put('RuanganDipilih', $request->ruangan);

        return redirect()->route('checkout');
    }


    public function detailPesanan()
    {
        $userId = session('id_pelanggan');
        $paymentDeadlineKey = 'payment_deadline_at_' . $userId;

        $data = Session::get('dataReservasi');
        $meja = Session::get('mejaDipilih');

        // dd($meja);

        $mejaData = DB::table('meja')->where('id_meja', $meja)->first();

        // kepake
        $ruangan = Session::get('RuanganDipilih');

        $menuReservasi = Session::get('menuReservasi');
        $keranjang = Session::get('keranjang_' . $userId, []);

        // dd($data);

        // ================================
        // JALUR 1️ : DARI RESERVASI
        // ================================
        if ($menuReservasi) {

            $pesanan = [[
                'id'     => $menuReservasi['id_menu'],
                'nama'   => $menuReservasi['nama'],
                'harga'  => $menuReservasi['harga'],
                'gambar' => $menuReservasi['gambar'],
                'qty'    => $menuReservasi['qty'], // 
            ]];

            $totalHarga = $menuReservasi['harga'] * $menuReservasi['qty'];
        }

        // ================================
        // JALUR 2️ : DARI KERANJANG
        // ================================
        elseif (!empty($keranjang)) {

            $pesanan = $keranjang;

            $totalHarga = 0;
            foreach ($keranjang as $item) {
                $totalHarga += $item['harga'] * $item['qty'];
            }
        }

        // ================================
        // VALIDASI
        // ================================
        else {
            return redirect()->route('home-login')
                ->with('gagal', 'Pesanan tidak ditemukan.');
        }

        // dd("menu Keranjang: {{$keranjang}}");

        // $pajak = $totalHarga * 0.1;
        // $totalBayar = $totalHarga + $pajak;
        $totalBayar = $totalHarga;

        // hanya wajib jika reservasi
        if ($menuReservasi && (!$data || !$ruangan)) {
            return redirect()->route('reservasi')
                ->with('gagal', 'Data reservasi belum lengkap.');
        }

        $paymentDeadlineAt = Session::get($paymentDeadlineKey);
        if (!$paymentDeadlineAt) {
            $paymentDeadlineAt = Carbon::now()->addMinutes(30)->toDateTimeString();
            Session::put($paymentDeadlineKey, $paymentDeadlineAt);
        }

        $paymentDeadlineAt = Carbon::parse($paymentDeadlineAt);
        $paymentExpired = Carbon::now()->greaterThan($paymentDeadlineAt);

        // dd($keranjang);

        return view('User.detail-pesanan', compact(
            'data',
            'ruangan',
            'mejaData',
            'pesanan',
            'totalHarga',
            'totalBayar',
            'keranjang',
            'paymentDeadlineAt',
            'paymentExpired'
        ));
    }


    public function uploadBukti(Request $request)
    {
        $id_pelanggan = session('id_pelanggan');
        $paymentDeadlineKey = 'payment_deadline_at_' . $id_pelanggan;
        $paymentDeadlineAt = Session::get($paymentDeadlineKey);

        if ($paymentDeadlineAt && Carbon::now()->greaterThan(Carbon::parse($paymentDeadlineAt))) {
            return redirect()
                ->route('detail-pesanan')
                ->with('gagal', 'Waktu upload bukti pembayaran sudah habis. Silakan ulangi pemesanan.');
        }

        $request->validate([
            'bukti-pembayaran' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $id_pengelola = session('id_pengelola');

        // =========================
        // AMBIL SESSION
        // =========================
        $dataReservasi = Session::get('dataReservasi');
        $ruangan = Session::get('RuanganDipilih');
        $mejaDipilih   = Session::get('mejaDipilih');
        $menuReservasi = Session::get('menuReservasi');
        $cartKey       = 'keranjang_' . $id_pelanggan;
        $keranjang     = Session::get($cartKey, []);


        // =========================
        // VALIDASI RESERVASI
        // =========================
        if (!$dataReservasi || empty($ruangan)) {
            return redirect()->route('reservasi')
                ->with('gagal', 'Data reservasi tidak lengkap.');
        }

        // =========================
        // TENTUKAN JALUR PESANAN
        // =========================
        if (!empty($keranjang)) {
            // 🔹 JALUR KERANJANG
            $pesananFinal = $keranjang;
        } elseif (!empty($menuReservasi)) {
            // 🔹 JALUR RESERVASI LANGSUNG
            $pesananFinal = [[
                'id'    => $menuReservasi['id_menu'],
                'harga' => $menuReservasi['harga'],
                'qty'   => $menuReservasi['qty'] ?? 1,
            ]];
        } else {
            return redirect()->route('home-login')
                ->with('gagal', 'Pesanan tidak ditemukan.');
        }


        // =========================
        // SIMPAN FILE BUKTI
        // =========================
        $path = $request->file('bukti-pembayaran')
            ->store('bukti-pembayaran', 'public');

        // dd($mejaDipilih);
        DB::beginTransaction();

        try {

            // =========================
            // OLAH RUANGAN
            // =========================
            // $ruanganChosed = [];
            // foreach ($mejaDipilih as $m) {
            //     $ruanganChosed[] = $m['ruangan'];
            // }

            // =========================
            // SIMPAN RESERVASI
            // =========================
            $reservasi = Reservasi::create([
                'id_pelanggan'     => $id_pelanggan,
                'tanggal'          => $dataReservasi['tanggal'],
                'waktu'            => $dataReservasi['waktu'],
                'jumlah_tamu'      => $dataReservasi['jumlah_tamu'] ?? 1,
                'ruangan'          => $ruangan,
                'bukti_pembayaran' => $path,
            ]);

            // =========================
            // SIMPAN RESERVASI Meja
            // =========================

            $reservasi_meja = ReservasiMeja::create([
                'id_reservasi'     => $reservasi->id_reservasi,
                'id_meja'          => $mejaDipilih,
            ]);

            // =========================
            // HITUNG TOTAL
            // =========================
            $totalHarga = 0;
            foreach ($pesananFinal as $item) {
                $totalHarga += $item['harga'] * $item['qty'];
            }

            // =========================
            // SIMPAN TRANSAKSI
            // =========================
            $transaksi = Transaksi::create([
                'id_transaksi'       => TransactionId::next(),
                'id_pelanggan'      => $id_pelanggan,
                'id_reservasi'      => $reservasi->id_reservasi,
                'id_pengelola'      => $id_pengelola,
                'total'             => $totalHarga,
                'status'            => 'menunggu',
                'metode_pembayaran' => 'QRIS',
            ]);

            // =========================
            // DETAIL PESANAN
            // =========================
            foreach ($pesananFinal as $item) {
                DetailPesanan::create([
                    'id_transaksi' => $transaksi->id_transaksi,
                    'id_menu'      => $item['id'],
                    'qty'          => $item['qty'],
                    'catatan'          => $item['catatan'] ?? null,
                ]);
            }

            // =========================
            // KURANGI STOK MENU
            // =========================
            foreach ($pesananFinal as $item) {

                $menu = MenuModel::where('id_menu', $item['id'])
                    ->lockForUpdate() //  cegah race condition
                    ->firstOrFail();

                // VALIDASI STOK
                if ($menu->stok < $item['qty']) {
                    throw new \Exception(
                        'Stok menu "' . $menu->nama_menu . '" tidak mencukupi'
                    );
                }

                // KURANGI STOK
                $menu->stok = $menu->stok - $item['qty'];
                $menu->save();
            }


            // =========================
            // RELASI MEJA
            // =========================
            // foreach ($mejaDipilih as $m) {
            //     $meja = Meja::where('ruangan', $m['ruangan'])
            //         ->where('kode_meja', $m['kode_meja'])
            //         ->firstOrFail();

            //     ReservasiMeja::create([
            //         'id_reservasi' => $reservasi->id_reservasi,
            //         'id_meja'      => $meja->id_meja,
            //     ]);
            // }

            DB::commit();

            // =========================
            // BERSIHKAN SESSION
            // =========================

            // Session::forget([
            //     'menuReservasi',
            //     'mejaDipilih',
            //     'dataReservasi',
            //     $cartKey
            // ]);



            return redirect()
                ->route('detail-transaksi', $reservasi->id_reservasi)
                ->with('success', 'Bukti pembayaran berhasil diupload!');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return redirect()->back()->with('gagal', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }




    // ============================
    // RESET SESSION
    // ============================
    public function resetSession(Request $request)
    {
        // Hapus session reservasi
        Session::forget([
            'dataReservasi',
            'mejaDipilih',
            'menuReservasi',
        ]);

        // Hapus semua keranjang user (jika ada)
        $userId = session('id_pelanggan');
        if ($userId) {
            Session::forget('keranjang_' . $userId);
        }

        // (Opsional) flash message
        return redirect()->route('home')
            ->with('success', 'Pesanan berhasil dibatalkan.');
    }




    // ============================
    // DETAIL TRANSAKSI
    // ============================
    public function detailTransaksi($id)
    {
        $idPelanggan = session('id_pelanggan');
        if (!$idPelanggan) {
            return redirect()->route('login');
        }

        $cartKey = 'keranjang_' . $idPelanggan;

        $reservasi = Reservasi::findOrFail($id);
        $data = Session::get('dataReservasi');
        $meja = Session::get('mejaDipilih');
        $ruangan = Session::get('RuanganDipilih');


        $keranjang = Session::get($cartKey, []);
        $menuFromReservasi = Session::get('menuReservasi');

        $dataMeja = DB::table('meja')->where('id_meja', $meja)->first();

        $pesanan = [];

        // 1️ Menu dari tombol reservasi
        if ($menuFromReservasi) {
            $pesanan[] = [
                'id'     => $menuFromReservasi['id_menu'],
                'nama'   => $menuFromReservasi['nama'],
                'harga'  => $menuFromReservasi['harga'],
                'gambar' => $menuFromReservasi['gambar'],
                'qty'    => 1,
            ];
        }

        // 2️ Gabungkan keranjang
        foreach ($keranjang as $item) {
            $pesanan[] = $item;
        }

        // Hitung total
        $totalHarga = 0;
        foreach ($pesanan as $item) {
            $totalHarga += $item['harga'] * $item['qty'];
        }

        // $pajak = $totalHarga * 0.1;
        // $totalBayar = $totalHarga + $pajak;
        $totalBayar = $totalHarga;

        if (!$data || !$ruangan) {
            return redirect()->route('reservasi')
                ->with('gagal', 'Data reservasi belum lengkap.');
        }

        return view('User.detail-transaksi', compact(
            'data',
            'dataMeja',
            'ruangan',
            'pesanan',
            'totalHarga',
            // 'pajak',
            'totalBayar',
            'reservasi'
        ));
    }


    public function setTransaksiGagal(Request $request)
    {
        $id_pelanggan = session('id_pelanggan');
        $paymentDeadlineKey = 'payment_deadline_at_' . $id_pelanggan;

        // Ambil data session persis seperti di uploadBukti
        $dataReservasi = Session::get('dataReservasi');
        $ruangan = Session::get('RuanganDipilih');
        $menuReservasi = Session::get('menuReservasi');
        $cartKey = 'keranjang_' . $id_pelanggan;
        $keranjang = Session::get($cartKey, []);
        $id_pengelola = session('id_pengelola');

        // Tentukan jalur pesanan final untuk menghitung total nominal
        if (!empty($keranjang)) {
            $pesananFinal = $keranjang;
        } elseif (!empty($menuReservasi)) {
            $pesananFinal = [[
                'id'    => $menuReservasi['id_menu'],
                'harga' => $menuReservasi['harga'],
                'qty'   => $menuReservasi['qty'] ?? 1,
            ]];
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak ditemukan atau session sudah bersih.'
            ], 404);
        }

        // Hitung total harga item pesanan
        $totalHarga = 0;
        foreach ($pesananFinal as $item) {
            $totalHarga += $item['harga'] * $item['qty'];
        }

        DB::beginTransaction();

        try {
            // 1. Simpan data ke tabel Reservasi terlebih dahulu dengan status/keterangan pembatalan waktu
            // Karena bukti_pembayaran wajib/tidak boleh null, kita isi dengan teks penanda
            $reservasi = Reservasi::create([
                'id_pelanggan'     => $id_pelanggan,
                'tanggal'          => $dataReservasi['tanggal'] ?? now()->toDateString(),
                'waktu'            => $dataReservasi['waktu'] ?? now()->toTimeString(),
                'jumlah_tamu'      => $dataReservasi['jumlah_tamu'] ?? 1,
                'ruangan'          => $ruangan ?? '-',
                'bukti_pembayaran' => 'EXPIRED_TIMEOUT', // Penanda bahwa baris ini gagal karena waktu habis
            ]);

            // 2. Simpan ke tabel Transaksi menggunakan nama kolom yang sesuai dengan model Anda ('total')
            $transaksi = Transaksi::create([
                'id_transaksi'       => TransactionId::next(),
                'id_pelanggan'      => $id_pelanggan,
                'id_reservasi'      => $reservasi->id_reservasi, // Menghubungkan foreign key
                'id_pengelola'      => $id_pengelola,
                'total'             => $totalHarga, // Sesuai kolom database Anda di uploadBukti
                'status'            => 'gagal',     // Langsung diset gagal karena waktu habis
                'metode_pembayaran' => 'QRIS',
            ]);

            // 3. Simpan baris item ke DetailPesanan agar riwayat menu yang ingin dibeli tetap tercatat
            foreach ($pesananFinal as $item) {
                DetailPesanan::create([
                    'id_transaksi' => $transaksi->id_transaksi,
                    'id_menu'      => $item['id'],
                    'qty'          => $item['qty'],
                ]);
            }

            DB::commit();

            // 4. Bersihkan session pemesanan agar transaksi dianggap hangus dan tidak bisa di-back
            Session::forget([
                'dataReservasi',
                'RuanganDipilih',
                'mejaDipilih',
                'menuReservasi',
                $cartKey,
                $paymentDeadlineKey
            ]);

            // Daftarkan pesan gagal untuk dibaca view setelah redirect browser
            Session::flash('gagal', 'Waktu upload bukti pembayaran sudah habis. Transaksi otomatis dicatat gagal.');

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi gagal berhasil disimpan ke database.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data ke database: ' . $e->getMessage()
            ], 500);
        }
    }
}
