<?php
// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get admin info
require_once '../config/database.php';
$admin_id = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Soka Toto Muda Initiative Trust</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #0e0c5e 0%, #1a1a2e 100%);
            color: white;
            padding: 0 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .menu-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            display: none;
        }
        
        .logo-admin {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo-admin img {
            height: 40px;
            width: auto;
        }
        
        .logo-text h2 {
            font-size: 1.2rem;
            margin: 0;
            color: white;
        }
        
        .logo-text p {
            font-size: 0.8rem;
            margin: 0;
            color: rgba(255,255,255,0.8);
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .admin-info {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            position: relative;
        }
        
        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff9d0b 0%, #ff6b6b 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .admin-details {
            text-align: right;
        }
        
        .admin-name {
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .admin-role {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.7);
        }
        
        .notification-bell {
            position: relative;
            cursor: pointer;
        }
        
        .notification-bell i {
            font-size: 1.3rem;
            color: white;
        }
        
        .notification-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff6b6b;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .dropdown-menu {
            position: absolute;
            top: 60px;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            width: 200px;
            display: none;
            z-index: 1000;
        }
        
        .dropdown-menu.show {
            display: block;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            color: #333;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .dropdown-item:hover {
            background: #f8f9fa;
        }
        
        .dropdown-item i {
            width: 20px;
            color: #666;
        }
        
        .dropdown-divider {
            height: 1px;
            background: #eee;
            margin: 5px 0;
        }
        
        .current-page {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.8);
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
            
            .admin-details {
                display: none;
            }
            
            .current-page {
                display: none;
            }
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="header-container">
            <div class="header-left">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="logo-admin">
                    <!-- You can add your logo here -->
                    <div class="admin-avatar">
                        <?php 
                        // Show initials of admin name
                        $initials = '';
                        if ($admin && $admin['full_name']) {
                            $nameParts = explode(' ', $admin['full_name']);
                            $initials = strtoupper(substr($nameParts[0], 0, 1));
                            if (count($nameParts) > 1) {
                                $initials .= strtoupper(substr($nameParts[1], 0, 1));
                            }
                        } else {
                            $initials = 'A';
                        }
                        echo $initials;
                        ?>
                    </div>
                    
                    <div class="logo-text">
                        <h2>STMI Trust Admin</h2>
                        <p>Soka Toto Muda Initiative</p>
                    </div>
                </div>
            </div>
            
            <div class="header-right">
                <div class="current-page">
                    <i class="fas fa-folder"></i>
                    <?php 
                    $current_page = basename($_SERVER['PHP_SELF']);
                    $page_names = [
                        'dashboard.php' => 'Dashboard',
                        'events.php' => 'Events',
                        'messages.php' => 'Messages',
                        'donations.php' => 'Donations',
                        'team.php' => 'Team',
                        'gallery.php' => 'Gallery',
                        'users.php' => 'Users',
                        'settings.php' => 'Settings'
                    ];
                    echo $page_names[$current_page] ?? 'Admin Panel';
                    ?>
                </div>
                
                <div class="notification-bell" id="notificationBell">
                    <i class="fas fa-bell"></i>
                    <span class="notification-count" id="notificationCount">0</span>
                </div>
                
                <div class="admin-info" id="adminDropdown">
                    <div class="admin-avatar">
                        <?php echo $initials; ?>
                    </div>
                    <div class="admin-details">
                        <div class="admin-name">
                            <?php echo $admin ? htmlspecialchars($admin['full_name']) : 'Administrator'; ?>
                        </div>
                        <div class="admin-role">
                            <?php echo $admin ? ucfirst($admin['role']) : 'Admin'; ?>
                        </div>
                    </div>
                    <i class="fas fa-chevron-down" style="font-size: 0.8rem;"></i>
                    
                    <!-- Dropdown Menu -->
                    <div class="dropdown-menu" id="dropdownMenu">
                        <a href="dashboard.php" class="dropdown-item">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            My Profile
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="settings.php" class="dropdown-item">
                            <i class="fas fa-cog"></i>
                            Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <script>
        // Toggle sidebar on mobile
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
        
        // Toggle dropdown menu
        document.getElementById('adminDropdown').addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('dropdownMenu').classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#adminDropdown')) {
                document.getElementById('dropdownMenu').classList.remove('show');
            }
        });
        
        // Notification bell click
        document.getElementById('notificationBell').addEventListener('click', function() {
            // Fetch notifications
            fetch('handlers/get_notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show notifications in a modal
                        showNotifications(data.notifications);
                        // Mark as read
                        fetch('handlers/mark_notifications_read.php');
                        document.getElementById('notificationCount').textContent = '0';
                    }
                });
        });
        
        function showNotifications(notifications) {
            // Create notifications modal
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.innerHTML = `
                <div class="modal-content" style="max-width: 400px;">
                    <div class="modal-header">
                        <h3>Notifications</h3>
                        <button class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body">
                        ${notifications.length > 0 ? 
                            notifications.map(n => `
                                <div class="notification-item">
                                    <strong>${n.title}</strong>
                                    <p>${n.message}</p>
                                    <small>${n.time_ago}</small>
                                </div>
                            `).join('') :
                            '<p class="text-center">No new notifications</p>'
                        }
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            modal.style.display = 'flex';
            
            // Close modal
            modal.querySelector('.modal-close').addEventListener('click', () => {
                modal.remove();
            });
            
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                }
            });
        }
        
        // Fetch notification count on page load
        document.addEventListener('DOMContentLoaded', function() {
            fetch('handlers/get_notification_count.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.count > 0) {
                        document.getElementById('notificationCount').textContent = data.count;
                    }
                });
        });
    </script>