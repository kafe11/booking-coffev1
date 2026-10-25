<?php

namespace App\Http\Controllers;

use App\Models\admin\menuModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class MenuController extends Controller
{
    private function getDefaultDrinkOptions(): array
    {
        return [
            'temperature' => 'ice',
            'sugar_level' => 'normal_sugar',
            'ice_level' => 'normal_ice',
        ];
    }

    private function normalizeDrinkOptions(array $options = []): array
    {
        $defaults = $this->getDefaultDrinkOptions();
        $allowed = [
            'temperature' => ['ice', 'hot'],
            'sugar_level' => ['normal_sugar', 'less_sugar'],
            'ice_level' => ['normal_ice', 'less_ice', 'no_ice'],
        ];

        foreach ($allowed as $key => $values) {
            if (!isset($options[$key]) || !in_array($options[$key], $values, true)) {
                $options[$key] = $defaults[$key];
            }
        }

        return $options;
    }

    public function menuMinuman()
    {
        // $Soda= menuModel::where('kategori', 'minuman')->where('jenis', 'Soda Based')->latest()->get();
        $NonCoffe = menuModel::where('kategori', 'minuman')->where('jenis', 'Non Coffee')->get();
        $Coffe = menuModel::where('kategori', 'minuman')->where('jenis', 'Coffee')->latest()->get();

        // dd($NonCoffe);

        return view('User.menu-minuman', compact('NonCoffe', 'Coffe'));
    }

    public function menuMakanan()
    {
        $menuMakananHeavy = menuModel::where('kategori', 'makanan')->where('jenis', 'Heavy Meal')->latest()->get();
        $Dessert = menuModel::where('kategori', 'makanan')->where('jenis', 'Dessert')->latest()->get();
        $Appetizer = menuModel::where('kategori', 'makanan')->where('jenis', 'Appetizer')->latest()->get();

        // dd($menuMakananHeavy, $Dessert, $Appetizer);

        return view('User.menu-makanan', compact('menuMakananHeavy', 'Dessert', 'Appetizer'));
    }

    public function detailMinuman($id)
    {
        $chosedMenu = menuModel::where('id_menu', $id)->get();

        if (!$chosedMenu) {
            abort(404);
        }

        // dd($chosedMenu);

        return view('User.detail-menu', compact('chosedMenu'));
    }

    public function detailMakanan($id)
    {
        $chosedMenu = menuModel::where('id_menu', $id)->get();

        if (!$chosedMenu) {
            abort(404);
        }

        return view('User.detail-menu', compact('chosedMenu'));
    }

    public function fromMenu($id)
    {
        $menu = menuModel::findOrFail($id);

        Session::put('menuReservasi', [
            'id_menu' => $menu->id_menu,
            'nama' => $menu->nama_menu,
            'harga' => $menu->harga,
            'gambar' => $menu->gambar,
            'qty' => 1,
        ]);

        return redirect()->route('reservasi');
    }

    public function addToCart(Request $request)
    {
        $userId = session('id_pelanggan');

        if (!$userId) {
            return redirect()->route('login')
                ->with('warning', 'Harap login terlebih dahulu sebelum menambahkan menu ke keranjang.');
        }

        $menu = menuModel::findOrFail($request->id);
        $qtyRequest = max(1, (int) $request->input('qty', 1));
        $catatan = $request->filled('level_pedas')
            ? trim((string) $request->level_pedas)
            : (implode(', ', array_filter([
                $request->temperature,
                $request->sugar_level,
                $request->ice_level,
            ])) ?: '-');

        $cartKey = 'keranjang_' . $userId;
        $keranjang = session($cartKey, []);
        $indexMenu = null;
        $qtyMenuSaatIni = 0;

        foreach ($keranjang as $index => &$item) {
            if ((string) ($item['id'] ?? '') !== (string) $menu->id_menu) {
                continue;
            }

            // Kompatibilitas dengan item lama yang belum memiliki daftar variasi.
            if (empty($item['variasi'])) {
                $item['variasi'] = [[
                    'catatan' => $item['catatan'] ?? '-',
                    'qty' => (int) ($item['qty'] ?? 1),
                ]];
            }

            $qtyMenuSaatIni += (int) ($item['qty'] ?? 0);
            if ($indexMenu === null) {
                $indexMenu = $index;
            }
        }
        unset($item);

        if ($qtyMenuSaatIni + $qtyRequest > (int) $menu->stok) {
            return back()->with('gagal', 'Stok "' . $menu->nama_menu . '" hanya tersisa ' . $menu->stok);
        }

        if ($indexMenu !== null) {
            // Gabungkan semua baris lama untuk menu yang sama menjadi satu kartu.
            $itemUtama = $keranjang[$indexMenu];
            $variasi = $itemUtama['variasi'] ?? [];

            foreach ($keranjang as $index => $item) {
                if ($index === $indexMenu || (string) ($item['id'] ?? '') !== (string) $menu->id_menu) {
                    continue;
                }

                $variasiLama = $item['variasi'] ?? [[
                    'catatan' => $item['catatan'] ?? '-',
                    'qty' => (int) ($item['qty'] ?? 1),
                ]];
                $variasi = array_merge($variasi, $variasiLama);
                unset($keranjang[$index]);
            }

            $variasiIndex = null;
            foreach ($variasi as $vIndex => $varian) {
                if (($varian['catatan'] ?? '-') === $catatan) {
                    $variasiIndex = $vIndex;
                    break;
                }
            }

            if ($variasiIndex !== null) {
                $variasi[$variasiIndex]['qty'] = (int) $variasi[$variasiIndex]['qty'] + $qtyRequest;
            } else {
                $variasi[] = ['catatan' => $catatan, 'qty' => $qtyRequest];
            }

            $keranjang[$indexMenu] = array_merge($itemUtama, [
                'id' => $menu->id_menu,
                'nama' => $menu->nama_menu,
                'harga' => $menu->harga,
                'gambar' => $menu->gambar,
                'qty' => $qtyMenuSaatIni + $qtyRequest,
                'stok' => $menu->stok,
                'kategori' => $menu->kategori,
                'variasi' => array_values($variasi),
                'catatan' => $catatan,
            ]);
        } else {
            $keranjang[] = [
                'id' => $menu->id_menu,
                'nama' => $menu->nama_menu,
                'harga' => $menu->harga,
                'gambar' => $menu->gambar,
                'qty' => $qtyRequest,
                'stok' => $menu->stok,
                'kategori' => $menu->kategori,
                'catatan' => $catatan,
                'variasi' => [['catatan' => $catatan, 'qty' => $qtyRequest]],
            ];
        }

        $keranjang = array_values($keranjang);
        session()->put($cartKey, $keranjang);

        return redirect()->route('keranjang')->with('success', 'Ditambahkan ke keranjang!');
    }

    public function keranjang()
    {
        $userId = session('id_pelanggan');

        if (!$userId) {
            return redirect()
                ->route('login')
                ->with('success', 'Untuk melihat keranjang harap login terlebih dahulu!');
        }

        $cartKey = 'keranjang_' . $userId;
        $selectedKey = 'keranjang_terpilih_' . $userId;

        $keranjang = session($cartKey, []);
        $selected = session($selectedKey, []);

        // dd($keranjang);

        foreach ($keranjang as &$item) {
            if (!isset($item['kategori']) && isset($item['id'])) {
                $menu = menuModel::find($item['id']);
                if ($menu) {
                    $item['kategori'] = $menu->kategori;
                }
            }

            if (($item['kategori'] ?? null) === 'minuman') {
                $item['options'] = $this->normalizeDrinkOptions($item['options'] ?? []);
            }
        }
        unset($item);

        session()->put($cartKey, $keranjang);

        return view('User.keranjang', compact('keranjang', 'selected'));
    }

    public function pilihItem(Request $request)
    {
        $request->validate([
            'index' => 'required',
            'checked' => 'required|boolean',
        ]);

        $userId = session('id_pelanggan');
        $cartKey = 'keranjang_' . $userId;
        $selectedKey = 'keranjang_terpilih_' . $userId;

        $keranjang = session($cartKey, []);
        $selected = session($selectedKey, []);

        if (!isset($keranjang[$request->index])) {
            return response()->json(['success' => false]);
        }

        if ($request->checked) {
            $selected[$request->index] = $keranjang[$request->index];
        } else {
            unset($selected[$request->index]);
        }

        session([$selectedKey => $selected]);

        return response()->json(['success' => true]);
    }

    public function removeItem($index)
    {
        $userId = session('id_pelanggan');
        $cartKey = 'keranjang_' . $userId;

        $keranjang = session($cartKey, []);

        if (!isset($keranjang[$index])) {
            return back()->with('error', 'Item tidak ditemukan.');
        }

        unset($keranjang[$index]);
        $keranjang = array_values($keranjang);

        session([$cartKey => $keranjang]);

        return back()->with('success', 'Item berhasil dihapus!');
    }

    public function updateQty(Request $request, $index)
    {
        $userId = session('id_pelanggan');
        $cartKey = 'keranjang_' . $userId;
        $keranjang = session($cartKey, []);

        if (!isset($keranjang[$index])) {
            return response()->json(['success' => false], 404);
        }

        $menu = menuModel::findOrFail($keranjang[$index]['id']);
        $qty = max(1, (int) $request->input('qty', 1));
        if ($qty > (int) $menu->stok) {
            $qty = (int) $menu->stok;
        }

        $keranjang[$index]['stok'] = $menu->stok;
        $keranjang[$index]['kategori'] = $menu->kategori;
        $variasi = $keranjang[$index]['variasi'] ?? [[
            'catatan' => $keranjang[$index]['catatan'] ?? '-',
            'qty' => (int) ($keranjang[$index]['qty'] ?? 1),
        ]];

        $totalLama = array_sum(array_map(fn ($v) => (int) ($v['qty'] ?? 0), $variasi));
        $selisih = $qty - $totalLama;
        if ($selisih > 0) {
            $lastIndex = count($variasi) - 1;
            $variasi[$lastIndex]['qty'] = (int) ($variasi[$lastIndex]['qty'] ?? 0) + $selisih;
        } elseif ($selisih < 0) {
            $kurangi = abs($selisih);
            for ($i = count($variasi) - 1; $i >= 0 && $kurangi > 0; $i--) {
                $tersedia = (int) ($variasi[$i]['qty'] ?? 0);
                $potong = min($tersedia, $kurangi);
                $variasi[$i]['qty'] = $tersedia - $potong;
                $kurangi -= $potong;
            }
            $variasi = array_values(array_filter($variasi, fn ($v) => (int) ($v['qty'] ?? 0) > 0));
        }

        if (empty($variasi)) {
            $variasi = [[
                'catatan' => $keranjang[$index]['catatan'] ?? '-',
                'qty' => 1,
            ]];
            $qty = 1;
        }

        $keranjang[$index]['variasi'] = array_values($variasi);
        $keranjang[$index]['qty'] = array_sum(array_map(fn ($v) => (int) $v['qty'], $variasi));
        $keranjang[$index]['catatan'] = $variasi[0]['catatan'] ?? '-';

        session()->put($cartKey, $keranjang);

        return response()->json([
            'success' => true,
            'qty' => $keranjang[$index]['qty'],
            'options' => $keranjang[$index]['options'] ?? [],
        ]);
    }

    public function showTempatDuduk()
    {
        $reservasi = session('reservasi');

        return view('user.tempat-duduk', compact('reservasi'));
    }

    public function pilihTempatDuduk(Request $request)
    {
        session([
            'reservasi' => [
                'nama' => $request->nama,
                'no_hp' => $request->no_hp,
                'waktu' => $request->waktu,
                'tanggal' => $request->tanggal,
                'jumlah_tamu' => $request->jumlah_tamu,
            ],
        ]);

        return redirect()->route('show-tempat-duduk');
    }
}
