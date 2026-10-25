<?php

namespace App\Http\Controllers\adminController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meja;

class mejaController extends Controller
{
    public function showMeja()
    {
        $mejaIndoor = Meja::where('ruangan', 'Indoor')->orderBy('kode_meja')->get();
        $mejaOutdoor = Meja::where('ruangan', 'Outdoor')->orderBy('kode_meja')->get();

        return view('Admin.dataMeja', compact('mejaIndoor', 'mejaOutdoor'));
    }

    public function showFormMeja()
    {
        return view('Admin.tambahMeja');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_meja' => 'required|string|max:255|unique:meja,kode_meja',
            'ruangan' => 'required|in:Indoor,Outdoor',
            'kapasitas' => 'required|integer|min:1|max:100',
        ]);

        Meja::create($validated);

        return redirect()->route('dataMeja.showMeja')->with('success', 'Meja berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $meja = Meja::findOrFail($id);

        return view('Admin.editMeja', compact('meja'));
    }

    public function update(Request $request, $id)
    {
        $meja = Meja::findOrFail($id);

        $validated = $request->validate([
            'kode_meja' => 'required|string|max:255|unique:meja,kode_meja,' . $meja->id_meja . ',id_meja',
            'ruangan' => 'required|in:Indoor,Outdoor',
            'kapasitas' => 'required|integer|min:1|max:100',
        ]);

        $meja->update($validated);

        return redirect()->route('dataMeja.showMeja')->with('success', 'Data meja berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $meja = Meja::findOrFail($id);
        $meja->delete();

        return redirect()->back()->with('success', 'Meja berhasil dihapus.');
    }
}
