@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <!-- Stats Cards Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Total Meja -->
        <div
            class="group relative overflow-hidden rounded-xl bg-white dark:bg-card-light p-6 shadow-sm border border-gray-200 dark:border-white/5 hover:border-primary/50 transition-all duration-300">
            <div class="flex items-start justify-between relative z-10">
                <div class="flex flex-col gap-2">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-800">Total Meja</p>
                    <h3 class="text-4xl font-bold text-gray-900 dark:text-gray-500 tracking-tight"> {{ $totalMeja }}</h3>
                </div>
                <div class="p-3 rounded-lg bg-gray-100 dark:bg-primary/5 text-gray-900 dark:text-dark group-hover:bg-background-dark group-hover:text-background-light transition-colors duration-300">
                    <span class="material-symbols-outlined">table_restaurant</span>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 relative z-10">
                <span>Indoor & Outdoor</span>
            </div>
        </div>

        <!-- Total Pelanggan -->
        <div class="group relative overflow-hidden rounded-xl bg-white dark:bg-card-light p-6 shadow-sm border border-gray-200 dark:border-white/5 hover:border-primary/50 transition-all duration-300">

            <div class="flex items-start justify-between relative z-10">
                <div class="flex flex-col gap-2">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-800">Total Pelanggan</p>
                    <h3 class="text-4xl font-bold text-gray-900 dark:text-gray-500 tracking-tight">{{ $jumlahPelanggan }}</h3>
                </div>
                
                <div class="p-3 rounded-lg bg-gray-100 dark:bg-primary/5 text-gray-900 dark:text-dark group-hover:bg-background-dark group-hover:text-background-light transition-colors duration-300">
                    <span class="material-symbols-outlined">groups</span>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 relative z-10">
                <span>Keseluruhan Pelanggan</span>
            </div>
        </div>

        <!-- Pesanan Masuk -->
        <div
            class="group relative overflow-hidden rounded-xl bg-white dark:bg-card-light p-6 shadow-sm border border-gray-200 dark:border-white/5 hover:border-primary/50 transition-all duration-300">
            <div
                class="absolute -right-6 -top-6 h-32 w-32 bg-primary/10 rounded-full blur-2xl group-hover:bg-primary/20 transition-all">
            </div>
            <div class="flex items-start justify-between relative z-10">
                <div class="flex flex-col gap-2">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-800">Pesanan Masuk</p>
                    <h3 class="text-4xl font-bold text-gray-900 dark:text-gray-500 tracking-tight">{{ $pesananMasuk }}</h3>
                </div>

               <div class="p-3 rounded-lg bg-gray-100 dark:bg-primary/5 text-gray-900 dark:text-dark group-hover:bg-background-dark group-hover:text-background-light transition-colors duration-300">
                    <span class="material-symbols-outlined">receipt_long</span>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 relative z-10">
                <span>Total Reservasi</span>
            </div>
        </div>
    </div>

    <!-- Main Section: Chart & Side List -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Chart Section -->
        <div class="xl:col-span-3 rounded-xl bg-white dark:bg-card-light p-6 shadow-sm border border-gray-200 dark:border-white/5">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-md font-bold text-gray-900 dark:text-grey-800">Grafik Reservasi</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Data reservasi per bulan</p>
                </div>
                <div class="text-right">
                    <h3 class="text-md font-bold text-gray-900 dark:text-grey-800">Total Pendapatan</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-400 tracking-tight">
                        Rp. {{ number_format($totalRevenue, 0, ',', '.') }}
                    </p>
                    {{-- <div class="flex items-center justify-end gap-1 text-sm text-primary font-medium">
                        <span class="material-symbols-outlined text-[16px]">arrow_upward</span>
                        15% vs yesterday
                    </div> --}}
                </div>
            </div>

            <!-- Chart Visualization -->
            <div class="relative w-full h-[320px]">
                <canvas id="reservasiChart"></canvas>
            </div>
        </div>
    </div>
@endsection


@push('extra-scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const chartLabels = @json($labels);
        const chartData = @json($data);

        console.log(chartLabels);
        console.log(chartData);

        document.addEventListener("DOMContentLoaded", () => {
            const ctx = document.getElementById("reservasiChart");

            new Chart(ctx, {
                type: "line",
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: "Reservasi",
                        data: chartData,
                        borderColor: "#19e65e",
                        backgroundColor: "rgba(25, 230, 94, 0.15)",
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 5,
                        pointBackgroundColor: "#19e65e",
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush
