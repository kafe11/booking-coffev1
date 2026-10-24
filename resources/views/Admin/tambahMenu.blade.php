@extends('layouts.admin')

@section('title', 'Tambah Menu')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/form-makanan.css') }}">
@endpush

@section('content')
    <div class="flex justify-center px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-2xl bg-white dark:bg-surface-dark rounded-xl shadow-lg p-6 sm:p-8">

            {{-- ALERT --}}
            @if (session('success'))
                <div class="mb-6 rounded-lg bg-green-100 text-green-700 px-4 py-3 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- TITLE --}}
            <h1 class="text-2xl sm:text-3xl font-bold text-center text-primary mb-8">
                Tambah Menu
            </h1>

            {{-- FORM --}}
            <form action="{{ route('addMenu') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- FORM GRID --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    {{-- NAMA --}}
                    <div>
                        <label for="nama_menu" class="block text-sm font-medium mb-1 text-gray-700">
                            Nama Menu
                        </label>
                        <input type="text" name="nama_menu" id="nama_menu" placeholder="Nama Menu"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2
                               focus:ring-primary focus:border-primary transition text-black">
                    </div>

                    {{-- jenis --}}
                    <input type="hidden" name="jenis" id="jenis" value="{{ $kategori }}">
                    {{-- KATEGORI --}}
                    <div>
                        <label for="kategori" class="block text-sm font-medium mb-1 text-gray-700">
                            Kategori
                        </label>
                        @if ($kategori === 'makanan')
                            <select name="kategori" id="kategori"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2
                               focus:ring-primary focus:border-primary transition text-gray-700">
                                <option value="Dessert">Dessert</option>
                                <option value="Appetizer">Appetizer</option>
                                <option value="Heavy Meal">Heavy Meal</option>
                            </select>
                        @else
                            <select name="kategori" id="kategori"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2
                               focus:ring-primary focus:border-primary transition text-gray-700">
                                {{-- <option value="Soda Based">Soda Based (Mojito & Mocktail)</option> --}}
                                <option value="Non Coffee">Non Coffee</option>
                                <option value="Coffee">Coffee</option>
                            </select>
                        @endif
                    </div>

                    {{-- HARGA --}}
                    <div>
                        <label for="harga" class="block text-sm font-medium mb-1 text-gray-700">
                            Harga
                        </label>
                        <input type="number" name="harga" id="harga"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2
                               focus:ring-primary focus:border-primary transition text-gray-700">
                    </div>

                    {{-- DESKRIPSI --}}
                    <div>
                        <label for="deskripsi" class="block text-sm font-medium mb-1">
                            Deskripsi
                        </label>
                        <input type="text" name="deskripsi" id="deskripsi" placeholder="Deskripsi Menu"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2
                               focus:ring-primary focus:border-primary transition text-gray-700">
                    </div>

                    {{-- STOK --}}
                    <div>
                        <label for="stok" class="block text-sm font-medium mb-1 text-gray-700">
                            Stok
                        </label>
                        <input type="number" name="stok" id="stok" min="0" placeholder="Stok"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2
                               focus:ring-primary focus:border-primary transition text-gray-700">
                    </div>

                </div>

                {{-- UPLOAD GAMBAR --}}
                <div class="mt-6">
                    <label class="block text-sm font-medium mb-1 text-gray-700">
                        Upload Gambar
                    </label>
                    <input type="file" name="gambar" id="previewGambar" accept="image/*"
                        class="w-full rounded-lg border border-gray-300
                           file:mr-4 file:py-2 file:px-4
                           file:rounded-lg file:border-0
                           file:text-sm file:font-medium
                           file:bg-primary file:text-white
                           hover:file:bg-primary/80 transition text-gray-700">
                </div>

                {{-- PREVIEW --}}
                <div class="mt-6 flex justify-center">
                    <img id="gambarPreview" class="hidden w-40 h-40 object-cover rounded-xl border shadow">
                </div>

                {{-- SUBMIT --}}
                <div class="mt-8">
                    <button type="submit"
                        class="w-full bg-primary text-white font-semibold py-3 rounded-lg
                           hover:bg-primary/90 transition shadow-md hover:shadow-lg">
                        Selanjutnya
                    </button>
                </div>

            </form>
        </div>
    </div>
@endsection

@push('extra-script')
    <script>
        document.getElementById('previewGambar').addEventListener('change', function(e) {
            const img = document.getElementById('gambarPreview');
            const file = e.target.files[0];

            if (file) {
                img.src = URL.createObjectURL(file);
                img.classList.remove('hidden');
            }
        });
    </script>
@endpush
