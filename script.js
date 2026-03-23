
// fonction pour la navbar
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
const overlayElement = document.getElementById('overlay');
if (overlayElement) {
    overlayElement.addEventListener('click', toggleNav);
}

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
    if (!track || dots.length === 0) {
        return;
    }

    track.style.transform = `translateX(-${currentSlide * 100}%)`;
    dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentSlide);
    });
}

// Auto-slide every 5 seconds
if (track && dots.length > 0) {
    setInterval(() => {
        moveCarousel(1);
    }, 5000);
}



// Fonction pour les rows de la navbar
function toggleRow(element) {
    const submenu = element.querySelector('.nav-submenu');
    const arrow = element.querySelector('.arrow');
    
    if (submenu) {
        submenu.classList.toggle('open');
    }
    if (arrow) {
        arrow.classList.toggle('open');
    }
}

