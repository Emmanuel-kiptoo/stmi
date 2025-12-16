<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

// Get dashboard statistics
$stats = [];

// Total Events
$stmt = $pdo->query("SELECT COUNT(*) as total FROM admin_events");
$stats['events'] = $stmt->fetch()['total'];

// Total Team Members
$stmt = $pdo->query("SELECT COUNT(*) as total FROM admin_team WHERE status = 'active'");
$stats['team'] = $stmt->fetch()['total'];

// Total Donations
$stmt = $pdo->query("SELECT SUM(amount) as total FROM admin_donations WHERE status = 'confirmed'");
$stats['donations'] = $stmt->fetch()['total'] ?: 0;

// Unread Messages
$stmt = $pdo->query("SELECT COUNT(*) as total FROM admin_contacts WHERE status = 'unread'");
$stats['messages'] = $stmt->fetch()['total'];

// Recent Events
$stmt = $pdo->prepare("SELECT * FROM admin_events ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recentEvents = $stmt->fetchAll();

// Recent Donations
$stmt = $pdo->prepare("SELECT * FROM admin_donations ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recentDonations = $stmt->fetchAll();

// Recent Messages
$stmt = $pdo->prepare("SELECT * FROM admin_contacts ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recentMessages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Soka Toto Muda Initiative Trust</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <!-- Welcome Header -->
            <div class="dashboard-header">
                <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_full_name']); ?>!</h1>
                <p>Here's what's happening with your organization today.</p>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #667eea;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['events']); ?></h3>
                        <p>Total Events</p>
                    </div>
                    <a href="events.php" class="stat-link">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #764ba2;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['team']); ?></h3>
                        <p>Team Members</p>
                    </div>
                    <a href="team.php" class="stat-link">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #57cc99;">
                        <i class="fas fa-hand-holding-heart"></i>
                    </div>
                    <div class="stat-info">
                        <h3>KES <?php echo number_format($stats['donations'], 2); ?></h3>
                        <p>Total Donations</p>
                    </div>
                    <a href="donations.php" class="stat-link">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #ff9d0b;">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['messages']); ?></h3>
                        <p>Unread Messages</p>
                    </div>
                    <a href="messages.php" class="stat-link">View All <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            
            <!-- Recent Activity Section -->
            <div class="recent-activity">
                <div class="recent-column">
                    <div class="recent-header">
                        <h3><i class="fas fa-calendar-alt"></i> Recent Events</h3>
                        <a href="events.php?action=add" class="btn btn-sm btn-primary">Add New</a>
                    </div>
                    <div class="recent-list">
                        <?php if (empty($recentEvents)): ?>
                            <p class="empty-message">No events found.</p>
                        <?php else: ?>
                            <?php foreach ($recentEvents as $event): ?>
                                <div class="recent-item">
                                    <div class="item-icon">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                    <div class="item-content">
                                        <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                        <p><?php echo date('M d, Y', strtotime($event['event_date'])); ?> • <?php echo htmlspecialchars($event['category']); ?></p>
                                    </div>
                                    <div class="item-status">
                                        <span class="status-badge status-<?php echo $event['status']; ?>">
                                            <?php echo ucfirst($event['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="recent-column">
                    <div class="recent-header">
                        <h3><i class="fas fa-hand-holding-heart"></i> Recent Donations</h3>
                        <a href="donations.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="recent-list">
                        <?php if (empty($recentDonations)): ?>
                            <p class="empty-message">No donations yet.</p>
                        <?php else: ?>
                            <?php foreach ($recentDonations as $donation): ?>
                                <div class="recent-item">
                                    <div class="item-icon">
                                        <i class="fas fa-donate"></i>
                                    </div>
                                    <div class="item-content">
                                        <h4><?php echo htmlspecialchars($donation['donor_name']); ?></h4>
                                        <p>KES <?php echo number_format($donation['amount'], 2); ?> • <?php echo ucfirst($donation['payment_method']); ?></p>
                                    </div>
                                    <div class="item-status">
                                        <span class="status-badge status-<?php echo $donation['status']; ?>">
                                            <?php echo ucfirst($donation['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="recent-column">
                    <div class="recent-header">
                        <h3><i class="fas fa-envelope"></i> Recent Messages</h3>
                        <a href="messages.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="recent-list">
                        <?php if (empty($recentMessages)): ?>
                            <p class="empty-message">No messages yet.</p>
                        <?php else: ?>
                            <?php foreach ($recentMessages as $message): ?>
                                <div class="recent-item">
                                    <div class="item-icon">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div class="item-content">
                                        <h4><?php echo htmlspecialchars($message['name']); ?></h4>
                                        <p><?php echo htmlspecialchars(substr($message['subject'], 0, 50)); ?></p>
                                    </div>
                                    <div class="item-status">
                                        <span class="status-badge status-<?php echo $message['status']; ?>">
                                            <?php echo ucfirst($message['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3>Quick Actions</h3>
                <div class="actions-grid">
                    <a href="events.php?action=add" class="action-card">
                        <i class="fas fa-calendar-plus"></i>
                        <span>Add New Event</span>
                    </a>
                    <a href="team.php?action=add" class="action-card">
                        <i class="fas fa-user-plus"></i>
                        <span>Add Team Member</span>
                    </a>
                    <a href="media.php?action=upload" class="action-card">
                        <i class="fas fa-upload"></i>
                        <span>Upload Media</span>
                    </a>
                    <a href="settings.php" class="action-card">
                        <i class="fas fa-cog"></i>
                        <span>Site Settings</span>
                    </a>
                </div>
            </div>
        </main>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>