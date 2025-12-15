// Select elements
const slides = document.querySelector('.slides');
const slideElements = document.querySelectorAll('.slide');
const next = document.getElementById('next');

let index = 0;
const slideInterval = 4000;

// Clone first slide and append it
const firstClone = slideElements[0].cloneNode(true);
slides.appendChild(firstClone);

const totalSlides = slides.children.length;

// Set transition
slides.style.transition = 'transform 0.6s ease-in-out';

// Move slider
function moveSlide() {
    index++;
    slides.style.transform = `translateX(${-index * 100}%)`;

    // When we reach the clone
    if (index === totalSlides - 1) {
        setTimeout(() => {
            slides.style.transition = 'none'; // remove animation
            index = 0;
            slides.style.transform = 'translateX(0)';
        }, 600); // must match transition duration

        setTimeout(() => {
            slides.style.transition = 'transform 0.6s ease-in-out';
        }, 650);
    }
}

// Auto slide (FORWARD ONLY)
let autoSlide = setInterval(moveSlide, slideInterval);

// Optional next button
if (next) {
    next.addEventListener('click', () => {
        clearInterval(autoSlide);
        moveSlide();
        autoSlide = setInterval(moveSlide, slideInterval);
    });
}

// Adjust slide height
function adjustSlideHeight() {
    slides.querySelectorAll('.slide').forEach(slide => {
        slide.style.height = `${window.innerHeight}px`;
    });
}

window.addEventListener('load', adjustSlideHeight);
window.addEventListener('resize', adjustSlideHeight);
