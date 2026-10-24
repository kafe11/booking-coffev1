@extends('layouts.admin')

@section('title', 'Tambah Makanan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/form-makanan.css') }}">
@endpush

@section('content')
    <div class="main-content" style="max-width: 600px">
        <div class="max-w-2xl  bg-white  dark:bg-surface-dark rounded-lg shadow-lg p-6 sm:p-8 md:p-12">

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <h1 class="text-3xl md:text-4xl font-bold text-center mb-8 text-primary">Tambah Menu Makanan</h1>
            <form action="{{ route('addMenu') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-text-muted-light dark:text-text-muted-dark mb-1"
                            for="nama_menu">Nama Makanan</label>

                        <input
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-background-light dark:bg-background-dark focus:ring-primary focus:border-primary transition duration-150 ease-in-out shadow-sm"
                            id="nama_menu" name="nama_menu" placeholder="Nama Makanan" type="text"/>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-text-muted-light dark:text-text-muted-dark mb-1"
                            for="kategori">Kategori</label>
                        <select id="kategori" name="kategori" class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-background-light dark:bg-background-dark focus:ring-primary focus:border-primary transition duration-150 ease-in-out shadow-sm text-sm font-medium text-text-muted-light dark:text-text-muted-dark ">
                            <option value="makanan">Makanan</option>
                            <option value="minuman">Minuman</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text-muted-light dark:text-text-muted-dark mb-1"
                            for="harga">Harga</label>
                        <input
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-background-light dark:bg-background-dark focus:ring-primary focus:border-primary transition duration-150 ease-in-out shadow-sm"
                            id="harga" name="harga" type="number" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted-light dark:text-text-muted-dark mb-1"
                            for="deskripsi">Deskripsi</label>
                        <input
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-background-light dark:bg-background-dark focus:ring-primary focus:border-primary transition duration-150 ease-in-out shadow-sm text-sm font-medium text-text-muted-light dark:text-text-muted-dark "
                            id="deskripsi" name="deskripsi" placeholder="Deskripsi Makanan"
                            type="text"/>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text-muted-light dark:text-text-muted-dark mb-1"
                            for="stok">Stok</label>
                        <input
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-background-light dark:bg-background-dark focus:ring-primary focus:border-primary transition duration-150 ease-in-out shadow-sm text-sm font-medium text-text-muted-light dark:text-text-muted-dark "
                            id="stok" name="stok"  min="0" placeholder="Stok Makanan"
                            type="number"/>
                    </div>
                </div>
                <div class="mb-3 mt-4">
                    <label class="form-label block text-sm font-medium text-text-muted-light dark:text-text-muted-dark mb-1">Upload Gambar</label>
                    <input 
                        type="file" 
                        name="gambar" 
                        id="previewGambar" 
                        accept="image/*"
                        class="
                            block w-full 
                            rounded-md 
                            border-gray-300 dark:border-gray-600 
                            bg-background-light dark:bg-background-dark 
                            focus:ring-primary focus:border-primary 
                            transition duration-150 ease-in-out shadow-sm 
                            text-sm font-medium text-text-muted-light dark:text-text-muted-dark

                            file:py-2     /* tinggi tombol */
                            file:px-4
                            file:rounded-md
                            file:border-0
                            file:text-sm
                            file:font-medium
                            file:bg-primary
                            file:text-white
                            hover:file:bg-primary/80
                        "
                    >
                </div>

                 <!-- Tempat Preview -->
                <div class="mt-5" style="display: flex; align-items: center; justify-content: center;">
                    <img id="gambarPreview" src="#" alt="Preview Gambar" 
                        style="display:none; width:200px; border-radius:10px;">
                </div>

                <div class="mt-8">
                    <button
                        class="w-full bg-primary text-white font-bold py-3 px-4 rounded-md hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary dark:focus:ring-offset-background-dark transition-all duration-300 ease-in-out shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                        type="submit">
                        Selanjutnya
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('extra-script')
    <script>
        // Menampilkan preview setelah pilih gambar
        document.getElementById('previewGambar').addEventListener('change', function(event) {
            const file = event.target.files[0];

            if (file) {
                const gambarPreview = document.getElementById('gambarPreview');
                gambarPreview.src = URL.createObjectURL(file);
                gambarPreview.style.display = 'block';
            }
        });
    </script>
@endpush
