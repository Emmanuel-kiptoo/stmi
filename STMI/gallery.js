// Gallery Tabs Functionality
document.addEventListener('DOMContentLoaded', function () {
    // Gallery Tabs
    const galleryTabs = document.querySelectorAll('.gallery-tab');
    const galleryContents = document.querySelectorAll('.gallery-content');

    galleryTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const tabId = this.getAttribute('data-tab');

            // Remove active class from all tabs and contents
            galleryTabs.forEach(t => t.classList.remove('active'));
            galleryContents.forEach(c => c.classList.remove('active'));

            // Add active class to clicked tab
            this.classList.add('active');

            // Show corresponding content
            const contentElement = document.querySelector(`.${tabId}-content`);
            if (contentElement) {
                contentElement.classList.add('active');
            }

            // Update URL hash for bookmarking
            window.location.hash = `gallery-${tabId}`;
        });
    });

    // Video Play Functionality
    const videoPlayers = document.querySelectorAll('.video-player');

    videoPlayers.forEach(player => {
        const video = player.querySelector('video');
        const playBtn = player.querySelector('.video-play-btn');
        const playIcon = playBtn.querySelector('i');

        if (!video || !playBtn) return;

        playBtn.addEventListener('click', function (e) {
            e.stopPropagation();

            if (video.paused) {
                video.play().catch(error => {
                    console.error('Error playing video:', error);
                });
                playIcon.classList.remove('fa-play');
                playIcon.classList.add('fa-pause');
            } else {
                video.pause();
                playIcon.classList.remove('fa-pause');
                playIcon.classList.add('fa-play');
            }
        });

        video.addEventListener('play', function () {
            playIcon.classList.remove('fa-play');
            playIcon.classList.add('fa-pause');
        });

        video.addEventListener('pause', function () {
            playIcon.classList.remove('fa-pause');
            playIcon.classList.add('fa-play');
        });

        video.addEventListener('click', function (e) {
            e.stopPropagation();
            if (video.paused) {
                video.play().catch(error => {
                    console.error('Error playing video:', error);
                });
            } else {
                video.pause();
            }
        });

        // Handle video loading
        video.addEventListener('loadeddata', function () {
            player.classList.remove('video-loading');
        });

        video.addEventListener('waiting', function () {
            player.classList.add('video-loading');
        });

        video.addEventListener('canplay', function () {
            player.classList.remove('video-loading');
        });
    });

    // Photo hover effects enhancement
    const photoCards = document.querySelectorAll('.photo-card');

    photoCards.forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.zIndex = '10';
        });

        card.addEventListener('mouseleave', function () {
            this.style.zIndex = '1';
        });

        // Keyboard navigation support
        card.addEventListener('keypress', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                this.click();
            }
        });
    });

    // Check URL hash on page load
    function checkUrlHash() {
        const hash = window.location.hash;
        if (hash.includes('gallery-')) {
            const tabId = hash.replace('gallery-', '');
            const tabElement = document.querySelector(`.gallery-tab[data-tab="${tabId}"]`);

            if (tabElement) {
                tabElement.click();
            }
        }
    }

    // Initialize on page load
    checkUrlHash();

    // Handle browser back/forward buttons
    window.addEventListener('hashchange', checkUrlHash);

    // Initialize first tab as active if none are active
    if (document.querySelectorAll('.gallery-tab.active').length === 0 && galleryTabs.length > 0) {
        galleryTabs[0].classList.add('active');
        galleryContents[0].classList.add('active');
    }
});

// Video Analytics Tracking
function trackVideoEvent(videoId, eventType) {
    // This function would typically send data to an analytics service
    console.log(`Video ${videoId}: ${eventType}`);

    // Example implementation for Google Analytics:
    /*
    if (typeof gtag !== 'undefined') {
        gtag('event', eventType, {
            'event_category': 'Video',
            'event_label': videoId,
            'value': 1
        });
    }
    */
}