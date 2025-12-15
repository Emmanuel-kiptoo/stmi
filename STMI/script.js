// Slider Functionality with Text Animations
document.addEventListener('DOMContentLoaded', function () {
    // Slider Elements
    const sliderContainer = document.querySelector('.slider-container');
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.slider-dot');
    const prevBtn = document.querySelector('.slider-arrow.prev');
    const nextBtn = document.querySelector('.slider-arrow.next');
    const progressBar = document.querySelector('.slider-progress-bar');
    const slideCounter = document.querySelector('.slide-counter');

    let currentSlide = 0;
    const totalSlides = slides.length;
    let slideInterval;
    let isAnimating = false;

    // Update slide counter
    function updateSlideCounter() {
        if (slideCounter) {
            slideCounter.textContent = `${currentSlide + 1} / ${totalSlides}`;
        }
    }

    // Reset animations for all slides
    function resetAllAnimations() {
        slides.forEach(slide => {
            const elements = slide.querySelectorAll('[class*="slide-"]');
            elements.forEach(el => {
                el.style.animation = 'none';
                el.style.opacity = '0';
            });
        });
    }

    // Trigger animations for active slide
    function triggerSlideAnimations() {
        const activeSlide = slides[currentSlide];

        // Reset and trigger animations
        const heading = activeSlide.querySelector('.slide-heading');
        const text = activeSlide.querySelector('.slide-text');
        const quote = activeSlide.querySelector('.slide-quote');
        const icon = activeSlide.querySelector('.slide-icon');

        // Reset animations
        [heading, text, quote, icon].forEach(el => {
            if (el) {
                el.style.animation = 'none';
                el.style.opacity = '0';
            }
        });

        // Trigger reflow and start animations
        setTimeout(() => {
            if (heading) {
                heading.style.animation = 'slideUpFadeIn 1s ease-out 0.3s forwards';
            }
            if (icon) {
                icon.style.animation = 'scaleFadeIn 1s ease-out 0.2s forwards';
            }
            if (text) {
                text.style.animation = 'slideUpFadeIn 1s ease-out 0.6s forwards';
            }
            if (quote) {
                quote.style.animation = 'slideUpFadeIn 1s ease-out 0.9s forwards';
            }
        }, 50);
    }

    // Initialize Slider
    function updateSlider(animate = true) {
        if (isAnimating) return;
        isAnimating = true;

        // Move slider container
        sliderContainer.style.transform = `translateX(-${currentSlide * 100}%)`;

        // Update active classes
        slides.forEach((slide, index) => {
            slide.classList.toggle('active', index === currentSlide);
        });

        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentSlide);
        });

        // Reset and restart progress bar
        resetProgressBar();

        // Update slide counter
        updateSlideCounter();

        // Trigger animations
        if (animate) {
            triggerSlideAnimations();
        }

        // Allow next animation after delay
        setTimeout(() => {
            isAnimating = false;
        }, 1000);
    }

    // Next Slide
    function nextSlide() {
        currentSlide = (currentSlide + 1) % totalSlides;
        updateSlider();
        resetAutoRotation();
    }

    // Previous Slide
    function prevSlide() {
        currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
        updateSlider();
        resetAutoRotation();
    }

    // Go to specific slide
    function goToSlide(index) {
        if (index === currentSlide) return;
        currentSlide = index;
        updateSlider();
        resetAutoRotation();
    }

    // Reset progress bar
    function resetProgressBar() {
        if (progressBar) {
            progressBar.style.transition = 'none';
            progressBar.style.width = '0%';

            setTimeout(() => {
                progressBar.style.transition = 'width 5s linear';
                progressBar.classList.remove('active');
                void progressBar.offsetWidth;
                progressBar.classList.add('active');
            }, 10);
        }
    }

    // Start auto rotation
    function startAutoRotation() {
        clearInterval(slideInterval);
        slideInterval = setInterval(nextSlide, 5000);

        if (progressBar) {
            progressBar.classList.add('active');
        }
    }

    // Reset auto rotation
    function resetAutoRotation() {
        startAutoRotation();
    }

    // Event Listeners
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            if (!isAnimating) prevSlide();
        });

        prevBtn.addEventListener('mouseenter', () => {
            clearInterval(slideInterval);
            if (progressBar) {
                progressBar.style.animationPlayState = 'paused';
            }
        });

        prevBtn.addEventListener('mouseleave', () => {
            resetAutoRotation();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            if (!isAnimating) nextSlide();
        });

        nextBtn.addEventListener('mouseenter', () => {
            clearInterval(slideInterval);
            if (progressBar) {
                progressBar.style.animationPlayState = 'paused';
            }
        });

        nextBtn.addEventListener('mouseleave', () => {
            resetAutoRotation();
        });
    }

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            if (!isAnimating) goToSlide(index);
        });

        dot.addEventListener('mouseenter', () => {
            clearInterval(slideInterval);
            if (progressBar) {
                progressBar.style.animationPlayState = 'paused';
            }
        });

        dot.addEventListener('mouseleave', () => {
            resetAutoRotation();
        });
    });

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft' && !isAnimating) {
            prevSlide();
        } else if (e.key === 'ArrowRight' && !isAnimating) {
            nextSlide();
        }
    });

    // Touch/swipe support
    let startX = 0;
    let isDragging = false;

    if (sliderContainer) {
        sliderContainer.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            isDragging = true;
            clearInterval(slideInterval);
            if (progressBar) {
                progressBar.style.animationPlayState = 'paused';
            }
        });

        sliderContainer.addEventListener('touchmove', (e) => {
            if (!isDragging || isAnimating) return;
            e.preventDefault();
        });

        sliderContainer.addEventListener('touchend', (e) => {
            if (!isDragging || isAnimating) return;
            isDragging = false;

            const endX = e.changedTouches[0].clientX;
            const diff = startX - endX;
            const threshold = 50;

            if (Math.abs(diff) > threshold) {
                if (diff > 0) {
                    nextSlide();
                } else {
                    prevSlide();
                }
            }

            setTimeout(resetAutoRotation, 1000);
        });
    }

    // Pause on hover
    if (sliderContainer) {
        sliderContainer.addEventListener('mouseenter', () => {
            clearInterval(slideInterval);
            if (progressBar) {
                progressBar.style.animationPlayState = 'paused';
            }
        });

        sliderContainer.addEventListener('mouseleave', () => {
            resetAutoRotation();
        });
    }

    // Handle page visibility
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(slideInterval);
            if (progressBar) {
                progressBar.style.animationPlayState = 'paused';
            }
        } else {
            resetAutoRotation();
        }
    });

    // Initialize
    resetAllAnimations();
    updateSlideCounter();
    updateSlider(false); // Don't animate on initial load
    triggerSlideAnimations(); // Trigger initial animations
    startAutoRotation();

    // Handle window resize
    window.addEventListener('resize', () => {
        sliderContainer.style.transform = `translateX(-${currentSlide * 100}%)`;
    });
});
const slides = document.querySelector('.slides');
const slideCount = document.querySelectorAll('.slide').length;
const prev = document.getElementById('prev');
const next = document.getElementById('next');
let index = 0;

function showSlide(i) {
    if (i < 0) index = slideCount - 1;
    else if (i >= slideCount) index = 0;
    else index = i;
    slides.style.transform = 'translateX(' + (-index * 300) + 'px)';
}

prev.addEventListener('click', () => showSlide(index - 1));
next.addEventListener('click', () => showSlide(index + 1));

// Auto slide every 3 seconds
setInterval(() => showSlide(index + 1), 3000);
