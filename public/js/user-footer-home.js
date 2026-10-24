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