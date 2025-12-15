// Events Tabs Functionality
document.addEventListener('DOMContentLoaded', function () {
    const eventTabs = document.querySelectorAll('.event-tab');
    const eventContents = document.querySelectorAll('.events-content');

    // Function to switch tabs
    function switchTab(tabId) {
        // Remove active class from all tabs and contents
        eventTabs.forEach(t => t.classList.remove('active'));
        eventContents.forEach(c => c.classList.remove('active'));

        // Add active class to clicked tab
        const activeTab = document.querySelector(`.event-tab[data-tab="${tabId}"]`);
        if (activeTab) {
            activeTab.classList.add('active');
        }

        // Show corresponding content
        const contentElement = document.querySelector(`.${tabId}-events`);
        if (contentElement) {
            contentElement.classList.add('active');
        }

        // Store active tab in sessionStorage for persistence
        sessionStorage.setItem('activeEventTab', tabId);
    }

    // Add click event to each tab
    eventTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const tabId = this.getAttribute('data-tab');
            switchTab(tabId);
        });
    });

    // Check for saved tab preference
    const savedTab = sessionStorage.getItem('activeEventTab');
    if (savedTab) {
        switchTab(savedTab);
    } else {
        // Initialize first tab as active
        if (eventTabs.length > 0) {
            eventTabs[0].classList.add('active');
        }
        if (eventContents.length > 0) {
            eventContents[0].classList.add('active');
        }
    }

    // Event card animations
    const eventCards = document.querySelectorAll('.event-card');

    // Add intersection observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Apply observer to event cards
    eventCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(card);
    });

    // Handle tab keyboard navigation
    eventTabs.forEach(tab => {
        tab.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                tab.click();
            }
        });
    });
});