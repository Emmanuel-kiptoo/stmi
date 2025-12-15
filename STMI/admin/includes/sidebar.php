<nav class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <h2><i class="fas fa-cog"></i> STMI Admin</h2>
            <p>Soka Toto Muda Initiative Trust</p>
        </div>
    </div>
    
    <ul class="nav-menu">
        <!-- Dashboard -->
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <!-- Events Management -->
        <li class="nav-item has-submenu">
            <a href="#" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['events.php', 'add_event.php', 'edit_event.php']) ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i>
                <span>Events</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <ul class="submenu">
                <li><a href="events.php?filter=upcoming" class="<?php echo basename($_SERVER['PHP_SELF']) == 'events.php' && ($_GET['filter'] ?? '') == 'upcoming' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-plus"></i> Upcoming Events
                </a></li>
                <li><a href="events.php?filter=past" class="<?php echo basename($_SERVER['PHP_SELF']) == 'events.php' && ($_GET['filter'] ?? '') == 'past' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check"></i> Past Events
                </a></li>
                <li><a href="events.php?filter=all" class="<?php echo basename($_SERVER['PHP_SELF']) == 'events.php' && ($_GET['filter'] ?? '') == 'all' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar"></i> All Events
                </a></li>
                <li><a href="add_event.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'add_event.php' ? 'active' : ''; ?>">
                    <i class="fas fa-plus-circle"></i> Add New Event
                </a></li>
            </ul>
        </li>
        
        <!-- About Page Content -->
        <li class="nav-item has-submenu">
            <a href="#" class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'about') !== false || basename($_SERVER['PHP_SELF']) == 'team.php' ? 'active' : ''; ?>">
                <i class="fas fa-info-circle"></i>
                <span>About Content</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <ul class="submenu">
                <li><a href="about_editor.php?section=who-we-are">
                    <i class="fas fa-users"></i> Who We Are
                </a></li>
                <li><a href="about_editor.php?section=core-values">
                    <i class="fas fa-heart"></i> Core Values
                </a></li>
                <li><a href="about_editor.php?section=our-programs">
                    <i class="fas fa-project-diagram"></i> Our Programs
                </a></li>
                <li><a href="team.php">
                    <i class="fas fa-user-tie"></i> Team Members
                </a></li>
                <li><a href="about_editor.php?section=history">
                    <i class="fas fa-history"></i> History
                </a></li>
            </ul>
        </li>
        
        <!-- Media Management -->
        <li class="nav-item has-submenu">
            <a href="#" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['articles.php', 'newsletters.php', 'resources.php', 'gallery.php', 'reports.php']) ? 'active' : ''; ?>">
                <i class="fas fa-photo-video"></i>
                <span>Media</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <ul class="submenu">
                <li><a href="articles.php">
                    <i class="fas fa-newspaper"></i> Articles
                </a></li>
                <li><a href="newsletters.php">
                    <i class="fas fa-envelope-open-text"></i> Newsletters
                </a></li>
                <li><a href="resources.php">
                    <i class="fas fa-book"></i> Resources
                </a></li>
                <li><a href="gallery.php">
                    <i class="fas fa-images"></i> Gallery
                </a></li>
                <li><a href="reports.php">
                    <i class="fas fa-chart-bar"></i> Reports
                </a></li>
                <li><a href="media_upload.php">
                    <i class="fas fa-upload"></i> Upload Media
                </a></li>
            </ul>
        </li>
        
        <!-- Contact Messages -->
        <li class="nav-item">
            <a href="messages.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i>
                <span>Messages</span>
                <?php
                // Get unread messages count
                require_once '../config/database.php';
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'");
                $stmt->execute();
                $unread = $stmt->fetch()['count'];
                if ($unread > 0): ?>
                <span class="badge"><?php echo $unread; ?></span>
                <?php endif; ?>
            </a>
        </li>
        
        <!-- Donations -->
        <li class="nav-item has-submenu">
            <a href="#" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['donations.php', 'donation_campaigns.php', 'add_donation.php']) ? 'active' : ''; ?>">
                <i class="fas fa-donate"></i>
                <span>Donations</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <ul class="submenu">
                <li><a href="donations.php">
                    <i class="fas fa-list"></i> All Donations
                </a></li>
                <li><a href="donations.php?filter=pending">
                    <i class="fas fa-clock"></i> Pending
                </a></li>
                <li><a href="donation_campaigns.php">
                    <i class="fas fa-bullhorn"></i> Campaigns
                </a></li>
                <li><a href="add_donation.php">
                    <i class="fas fa-plus"></i> Add Donation
                </a></li>
                <li><a href="mpesa_confirm.php">
                    <i class="fas fa-mobile-alt"></i> MPESA Confirm
                </a></li>
            </ul>
        </li>
        
        <!-- User Management -->
        <li class="nav-item">
            <a href="users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users-cog"></i>
                <span>Users</span>
            </a>
        </li>
        
        <!-- Settings -->
        <li class="nav-item">
            <a href="settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cogs"></i>
                <span>Settings</span>
            </a>
        </li>
        
        <!-- Logout -->
        <li class="nav-item">
            <a href="logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
    
    <!-- Quick Stats -->
    <div class="sidebar-stats">
        <h4>Quick Stats</h4>
        <div class="stats">
            <?php
            // Get quick stats
            $today = date('Y-m-d');
            
            // Today's events
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE event_date = ?");
            $stmt->execute([$today]);
            $today_events = $stmt->fetchColumn();
            
            // This month's donations
            $stmt = $pdo->prepare("SELECT SUM(amount) FROM donations WHERE status = 'completed' AND MONTH(created_at) = MONTH(CURRENT_DATE())");
            $stmt->execute();
            $month_donations = $stmt->fetchColumn();
            ?>
            
            <div class="stat-item">
                <i class="fas fa-calendar-day"></i>
                <div>
                    <strong><?php echo $today_events; ?></strong>
                    <span>Today's Events</span>
                </div>
            </div>
            
            <div class="stat-item">
                <i class="fas fa-money-bill-wave"></i>
                <div>
                    <strong>KES <?php echo number_format($month_donations ?: 0, 0); ?></strong>
                    <span>This Month</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- System Info -->
    <div class="system-info">
        <p><i class="fas fa-server"></i> Server: <?php echo $_SERVER['SERVER_NAME']; ?></p>
        <p><i class="fas fa-clock"></i> Last Login: 
            <?php 
            if ($admin && $admin['last_login']) {
                echo date('M d, H:i', strtotime($admin['last_login']));
            } else {
                echo 'First time';
            }
            ?>
        </p>
    </div>
