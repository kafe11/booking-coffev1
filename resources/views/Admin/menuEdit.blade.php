@extends('layouts.admin')

@section('title', 'Edit Menu')

@section('content')

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-0">

    <div class="bg-white dark:bg-surface-dark rounded-xl shadow-lg p-6 sm:p-8">

        {{-- ALERT --}}
        @if (session('success'))
            <div class="mb-6 rounded-lg bg-green-100 text-green-700 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        {{-- TITLE --}}
        <h1 class="text-2xl sm:text-3xl font-bold text-center text-primary mb-8">
            Edit Menu
        </h1>

        <form action="{{ route('update', $menu->id_menu) }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf

            {{-- GAMBAR SAAT INI --}}
            @if ($menu->gambar)
                <div class="flex justify-center mb-6">
                    <img src="{{ asset('storage/' . $menu->gambar) }}"
                         class="w-48 h-48 object-cover rounded-xl border shadow">
                </div>
            @endif

            {{-- FORM GRID --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                {{-- NAMA --}}
                <div>
                    <label class="block text-sm font-medium mb-1 text-primary">
                        Nama Makanan
                    </label>
                    <input type="text"
                           name="nama_menu"
                           value="{{ old('nama_menu', $menu->nama_menu) }}"
                           placeholder="Nama Makanan"
                           class="w-full rounded-lg border border-gray-300
                                  px-4 py-2
                                  focus:ring-2 focus:ring-primary focus:outline-none text-primary">
                </div>

                {{-- HARGA --}}
                <div>
                    <label class="block text-sm font-medium mb-1 text-primary">
                        Harga
                    </label>
                    <input type="number"
                           name="harga"
                           value="{{ old('harga', $menu->harga) }}"
                           class="w-full rounded-lg border border-gray-300
                                  px-4 py-2
                                  focus:ring-2 focus:ring-primary focus:outline-none text-primary">
                </div>

                {{-- DESKRIPSI --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium mb-1 text-primary">
                        Deskripsi
                    </label>
                    <textarea name="deskripsi"
                              rows="3"
                              placeholder="Deskripsi Makanan"
                              class="w-full rounded-lg border border-gray-300 text-primary
                                     px-4 py-2
                                     focus:ring-2 focus:ring-primary focus:outline-none">{{ old('deskripsi', $menu->deskripsi) }}</textarea>
                </div>

                {{-- STOK --}}
                <div>
                    <label class="block text-sm font-medium mb-1 text-primary">
                        Stok
                    </label>
                    <input type="number"
                           min="0"
                           name="stok"
                           value="{{ old('stok', $menu->stok) }}"
                           class="w-full rounded-lg border border-gray-300 text-primary
                                  px-4 py-2
                                  focus:ring-2 focus:ring-primary focus:outline-none">
                </div>

                {{-- UPLOAD GAMBAR --}}
                <div>
                    <label class="block text-sm font-medium mb-1 text-primary">
                        Upload Gambar
                    </label>
                    <input type="file"
                           name="gambar"
                           id="previewGambar"
                           accept="image/*"
                           class="block w-full text-sm
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-lg file:border-0
                                  file:bg-primary file:text-white
                                  hover:file:bg-primary/80
                                  border rounded-lg cursor-pointer text-primary">
                </div>

            </div>

            {{-- PREVIEW --}}
            <div class="mt-6 flex justify-center">
                <img id="gambarPreview"
                     class="hidden w-40 h-40 object-cover rounded-xl border shadow">
            </div>

            {{-- SUBMIT --}}
            <div class="mt-8">
                <button type="submit"
                        class="w-full bg-primary hover:bg-primary/90
                               text-white font-semibold
                               py-3 rounded-lg
                               transition shadow-lg">
                    Simpan Perubahan
                </button>
            </div>

        </form>

    </div>
</div>

@endsection

@push('extra-script')
<script>
    document.getElementById('previewGambar').addEventListener('change', function (e) {
        const img = document.getElementById('gambarPreview');
        const file = e.target.files[0];

        if (file) {
            img.src = URL.createObjectURL(file);
            img.classList.remove('hidden');
        }
    });
</script>
@endpush
