let currentSlide = 0;
        const track = document.getElementById('carouselTrack');
        const dots = document.querySelectorAll('.dot');
        const totalSlides = 4;

        function toggleNav() {
            const navbar = document.getElementById('navbar');
            const overlay = document.getElementById('overlay');
            navbar.classList.toggle('open');
            overlay.classList.toggle('show');
        }

        // Close navbar when clicking overlay
        document.getElementById('overlay').addEventListener('click', toggleNav);

        function moveCarousel(direction) {
            currentSlide += direction;
            if (currentSlide < 0) currentSlide = totalSlides - 1;
            if (currentSlide >= totalSlides) currentSlide = 0;
            updateCarousel();
        }

        function goToSlide(index) {
            currentSlide = index;
            updateCarousel();
        }

        function updateCarousel() {
            track.style.transform = `translateX(-${currentSlide * 100}%)`;
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentSlide);
            });
        }

        // Auto-slide every 5 seconds
        setInterval(() => {
            moveCarousel(1);
        }, 5000);