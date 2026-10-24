@extends('layouts.admin')

@section('title', 'Meja')

@section('content')

<!-- TITLE -->
<h2 class="text-2xl font-bold text-gray-800 mb-6">
    Data Meja
</h2>

<div class="space-y-6">

    <!-- TOP ACTION -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between
                bg-white gap-4 py-4 px-5 rounded-lg shadow">

        <a href="{{ route('dataMeja.showFormMeja') }}">

            <span class="inline-flex items-center gap-2
                   bg-primary hover:bg-primary/90
                   text-white font-semibold
                   px-4 py-2 rounded-lg transition"><span class="material-symbols-outlined">add</span> Tambah Meja</span>
        </a>

    </div>

    <!-- TABLE INDOOR -->
    <div class="overflow-x-auto py-0 bg-white rounded-xl shadow border">
        <table class="min-w-full text-sm text-gray-700">
            <caption class="caption-top text-left px-6 py-4 font-semibold text-gray-700 border-b bg-gray-50 rounded-t-xl text-center">
                Tabel Meja Indoor
            </caption>
            <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-center">No</th>
                    <th class="px-4 py-3 text-center">Nomor Meja</th>
                    <th class="px-4 py-3 text-center">Ruangan</th>
                    {{-- <th class="px-4 py-3 text-center">Status</th> --}}
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse ($mejaIndoor as $mi)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-center">
                            {{ $loop->iteration }}
                        </td>

                        <td class="px-4 py-3 text-center font-semibold">
                            {{ $mi->kode_meja }}
                        </td>

                        <td class="px-4 py-3 text-center font-semibold">
                            {{ $mi->ruangan }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            <form action="{{ route('dataMeja.destroy', $mi->id_meja) }}"
                                  method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus meja ini?')">
                                @csrf
                                @method('DELETE')

                                <button
                                    class="px-3 py-2 rounded-md
                                           bg-red-500 hover:bg-red-600
                                           text-white transition">
                                    <span class="material-symbols-outlined text-[18px]">
                                        delete
                                    </span>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-6 text-gray-400">
                            Tidak ada data meja
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>


    <!-- TABLE OUTDOOR -->
    <div class="overflow-x-auto bg-white rounded-xl shadow border">

        <table class="min-w-full text-sm text-gray-700">
            <caption class="caption-top text-left px-6 py-4 font-semibold text-gray-700 border-b bg-gray-50 rounded-t-xl text-center">
                Tabel Meja Outdoor
            </caption>
            <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-center">No</th>
                    <th class="px-4 py-3 text-center">Nomor Meja</th>
                    <th class="px-4 py-3 text-center">Ruangan</th>
                    {{-- <th class="px-4 py-3 text-center">Status</th> --}}
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse ($mejaOutdoor as $mo)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-center">
                            {{ $loop->iteration }}
                        </td>

                        <td class="px-4 py-3 text-center font-semibold">
                            {{ $mo->kode_meja }}
                        </td>

                        <td class="px-4 py-3 text-center font-semibold">
                            {{ $mo->ruangan }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            <form action="{{ route('dataMeja.destroy', $mo->id_meja) }}"
                                  method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus meja ini?')">
                                @csrf
                                @method('DELETE')

                                <button
                                    class="px-3 py-2 rounded-md
                                           bg-red-500 hover:bg-red-600
                                           text-white transition">
                                    <span class="material-symbols-outlined text-[18px]">
                                        delete
                                    </span>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-6 text-gray-400">
                            Tidak ada data meja
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL TAMBAH MEJA -->
<div id="modalTambah"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">

    <div class="bg-white w-full max-w-md rounded-lg shadow-lg p-6 relative">
        <button onclick="closeModal()"
                class="absolute top-3 right-3 text-gray-500 hover:text-red-500">
            &times;
        </button>

        <h3 class="text-lg font-semibold mb-4">Tambah Meja</h3>

        <form action="{{ route('dataMeja.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium">Nomor Meja</label>
                <input type="text" name="nomor_meja" required
                    class="w-full border rounded-lg px-3 py-2 focus:ring-primary">
            </div>

            <div>
                <label class="block text-sm font-medium">Kapasitas</label>
                <input type="number" name="kapasitas" min="1" required
                    class="w-full border rounded-lg px-3 py-2 focus:ring-primary">
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeModal()"
                    class="px-4 py-2 rounded-lg border">
                    Batal
                </button>

                <button type="submit"
                    class="px-4 py-2 rounded-lg bg-primary text-white">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('extra-script')
<script>
    function openModal() {
        document.getElementById('modalTambah').classList.remove('hidden');
        document.getElementById('modalTambah').classList.add('flex');
    }

    function closeModal() {
        document.getElementById('modalTambah').classList.add('hidden');
        document.getElementById('modalTambah').classList.remove('flex');
    }
</script>
@endpush
