<nav class="admin-sidebar">
    <div class="sidebar-header">
        <h2>STMI Trust</h2>
        <p>Admin Panel</p>
    </div>
    
    <div class="admin-nav">
        <a href="dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        
        <a href="events.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i>
            <span>Events</span>
        </a>
        
        <a href="team.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'team.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Team Members</span>
        </a>
        
        <a href="donations.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'donations.php' ? 'active' : ''; ?>">
            <i class="fas fa-hand-holding-heart"></i>
            <span>Donations</span>
        </a>
        
        <a href="messages.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>">
            <i class="fas fa-envelope"></i>
            <span>Messages</span>
            <span class="nav-badge" id="unread-count">0</span>
        </a>
        
        <a href="media.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'media.php' ? 'active' : ''; ?>">
            <i class="fas fa-images"></i>
            <span>Media Library</span>
        </a>
        
      <a href="reports.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i>
            <span>Reports</span>
        </a>
        
        <?php if (hasPermission('admin')): ?>
        <div class="nav-section">
            <div class="nav-section-title">Administration</div>
            
            <a href="users.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-cog"></i>
                <span>Admin Users</span>
            </a>
            
            <a href="settings.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
            
            <a href="logs.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'logs.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i>
                <span>Activity Logs</span>
            </a>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="sidebar-footer">
        <a href="../index.php" target="_blank" class="btn btn-sm btn-secondary">
            <i class="fas fa-external-link-alt"></i> View Website
        </a>
    </div>
</nav>

<script>
// Fetch unread message count
fetch('api/get_unread_count.php')
    .then(response => response.json())
    .then(data => {
        if (data.count > 0) {
            document.getElementById('unread-count').textContent = data.count;
        } else {
            document.getElementById('unread-count').style.display = 'none';
        }
    });
</script>