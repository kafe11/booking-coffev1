@extends('layouts.app')

@section('title', 'Dashboard')


@section('content')
    <main class="flex-grow">
        <section class="w-full h-[60vh] md:h-[80vh] relative carousel-container" id="hero-carousel">
            <div class="carousel-track h-full">
                @for ($i = 1; $i <= 5; $i++)
                    @php $slidePath = public_path('images/slide' . $i . '.jpg'); @endphp
                    <div class="carousel-slide h-auto!">
                        <div class="relative w-full h-full">
                            <div class="absolute inset-0 w-full h-full overflow-hidden z-0">
                                @if (file_exists($slidePath))
                                    <img src="{{ asset('images/slide' . $i . '.jpg') . '?v=' . time() }}"
                                        class="w-full h-full object-cover blur-lg scale-110"
                                        style="filter: blur(20px); transform: scale(1.1);"
                                        alt="Background Blur Slide {{ $i }}" />
                                @else
                                    <img src="https://via.placeholder.com/800x400?text=Foto+Slide+{{ $i }}"
                                        class="w-full h-full object-cover blur-lg scale-110"
                                        style="filter: blur(20px); transform: scale(1.1);"
                                        alt="Background Blur Slide {{ $i }}" />
                                @endif
                            </div>
                            <div class="absolute inset-0 w-full h-full flex items-center justify-center z-10">
                                @if (file_exists($slidePath))
                                    <img alt="Foto Slide {{ $i }}" class="w-full h-full object-contain"
                                        style="aspect-ratio:16/9;"
                                        src="{{ asset('images/slide' . $i . '.jpg') . '?v=' . time() }}" />
                                @else
                                    <img alt="Foto Slide {{ $i }} Default" class="w-full h-full object-contain"
                                        style="aspect-ratio:16/9;"
                                        src="https://via.placeholder.com/800x400?text=Foto+Slide+{{ $i }}" />
                                @endif
                            </div>
                        </div>
                    </div>
                @endfor
            </div>

            <button class="carousel-button left" onclick="moveSlide(-1)">
                <span class="material-symbols-outlined">chevron_left</span>
            </button>
            <button class="carousel-button right" onclick="moveSlide(1)">
                <span class="material-symbols-outlined">chevron_right</span>
            </button>
        </section>

        <section class="py-16 md:py-24">
            <div class="container mx-auto px-6">
                <h2 class="text-4xl md:text-5xl font-display font-bold text-primary text-center mb-12">Tentang Kami</h2>
                
                {{-- Diubah menjadi flex-col agar teks duluan baru foto, dan lg:flex-row untuk desktop --}}
                <div class="flex flex-col lg:flex-row items-center justify-center gap-12 lg:gap-16">
                    
                    {{-- Bagian Teks Keterangan (Posisinya di atas / kiri) --}}
                    <div class="w-full lg:w-1/2 text-center lg:text-left">
                        <h3 class="text-3xl font-display text-primary mb-4">Tentang Cafe</h3>
                        <p class="text-text-muted-light dark:text-text-muted-dark leading-relaxed">
                            {!! file_exists(resource_path('tentang_kami.txt')) ? nl2br(e(file_get_contents(resource_path('tentang_kami.txt')))) : 'Belum ada deskripsi tentang cafe.' !!}
                        </p>
                    </div>

                    {{-- Bagian Foto (Posisinya di bawah / kanan, ukuran maksimal diubah dari max-w-md ke max-w-xl) --}}
                    <div class="w-full lg:w-1/2 flex justify-center perspective-[800px]">
                        @php
                            $fotoTentang = public_path('images/foto_tentang.jpg');
                        @endphp
                        @if (file_exists($fotoTentang))
                            <img alt="Foto Tentang"
                                class="rounded-xl shadow-2xl w-full max-w-xl h-auto object-cover transform transition-transform duration-500 ease-in-out hover:rotate-y-[-10deg] hover:rotate-x-[5deg] hover:scale-105"
                                src="{{ asset('images/foto_tentang.jpg') . '?v=' . time() }}" />
                        @else
                            <img alt="Foto Tentang Default"
                                class="rounded-xl shadow-2xl w-full max-w-xl h-auto object-cover transform transition-transform duration-500 ease-in-out hover:rotate-y-[-10deg] hover:rotate-x-[5deg] hover:scale-105"
                                src="https://via.placeholder.com/600x400?text=Foto+Tentang+Kami" />
                        @endif
                    </div>

                </div>
            </div>
        </section>
    </main>

    {{-- Script yg berada di dalam content, tapi dipush ke bawah body --}}
    @push('extra-scripts')
        <script>
            let currentSlide = 0;
            const slides = document.querySelectorAll('.carousel-slide');
            const track = document.querySelector('.carousel-track');
            const totalSlides = slides.length;

            function updateCarousel() {
                track.style.transform = `translateX(-${currentSlide * 100}%)`;
            }

            function moveSlide(direction) {
                currentSlide = (currentSlide + direction + totalSlides) % totalSlides;
                updateCarousel();
            }
            setInterval(() => {
                moveSlide(1);
            }, 5000);
        </script>
    @endpush
@endsection