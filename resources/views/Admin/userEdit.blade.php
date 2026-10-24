@extends('layouts.admin')

@section('title', 'Edit User')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/form-makanan.css') }}">
@endpush

@section('content')
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl">

            {{-- ALERT SUCCESS --}}
            @if (session('success'))
                <div
                    class="mb-6 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-5 py-4 text-green-700 shadow-sm">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            {{-- CARD --}}
            <div class="rounded-xl bg-white dark:bg-surface-dark shadow-lg p-6 sm:p-8">

                <h1 class="mb-8 text-center text-2xl sm:text-3xl font-bold text-primary">
                    Edit User
                </h1>

                <form action="{{ route('updateUser', $userEdit->id_pelanggan) }}" method="POST">
                    @csrf

                    {{-- FORM GRID --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- NAMA --}}
                        <div>
                            <label for="nama_pelanggan"
                                class="mb-1 block text-sm font-medium text-text-muted-light dark:text-text-muted-dark text-gray-700">
                                Nama Pelanggan
                            </label>
                            <input type="text" id="nama_pelanggan" name="nama_pelanggan"
                                value="{{ old('nama_pelanggan', $userEdit->nama_pelanggan) }}"
                                placeholder="Nama Pelanggan"
                                class="w-full rounded-md border-gray-300 
                                px-3 py-2
                                focus:ring-primary focus:border-primary transition text-gray-700">
                        </div>

                        {{-- TELEPON --}}
                        <div>
                            <label for="no_telepon"
                                class="mb-1 block text-sm font-medium text-text-muted-light dark:text-text-muted-dark text-gray-700">
                                No. Telepon
                            </label>
                            <input type="text" id="no_telepon" name="no_telepon"
                                value="{{ old('no_telepon', $userEdit->no_telepon) }}"
                                placeholder="No Telepon"
                                class="w-full rounded-md border-gray-300 
                                px-3 py-2
                                focus:ring-primary focus:border-primary transition text-gray-700">
                        </div>

                        {{-- USERNAME --}}
                        <div class="md:col-span-2">
                            <label for="username"
                                class="mb-1 block text-sm font-medium text-text-muted-light dark:text-text-muted-dark text-gray-700">
                                Username
                            </label>
                            <input type="text" id="username" name="username"
                                value="{{ old('username', $userEdit->username) }}"
                                placeholder="Username"
                                class="w-full rounded-md border-gray-300 
                                px-3 py-2
                                focus:ring-primary focus:border-primary transition text-gray-700">
                        </div>

                    </div>

                    {{-- BUTTON --}}
                    <div class="mt-8">
                        <button type="submit"
                            class="w-full rounded-md bg-primary py-3 text-white font-semibold
                                   hover:opacity-90 focus:outline-none focus:ring-2
                                   focus:ring-primary focus:ring-offset-2
                                   transition-all shadow-md hover:shadow-lg">
                            Simpan Perubahan
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection
