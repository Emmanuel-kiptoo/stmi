<?php
// Fetch events from database
require_once 'config/database.php';

// Fetch upcoming events
$stmt = $pdo->prepare("
    SELECT * FROM events 
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
    SELECT * FROM events 
    WHERE (category = 'past' OR event_date < CURDATE())
    AND status = 'published'
    ORDER BY event_date DESC 
    LIMIT 3
");
$stmt->execute();
$past_events = $stmt->fetchAll();

// Fetch ongoing events
$stmt = $pdo->prepare("
    SELECT * FROM events 
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
                    <p>Transforming Children’s Lives through Sports, Creative Arts, and Psychosocial Support for Teen and young Mothers.</p>
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
                    <?php foreach ($upcoming_events as $event): ?>
                    <div class="event-card">
                        <div class="event-date">
                            <span class="day"><?php echo date('d', strtotime($event['event_date'])); ?></span>
                            <span class="month"><?php echo date('F Y', strtotime($event['event_date'])); ?></span>
                        </div>
                        <div class="event-details">
                            <span class="event-status status-upcoming">Upcoming</span>
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($event['description'], 0, 150)) . '...'; ?></p>
                            <div class="event-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                <span><i class="far fa-clock"></i> 
                                    <?php echo date('g:i A', strtotime($event['start_time'])); ?> - 
                                    <?php echo date('g:i A', strtotime($event['end_time'])); ?>
                                </span>
                            </div>
                            <a href="<?php echo $event['registration_link']; ?>" class="cta-button small">Register Now</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Past Events -->
            <div class="events-content past-events">
                <div class="events-grid">
                    <?php foreach ($past_events as $event): ?>
                    <div class="event-card">
                        <div class="event-date">
                            <span class="day"><?php echo date('d', strtotime($event['event_date'])); ?></span>
                            <span class="month"><?php echo date('F Y', strtotime($event['event_date'])); ?></span>
                        </div>
                        <div class="event-details">
                            <span class="event-status status-past">Past Event</span>
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($event['description'], 0, 150)) . '...'; ?></p>
                            <div class="event-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                <span><i class="fas fa-check-circle"></i> Completed</span>
                            </div>
                            <a href="#" class="cta-button small">View Photos</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Ongoing Events -->
            <div class="events-content ongoing-events">
                <div class="events-grid">
                    <?php foreach ($ongoing_events as $event): ?>
                    <div class="event-card">
                        <div class="event-date">
                            <span class="day">Ongoing</span>
                            <span class="month"><?php echo date('F Y', strtotime($event['event_date'])); ?></span>
                        </div>
                        <div class="event-details">
                            <span class="event-status status-ongoing">Ongoing</span>
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($event['description'], 0, 150)) . '...'; ?></p>
                            <div class="event-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                <span><i class="far fa-clock"></i> Every Week</span>
                            </div>
                            <a href="<?php echo $event['registration_link']; ?>" class="cta-button small">Join Now</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>
    
    <?php include 'footer.php'; ?>
</body>
</html>