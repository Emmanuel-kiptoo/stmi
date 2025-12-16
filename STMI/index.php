<?php
// Fetch events from database
require_once 'config/database.php';

// Fetch upcoming events
$stmt = $pdo->prepare("
    SELECT * FROM admin_events 
    WHERE category = 'upcoming' 
    AND status = 'published'
    AND event_date >= CURDATE()
    ORDER BY event_date ASC 
    LIMIT 3
");
$stmt->execute();
$upcoming_events = $stmt->fetchAll();

// Fetch past events
$stmt = $pdo->prepare("
    SELECT * FROM admin_events 
    WHERE (category = 'past' OR event_date < CURDATE())
    AND status = 'published'
    ORDER BY event_date DESC 
    LIMIT 3
");
$stmt->execute();
$past_events = $stmt->fetchAll();

// Fetch ongoing events
$stmt = $pdo->prepare("
    SELECT * FROM admin_events 
    WHERE category = 'ongoing'
    AND status = 'published'
    ORDER BY event_date DESC 
    LIMIT 3
");
$stmt->execute();
$ongoing_events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soka Toto Muda Initiative Trust</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Event Banner Styles */
        .event-banner {
            margin: 15px 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .event-banner-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        /* Adjust event card layout to accommodate banner */
        .event-card {
            display: flex;
            margin-bottom: 30px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .event-date {
            min-width: 100px;
            background: #0e0c5e;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 15px;
            text-align: center;
        }

        .event-date .day {
            font-size: 32px;
            font-weight: bold;
            line-height: 1;
        }

        .event-date .month {
            font-size: 14px;
            margin-top: 5px;
            opacity: 0.9;
        }

        .event-details {
            flex: 1;
            padding: 25px;
        }

        .event-details h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 1.4rem;
        }

        .event-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-upcoming {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-past {
            background: #f5f5f5;
            color: #666;
        }

        .status-ongoing {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .event-details p {
            color: #666;
            line-height: 1.6;
            margin: 10px 0 20px 0;
        }

        .event-meta {
            display: flex;
            gap: 20px;
            margin: 15px 0;
            color: #666;
            font-size: 14px;
        }

        .event-meta span {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cta-button.small {
            display: inline-block;
            background: #0e0c5e;
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.3s ease;
        }

        .cta-button.small:hover {
            background: #15127a;
        }

        /* Events Grid Layout */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        /* When only one event - make it larger */
        .events-grid:has(.event-card:only-child) {
            grid-template-columns: 1fr;
            max-width: 800px;
            margin: 30px auto;
        }

        .events-grid:has(.event-card:only-child) .event-card {
            display: flex;
            flex-direction: row;
        }

        .events-grid:has(.event-card:only-child) .event-banner-image {
            height: 250px;
        }

        .events-grid:has(.event-card:only-child) .event-details {
            padding: 30px;
        }

        .events-grid:has(.event-card:only-child) .event-details h3 {
            font-size: 1.6rem;
        }

        /* For 3 events - regular size */
        .events-grid:has(.event-card:nth-child(3)) .event-banner-image {
            height: 180px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .event-card {
                flex-direction: column;
            }
            
            .event-date {
                min-width: auto;
                flex-direction: row;
                justify-content: center;
                gap: 20px;
                padding: 15px;
            }
            
            .event-date .day {
                font-size: 24px;
            }
            
            .event-banner-image {
                height: 180px;
            }
            
            .events-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .event-meta {
                flex-direction: column;
                gap: 10px;
            }
            
            .events-grid:has(.event-card:only-child) .event-card {
                flex-direction: column;
            }
            
            .events-grid:has(.event-card:only-child) .event-banner-image {
                height: 200px;
            }
        }

        @media (max-width: 480px) {
            .event-banner-image {
                height: 150px;
            }
            
            .event-details {
                padding: 20px;
            }
            
            .event-details h3 {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'topbars.php'; ?>
    
    <main class="main-content">
        <!-- Your existing slider code -->
        <section class="slider">
            <div class="slider-container">
                <div class="slides">
                    <div class="slide">
                        <img src="images/main.jpg" alt="Slide 1">
                        <div class="text">
                            <h2>Soka Toto Muda Initiative Trust</h2>
                            <p>Transforming Children's Lives through Sports, Creative Arts, and Psychosocial Support for Teen and young Mothers.</p>
                        </div>
                    </div>
                    <div class="slide">
                        <img src="images/mission.jpg" alt="Slide 2">
                        <div class="text">
                            <h2>Our Vision Statement</h2>
                            <p>To empower children with opportunities to explore their talents, receive support with dignity and grow into confident, independent individuals.</p>
                        </div>
                    </div>
                    <div class="slide">
                        <img src="images/vision.jpg" alt="Slide 3">
                        <div class="text">
                            <h2>Our Mission Statement</h2>
                            <p>To holistically transform our children through talent exploration so that they are excellent, independent decision-makers and resourceful people in society.</p>
                        </div>
                    </div>
                </div>
                <div class="buttons">
                    <button id="prev">&#10094;</button>
                    <button id="next">&#10095;</button>
                </div>
            </div>
        </section>

        <!-- Events Section -->
        <section class="events-section">
            <div class="section-title">
                <h2>Our Events</h2>
                <p>Join us in our upcoming events or explore what we've accomplished in the past</p>
            </div>
            
            <!-- Events Tabs -->
            <div class="events-tabs">
                <button class="event-tab active" data-tab="upcoming">Upcoming Events</button>
                <button class="event-tab" data-tab="past">Past Events</button>
                <button class="event-tab" data-tab="ongoing">Ongoing Events</button>
            </div>
            
            <!-- Upcoming Events -->
            <div class="events-content upcoming-events active">
                <div class="events-grid">
                    <?php if (empty($upcoming_events)): ?>
                        <div class="no-events">
                            <i class="fas fa-calendar-times"></i>
                            <h3>No upcoming events scheduled</h3>
                            <p>Check back soon for our upcoming events!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcoming_events as $event): ?>
                        <div class="event-card">
                            <div class="event-date">
                                <span class="day"><?php echo date('d', strtotime($event['event_date'])); ?></span>
                                <span class="month"><?php echo date('F Y', strtotime($event['event_date'])); ?></span>
                            </div>
                            <div class="event-details">
                                <span class="event-status status-upcoming">Upcoming</span>
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                
                                <!-- Banner Display -->
                                <?php if (!empty($event['banner_image'])): ?>
                                <div class="event-banner">
                                    <img src="<?php echo htmlspecialchars($event['banner_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($event['title']); ?>"
                                         class="event-banner-image">
                                </div>
                                <?php endif; ?>
                                
                                <p><?php echo htmlspecialchars(substr($event['description'], 0, 150)) . '...'; ?></p>
                                <div class="event-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                    <span><i class="far fa-clock"></i> 
                                        <?php echo date('g:i A', strtotime($event['start_time'])); ?> - 
                                        <?php echo date('g:i A', strtotime($event['end_time'])); ?>
                                    </span>
                                </div>
                                <?php if (!empty($event['registration_link'])): ?>
                                <a href="<?php echo $event['registration_link']; ?>" class="cta-button small" target="_blank">Register Now</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Past Events -->
            <div class="events-content past-events">
                <div class="events-grid">
                    <?php if (empty($past_events)): ?>
                        <div class="no-events">
                            <i class="fas fa-calendar-times"></i>
                            <h3>No past events to display</h3>
                            <p>We're working on adding our past events.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($past_events as $event): ?>
                        <div class="event-card">
                            <div class="event-date">
                                <span class="day"><?php echo date('d', strtotime($event['event_date'])); ?></span>
                                <span class="month"><?php echo date('F Y', strtotime($event['event_date'])); ?></span>
                            </div>
                            <div class="event-details">
                                <span class="event-status status-past">Past Event</span>
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                
                                <!-- Banner Display -->
                                <?php if (!empty($event['banner_image'])): ?>
                                <div class="event-banner">
                                    <img src="<?php echo htmlspecialchars($event['banner_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($event['title']); ?>"
                                         class="event-banner-image">
                                </div>
                                <?php endif; ?>
                                
                                <p><?php echo htmlspecialchars(substr($event['description'], 0, 150)) . '...'; ?></p>
                                <div class="event-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                    <span><i class="fas fa-check-circle"></i> Completed</span>
                                </div>
                                <a href="#" class="cta-button small">View Photos</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Ongoing Events -->
            <div class="events-content ongoing-events">
                <div class="events-grid">
                    <?php if (empty($ongoing_events)): ?>
                        <div class="no-events">
                            <i class="fas fa-calendar-times"></i>
                            <h3>No ongoing events at the moment</h3>
                            <p>Check our upcoming events for future activities.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($ongoing_events as $event): ?>
                        <div class="event-card">
                            <div class="event-date">
                                <!-- Fixed: Display actual date instead of "Ongoing" -->
                                <span class="day"><?php echo date('d', strtotime($event['event_date'])); ?></span>
                                <span class="month"><?php echo date('F Y', strtotime($event['event_date'])); ?></span>
                            </div>
                            <div class="event-details">
                                <span class="event-status status-ongoing">Ongoing</span>
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                
                                <!-- Banner Display -->
                                <?php if (!empty($event['banner_image'])): ?>
                                <div class="event-banner">
                                    <img src="<?php echo htmlspecialchars($event['banner_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($event['title']); ?>"
                                         class="event-banner-image">
                                </div>
                                <?php endif; ?>
                                
                                <p><?php echo htmlspecialchars(substr($event['description'], 0, 150)) . '...'; ?></p>
                                <div class="event-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                    <span><i class="far fa-clock"></i> 
                                        <?php echo date('g:i A', strtotime($event['start_time'])); ?> - 
                                        <?php echo date('g:i A', strtotime($event['end_time'])); ?>
                                    </span>
                                </div>
                                <?php if (!empty($event['registration_link'])): ?>
                                <a href="<?php echo $event['registration_link']; ?>" class="cta-button small" target="_blank">Join Now</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        
        <!-- Add more sections as needed -->
    </main>
    
    <?php include 'footer.php'; ?>
    
    <script>
        // JavaScript for event tabs
        document.addEventListener('DOMContentLoaded', function() {
            // Event tabs functionality
            const tabButtons = document.querySelectorAll('.event-tab');
            const tabContents = document.querySelectorAll('.events-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Remove active class from all buttons and contents
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to clicked button
                    button.classList.add('active');
                    
                    // Show corresponding content
                    const tabId = button.getAttribute('data-tab');
                    document.querySelector(`.${tabId}-events`).classList.add('active');
                    
                    // Adjust banner sizes after tab switch
                    setTimeout(adjustEventBanners, 50);
                });
            });
            
            // Dynamic banner sizing based on event count
            function adjustEventBanners() {
                const activeContent = document.querySelector('.events-content.active');
                if (!activeContent) return;
                
                const eventGrid = activeContent.querySelector('.events-grid');
                if (!eventGrid) return;
                
                const eventCards = eventGrid.querySelectorAll('.event-card');
                const eventCount = eventCards.length;
                
                // Adjust banner heights
                eventCards.forEach(card => {
                    const banner = card.querySelector('.event-banner-image');
                    if (!banner) return;
                    
                    // Reset inline styles
                    banner.style.height = '';
                    
                    // Apply different heights based on count
                    if (eventCount === 1) {
                        banner.style.height = '250px';
                    } else if (eventCount === 2) {
                        banner.style.height = '200px';
                    } else if (eventCount >= 3) {
                        banner.style.height = '180px';
                    }
                });
            }
            
            // Initialize banner sizes on load
            adjustEventBanners();
            
            // Adjust on window resize
            window.addEventListener('resize', adjustEventBanners);
            
            // Slider functionality
            const slides = document.querySelectorAll('.slide');
            const prevBtn = document.getElementById('prev');
            const nextBtn = document.getElementById('next');
            let currentSlide = 0;
            
            function showSlide(index) {
                slides.forEach(slide => slide.style.display = 'none');
                slides[index].style.display = 'block';
            }
            
            function nextSlide() {
                currentSlide = (currentSlide + 1) % slides.length;
                showSlide(currentSlide);
            }
            
            function prevSlide() {
                currentSlide = (currentSlide - 1 + slides.length) % slides.length;
                showSlide(currentSlide);
            }
            
            // Auto slide every 5 seconds
            let slideInterval = setInterval(nextSlide, 5000);
            
            // Manual controls
            nextBtn.addEventListener('click', () => {
                clearInterval(slideInterval);
                nextSlide();
                slideInterval = setInterval(nextSlide, 5000);
            });
            
            prevBtn.addEventListener('click', () => {
                clearInterval(slideInterval);
                prevSlide();
                slideInterval = setInterval(nextSlide, 5000);
            });
            
            // Initialize first slide
            showSlide(currentSlide);
        });
    </script>
</body>
</html>