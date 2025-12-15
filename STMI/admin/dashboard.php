<?php
require_once '../config/database.php';
requireAdmin();

// Get statistics
$stats = [];

// Total events
$stmt = $pdo->query("SELECT COUNT(*) as count FROM events");
$stats['total_events'] = $stmt->fetch()['count'];

// Upcoming events
$stmt = $pdo->query("SELECT COUNT(*) as count FROM events WHERE category = 'upcoming' AND event_date >= CURDATE()");
$stats['upcoming_events'] = $stmt->fetch()['count'];

// Unread messages
$stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'");
$stats['unread_messages'] = $stmt->fetch()['count'];

// Total donations
$stmt = $pdo->query("SELECT SUM(amount) as total FROM donations WHERE status = 'completed'");
$stats['total_donations'] = $stmt->fetch()['total'] ?: 0;

// Recent activities
$stmt = $pdo->query("
    (SELECT 'event' as type, title, created_at FROM events ORDER BY created_at DESC LIMIT 3)
    UNION
    (SELECT 'message' as type, name as title, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 3)
    UNION
    (SELECT 'donation' as type, donor_name as title, created_at FROM donations ORDER BY created_at DESC LIMIT 3)
    ORDER BY created_at DESC LIMIT 10
");
$recent_activities = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - STMI Trust</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</p>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #3498db;">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_events']; ?></h3>
                    <p>Total Events</p>
                </div>
                <a href="events.php" class="stat-link">View All</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #2ecc71;">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['upcoming_events']; ?></h3>
                    <p>Upcoming Events</p>
                </div>
                <a href="events.php?filter=upcoming" class="stat-link">View</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #e74c3c;">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['unread_messages']; ?></h3>
                    <p>Unread Messages</p>
                </div>
                <a href="messages.php" class="stat-link">View</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #f39c12;">
                    <i class="fas fa-donate"></i>
                </div>
                <div class="stat-info">
                    <h3>KES <?php echo number_format($stats['total_donations'], 2); ?></h3>
                    <p>Total Donations</p>
                </div>
                <a href="donations.php" class="stat-link">View</a>
            </div>
        </div>
        
        <!-- Recent Activities -->
        <div class="recent-activities">
            <h2>Recent Activities</h2>
            <div class="activity-list">
                <?php foreach ($recent_activities as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <?php if ($activity['type'] === 'event'): ?>
                                <i class="fas fa-calendar-alt"></i>
                            <?php elseif ($activity['type'] === 'message'): ?>
                                <i class="fas fa-envelope"></i>
                            <?php else: ?>
                                <i class="fas fa-donate"></i>
                            <?php endif; ?>
                        </div>
                        <div class="activity-content">
                            <p><?php echo htmlspecialchars($activity['title']); ?></p>
                            <span><?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>