@extends('layouts.admin')

@section('title', 'Meja')

@section('content')
<h2 class="text-2xl font-bold text-gray-800 mb-6">Data Meja</h2>

@if (session('success'))
    <div class="mb-4 rounded-lg bg-green-100 text-green-700 px-4 py-3">{{ session('success') }}</div>
@endif

<div class="space-y-6">
    <div class="flex justify-between bg-white py-4 px-5 rounded-lg shadow">
        <a href="{{ route('dataMeja.showFormMeja') }}" class="inline-flex items-center gap-2 bg-primary text-white font-semibold px-4 py-2 rounded-lg">
            <span class="material-symbols-outlined">add</span> Tambah Meja
        </a>
    </div>

    @foreach (['Indoor' => $mejaIndoor, 'Outdoor' => $mejaOutdoor] as $ruangan => $daftarMeja)
    <div class="overflow-x-auto bg-white rounded-xl shadow border">
        <table class="min-w-full text-sm text-gray-700">
            <caption class="px-6 py-4 font-semibold border-b bg-gray-50 text-center">Tabel Meja {{ $ruangan }}</caption>
            <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-center">No</th>
                    <th class="px-4 py-3 text-center">Nomor Meja</th>
                    <th class="px-4 py-3 text-center">Ruangan</th>
                    <th class="px-4 py-3 text-center">Kapasitas</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($daftarMeja as $meja)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-center">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3 text-center font-semibold">{{ $meja->kode_meja }}</td>
                    <td class="px-4 py-3 text-center">{{ $meja->ruangan }}</td>
                    <td class="px-4 py-3 text-center">{{ $meja->kapasitas }} orang</td>
                    <td class="px-4 py-3 text-center">
                        <div class="inline-flex items-center gap-2">
                            <a href="{{ route('dataMeja.edit', $meja->id_meja) }}" class="px-3 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white" title="Edit meja">
                                <span class="material-symbols-outlined text-[18px]">edit</span>
                            </a>
                            <form action="{{ route('dataMeja.destroy', $meja->id_meja) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus meja ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="px-3 py-2 rounded-md bg-red-500 hover:bg-red-600 text-white" title="Hapus meja">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-6 text-gray-400">Tidak ada data meja</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endforeach
</div>
@endsection