</nav>

<style>
    /* Sidebar Styling */
    .sidebar {
        width: 250px;
        background: linear-gradient(180deg, #0e0c5e, #1a1a2e);
        color: white;
        position: fixed;
        left: 0;
        top: 70px; /* Height of header */
        height: calc(100vh - 70px);
        overflow-y: auto;
        transition: all 0.3s ease;
        z-index: 99;
    }
    
    .sidebar-header {
        padding: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .logo h2 {
        font-size: 1.2rem;
        margin-bottom: 5px;
        color: white;
    }
    
    .logo p {
        font-size: 0.8rem;
        color: rgba(255,255,255,0.7);
        margin: 0;
    }
    
    .nav-menu {
        list-style: none;
        padding: 20px 0;
    }
    
    .nav-item {
        margin: 2px 0;
    }
    
    .nav-link {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: rgba(255,255,255,0.8);
        text-decoration: none;
        transition: all 0.3s;
        border-left: 4px solid transparent;
        position: relative;
    }
    
    .nav-link:hover, .nav-link.active {
        background: rgba(255,255,255,0.1);
        color: white;
        border-left-color: #ff9d0b;
    }
    
    .nav-link i:first-child {
        width: 20px;
        text-align: center;
        margin-right: 10px;
        font-size: 1.1rem;
    }
    
    .nav-link i.fa-chevron-down {
        margin-left: auto;
        font-size: 0.8rem;
        transition: transform 0.3s;
    }
    
    .nav-item.has-submenu.active .nav-link i.fa-chevron-down {
        transform: rotate(180deg);
    }
    
    .badge {
        background: #ff6b6b;
        color: white;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 0.7rem;
        margin-left: auto;
    }
    
    /* Submenu */
    .submenu {
        list-style: none;
        background: rgba(0,0,0,0.2);
        display: none;
        padding-left: 20px;
    }
    
    .nav-item.has-submenu.active .submenu {
        display: block;
    }
    
    .submenu li a {
        display: flex;
        align-items: center;
        padding: 10px 20px;
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.3s;
        border-left: 2px solid transparent;
    }
    
    .submenu li a:hover, .submenu li a.active {
        color: white;
        background: rgba(255,255,255,0.05);
        border-left-color: #57cc99;
    }
    
    .submenu li a i {
        width: 20px;
        text-align: center;
        margin-right: 8px;
        font-size: 0.9rem;
    }
    
    /* Sidebar Stats */
    .sidebar-stats {
        padding: 20px;
        border-top: 1px solid rgba(255,255,255,0.1);
        margin-top: 20px;
    }
    
    .sidebar-stats h4 {
        font-size: 0.9rem;
        color: rgba(255,255,255,0.7);
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .stat-item {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        padding: 10px;
        background: rgba(255,255,255,0.05);
        border-radius: 5px;
    }
    
    .stat-item i {
        font-size: 1.2rem;
        color: #ff9d0b;
    }
    
    .stat-item div {
        flex: 1;
    }
    
    .stat-item strong {
        display: block;
        font-size: 1.1rem;
        color: white;
    }
    
    .stat-item span {
        font-size: 0.8rem;
        color: rgba(255,255,255,0.6);
    }
    
    /* System Info */
    .system-info {
        padding: 20px;
        border-top: 1px solid rgba(255,255,255,0.1);
        font-size: 0.8rem;
        color: rgba(255,255,255,0.6);
    }
    
    .system-info p {
        margin: 5px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .system-info i {
        width: 15px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .sidebar {
            left: -250px;
            top: 0;
            height: 100vh;
            z-index: 1000;
        }
        
        .sidebar.show {
            left: 0;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
        }
        
        .header-left .menu-toggle {
            display: block;
        }
    }
</style>

<script>
    // Handle submenu toggle
    document.querySelectorAll('.nav-item.has-submenu').forEach(item => {
        const link = item.querySelector('.nav-link');
        link.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                item.classList.toggle('active');
            }
        });
    });
    
    // Auto-close other submenus when one opens
    document.querySelectorAll('.nav-item.has-submenu').forEach(item => {
        const link = item.querySelector('.nav-link');
        link.addEventListener('click', function(e) {
            if (window.innerWidth > 768) {
                document.querySelectorAll('.nav-item.has-submenu').forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                    }
                });
                item.classList.toggle('active');
            }
        });
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            const sidebar = document.querySelector('.sidebar');
            const menuToggle = document.getElementById('menuToggle');
            
            if (!sidebar.contains(e.target) && !menuToggle.contains(e.target) && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        }
    });
</script>