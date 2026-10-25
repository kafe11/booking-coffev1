@extends('layouts.admin')

@section('title', 'Tambah Meja')

@section('content')
<div class="flex justify-center px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-2xl bg-white rounded-xl shadow-lg p-6 sm:p-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-center text-primary mb-8">Tambah Meja</h1>

        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-100 text-red-700 px-4 py-3 text-sm">
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('dataMeja.store') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label for="kode_meja" class="block text-sm font-medium mb-1 text-gray-700">Kode Meja</label>
                <input type="text" name="kode_meja" id="kode_meja" value="{{ old('kode_meja') }}" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-black">
            </div>
            <div>
                <label for="ruangan" class="block text-sm font-medium mb-1 text-gray-700">Ruangan</label>
                <select name="ruangan" id="ruangan" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-700">
                    <option value="Indoor" @selected(old('ruangan', 'Indoor') === 'Indoor')>Indoor</option>
                    <option value="Outdoor" @selected(old('ruangan') === 'Outdoor')>Outdoor</option>
                </select>
            </div>
            <div>
                <label for="kapasitas" class="block text-sm font-medium mb-1 text-gray-700">Kapasitas (orang)</label>
                <input type="number" name="kapasitas" id="kapasitas" min="1" max="100" value="{{ old('kapasitas', 4) }}" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-black">
            </div>
            <div class="flex gap-3">
                <a href="{{ route('dataMeja.showMeja') }}" class="flex-1 text-center border rounded-lg py-3">Batal</a>
                <button type="submit" class="flex-1 bg-primary text-white font-semibold py-3 rounded-lg hover:bg-primary/90">Simpan Meja</button>
            </div>
        </form>
    </div>
</div>
@endsection
