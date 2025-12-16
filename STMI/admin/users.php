<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

// Check if user has admin permissions
requirePermission('admin');

$action = $_GET['action'] ?? 'list';
$role = $_GET['role'] ?? 'all';
$status = $_GET['status'] ?? 'all';

switch ($action) {
    case 'add':
        handleUserAdd();
        break;
    case 'edit':
        handleUserEdit();
        break;
    case 'view':
        handleUserView();
        break;
    case 'delete':
        handleUserDelete();
        break;
    case 'activate':
        handleUserActivation(true);
        break;
    case 'deactivate':
        handleUserActivation(false);
        break;
    case 'reset-password':
        handlePasswordReset();
        break;
    case 'profile':
        handleUserProfile();
        break;
    case 'permissions':
        handleUserPermissions();
        break;
    default:
        listUsers();
}

function listUsers() {
    global $pdo, $role, $status;
    
    $search = $_GET['search'] ?? '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 20;
    $offset = ($page - 1) * $per_page;
    
    // Build query
    $sql = "SELECT 
                u.*, 
                r.role_name,
                COUNT(DISTINCT a.id) as activity_count,
                MAX(a.created_at) as last_activity
            FROM admin_users u
            LEFT JOIN user_roles r ON u.role_id = r.id
            LEFT JOIN activity_logs a ON u.id = a.user_id
            WHERE 1=1";
    
    $count_sql = "SELECT COUNT(*) as total FROM admin_users u WHERE 1=1";
    $params = [];
    $count_params = [];
    
    if ($role !== 'all') {
        $sql .= " AND u.role_id = ?";
        $count_sql .= " AND u.role_id = ?";
        $params[] = $role;
        $count_params[] = $role;
    }
    
    if ($status !== 'all') {
        $sql .= " AND u.status = ?";
        $count_sql .= " AND u.status = ?";
        $params[] = $status;
        $count_params[] = $status;
    }
    
    if ($search) {
        $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)";
        $count_sql .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $count_params[] = $search_term;
        $count_params[] = $search_term;
        $count_params[] = $search_term;
    }
    
    // Get total count
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($count_params);
    $total_result = $stmt->fetch();
    $total_items = $total_result['total'];
    $total_pages = ceil($total_items / $per_page);
    
    // Get paginated results
    $sql .= " GROUP BY u.id ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
    
    // Get statistics
    $stats_sql = "
        SELECT 
            COUNT(*) as total_users,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active_count,
            COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive_count,
            COUNT(CASE WHEN status = 'suspended' THEN 1 END) as suspended_count,
            COUNT(CASE WHEN last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as recent_login_count,
            COUNT(CASE WHEN role_id = 1 THEN 1 END) as admin_count,
            COUNT(CASE WHEN role_id = 2 THEN 1 END) as editor_count,
            COUNT(CASE WHEN role_id = 3 THEN 1 END) as viewer_count
        FROM admin_users
    ";
    $stats_stmt = $pdo->query($stats_sql);
    $stats = $stats_stmt->fetch();
    
    // Get roles for filter
    $roles_stmt = $pdo->query("SELECT id, role_name FROM user_roles ORDER BY id");
    $roles = $roles_stmt->fetchAll();
    
    include 'includes/header.php';
    ?>
    <div class="admin-content">
        <div class="dashboard-header">
            <h1><i class="fas fa-users"></i> User Management</h1>
            <p>Manage system users and their permissions</p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #667eea;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['total_users']); ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #57cc99;">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['active_count']); ?></h3>
                    <p>Active Users</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #ff9d0b;">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['admin_count']); ?></h3>
                    <p>Administrators</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #764ba2;">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['recent_login_count']); ?></h3>
                    <p>Recent Logins (7 days)</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="action-buttons">
                <a href="users.php?action=add" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Add New User
                </a>
                <a href="users.php?status=active" class="btn btn-success">
                    <i class="fas fa-user-check"></i> Active Users
                </a>
                <a href="users.php?status=inactive" class="btn btn-warning">
                    <i class="fas fa-user-clock"></i> Inactive Users
                </a>
                <a href="users.php?status=suspended" class="btn btn-danger">
                    <i class="fas fa-user-slash"></i> Suspended Users
                </a>
                <a href="users.php?export=csv" class="btn btn-info">
                    <i class="fas fa-download"></i> Export Users
                </a>
            </div>
        </div>
        
        <!-- Filters and Search -->
        <div class="users-filters">
            <div class="filters-left">
                <div class="filter-group">
                    <label>Role:</label>
                    <select id="roleFilter" class="form-control">
                        <option value="all" <?php echo $role === 'all' ? 'selected' : ''; ?>>All Roles</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?php echo $r['id']; ?>" <?php echo $role == $r['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($r['role_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Status:</label>
                    <select id="statusFilter" class="form-control">
                        <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="suspended" <?php echo $status === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                    </select>
                </div>
            </div>
            
            <div class="filters-right">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search users..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button onclick="applyFilters()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Users Table -->
        <div class="users-container">
            <?php if (empty($users)): ?>
                <div class="empty-state">
                    <i class="fas fa-users fa-3x"></i>
                    <h4>No users found</h4>
                    <p>No users match your current filters.</p>
                    <?php if ($status !== 'all' || $role !== 'all' || $search): ?>
                        <a href="users.php" class="btn btn-primary">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    <?php endif; ?>
                    <a href="users.php?action=add" class="btn btn-success mt-2">
                        <i class="fas fa-user-plus"></i> Add First User
                    </a>
                </div>
            <?php else: ?>
                <div class="users-table-container">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>User</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Activity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr class="user-row status-<?php echo $user['status']; ?>" data-id="<?php echo $user['id']; ?>">
                                    <td>
                                        <div class="user-id">#<?php echo $user['id']; ?></div>
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <?php if ($user['profile_picture']): ?>
                                                    <img src="../<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                                         alt="<?php echo htmlspecialchars($user['full_name']); ?>">
                                                <?php else: ?>
                                                    <div class="avatar-placeholder">
                                                        <?php echo strtoupper(substr($user['full_name'], 0, 2)); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="user-details">
                                                <div class="user-name">
                                                    <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                                    <?php if ($user['id'] == $_SESSION['admin_id']): ?>
                                                        <span class="badge badge-primary">You</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="user-email">
                                                    <i class="fas fa-envelope"></i>
                                                    <?php echo htmlspecialchars($user['email']); ?>
                                                </div>
                                                <div class="user-username">
                                                    <i class="fas fa-user"></i>
                                                    <?php echo htmlspecialchars($user['username']); ?>
                                                </div>
                                                <?php if ($user['phone']): ?>
                                                    <div class="user-phone">
                                                        <i class="fas fa-phone"></i>
                                                        <?php echo htmlspecialchars($user['phone']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="user-role">
                                            <span class="role-badge role-<?php echo strtolower($user['role_name']); ?>">
                                                <?php echo htmlspecialchars($user['role_name']); ?>
                                            </span>
                                            <?php if ($user['is_super_admin']): ?>
                                                <div class="super-admin-badge" title="Super Administrator">
                                                    <i class="fas fa-crown"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="user-status">
                                            <span class="status-badge status-<?php echo $user['status']; ?>">
                                                <?php echo ucfirst($user['status']); ?>
                                            </span>
                                            <div class="status-details">
                                                <?php if ($user['last_login']): ?>
                                                    <small>Last login: <?php echo timeAgo($user['last_login']); ?></small>
                                                <?php else: ?>
                                                    <small>Never logged in</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="user-activity">
                                            <?php if ($user['last_activity']): ?>
                                                <div class="activity-time">
                                                    <?php echo timeAgo($user['last_activity']); ?>
                                                </div>
                                                <div class="activity-count">
                                                    <?php echo number_format($user['activity_count']); ?> activities
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">No activity</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="user-actions">
                                            <a href="users.php?action=view&id=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-info" title="View Profile">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="users.php?action=edit&id=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                                <?php if ($user['status'] === 'active'): ?>
                                                    <a href="users.php?action=deactivate&id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-sm btn-warning" title="Deactivate"
                                                       onclick="return confirm('Are you sure you want to deactivate this user?')">
                                                        <i class="fas fa-user-times"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="users.php?action=activate&id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-sm btn-success" title="Activate">
                                                        <i class="fas fa-user-check"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="users.php?action=reset-password&id=<?php echo $user['id']; ?>" 
                                                   class="btn btn-sm btn-secondary" title="Reset Password"
                                                   onclick="return confirm('Reset password for this user?')">
                                                    <i class="fas fa-key"></i>
                                                </a>
                                                <?php if ($user['status'] !== 'suspended'): ?>
                                                    <a href="users.php?action=suspend&id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-sm btn-danger" title="Suspend"
                                                       onclick="return confirm('Are you sure you want to suspend this user?')">
                                                        <i class="fas fa-ban"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="users.php?action=unsuspend&id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-sm btn-success" title="Unsuspend">
                                                        <i class="fas fa-check-circle"></i>
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <a href="users.php?action=profile" class="btn btn-sm btn-outline-primary" title="Your Profile">
                                                    <i class="fas fa-user"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <nav aria-label="Page navigation">
                        <ul class="pagination-list">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&role=<?php echo $role; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&role=<?php echo $role; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&role=<?php echo $role; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    
                    <div class="pagination-info">
                        Showing <?php echo min(($page - 1) * $per_page + 1, $total_items); ?> - 
                        <?php echo min($page * $per_page, $total_items); ?> of 
                        <?php echo number_format($total_items); ?> users
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- User Activity Chart -->
        <div class="chart-container">
            <div class="chart-card">
                <h3><i class="fas fa-chart-line"></i> User Activity (Last 30 Days)</h3>
                <div class="chart-wrapper">
                    <canvas id="userActivityChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    function applyFilters() {
        const role = document.getElementById('roleFilter').value;
        const status = document.getElementById('statusFilter').value;
        const search = document.getElementById('searchInput').value;
        
        let url = 'users.php?';
        const params = [];
        
        if (role !== 'all') params.push('role=' + encodeURIComponent(role));
        if (status !== 'all') params.push('status=' + encodeURIComponent(status));
        if (search) params.push('search=' + encodeURIComponent(search));
        
        window.location.href = url + params.join('&');
    }
    
    // Auto-apply filters on change
    document.getElementById('roleFilter').addEventListener('change', applyFilters);
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    
    // Search on Enter key
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyFilters();
        }
    });
    
    // User Activity Chart
    document.addEventListener('DOMContentLoaded', function() {
        // This would typically come from an AJAX request
        // For now, we'll use static data
        const ctx = document.getElementById('userActivityChart').getContext('2d');
        
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan 1', 'Jan 5', 'Jan 10', 'Jan 15', 'Jan 20', 'Jan 25', 'Jan 30'],
                datasets: [{
                    label: 'User Logins',
                    data: [12, 19, 8, 15, 22, 18, 25],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'User Registrations',
                    data: [2, 3, 1, 4, 2, 3, 1],
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Users'
                        },
                        ticks: {
                            stepSize: 5
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                }
            }
        });
        
        // View user on row click
        document.querySelectorAll('.user-row').forEach(row => {
            row.addEventListener('click', function(e) {
                // Don't trigger if clicking on links/buttons
                if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || 
                    e.target.closest('a') || e.target.closest('button') || 
                    e.target.closest('.user-actions')) {
                    return;
                }
                
                const userId = this.getAttribute('data-id');
                window.location.href = `users.php?action=view&id=${userId}`;
            });
        });
    });
    </script>
    
    <style>
    .users-filters {
        background: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .filters-left {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
        min-width: 200px;
    }
    
    .filter-group label {
        font-weight: 600;
        margin-bottom: 5px;
        color: #555;
        font-size: 14px;
    }
    
    .filters-right {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .search-box {
        display: flex;
        align-items: center;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 5px 15px;
        border: 1px solid #dee2e6;
    }
    
    .search-box input {
        border: none;
        background: transparent;
        padding: 8px;
        width: 250px;
        outline: none;
    }
    
    .search-box button {
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
        padding: 5px;
    }
    
    .quick-actions {
        margin-bottom: 20px;
    }
    
    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .users-container {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .users-table-container {
        overflow-x: auto;
    }
    
    .users-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .users-table th {
        background: #f8f9fa;
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #555;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }
    
    .users-table td {
        padding: 15px;
        border-bottom: 1px solid #dee2e6;
        vertical-align: top;
    }
    
    .user-row {
        transition: background-color 0.2s;
        cursor: pointer;
    }
    
    .user-row:hover {
        background-color: #f8f9fa;
    }
    
    .user-row.status-active {
        background-color: #f0fff4;
    }
    
    .user-row.status-active:hover {
        background-color: #e6ffed;
    }
    
    .user-row.status-inactive {
        background-color: #fff9e6;
    }
    
    .user-row.status-inactive:hover {
        background-color: #fff4d1;
    }
    
    .user-row.status-suspended {
        background-color: #ffeaea;
    }
    
    .user-row.status-suspended:hover {
        background-color: #ffd6d6;
    }
    
    .user-id {
        font-weight: 600;
        color: #6c757d;
        font-family: monospace;
    }
    
    .user-info {
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }
    
    .user-avatar {
        position: relative;
    }
    
    .user-avatar img {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .avatar-placeholder {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 18px;
        border: 3px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .user-details {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .user-name {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .user-name strong {
        font-size: 16px;
        color: #333;
    }
    
    .user-email, .user-username, .user-phone {
        font-size: 13px;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .user-email i, .user-username i, .user-phone i {
        font-size: 12px;
        width: 16px;
        text-align: center;
    }
    
    .user-role {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .role-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .role-badge.role-administrator {
        background-color: #dc3545;
        color: white;
    }
    
    .role-badge.role-editor {
        background-color: #28a745;
        color: white;
    }
    
    .role-badge.role-viewer {
        background-color: #6c757d;
        color: white;
    }
    
    .super-admin-badge {
        color: #ffc107;
        font-size: 14px;
    }
    
    .user-status {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        width: fit-content;
    }
    
    .status-badge.status-active {
        background-color: #28a745;
        color: white;
    }
    
    .status-badge.status-inactive {
        background-color: #ffc107;
        color: #212529;
    }
    
    .status-badge.status-suspended {
        background-color: #dc3545;
        color: white;
    }
    
    .status-details small {
        color: #6c757d;
        font-size: 11px;
        display: block;
    }
    
    .user-activity {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    
    .activity-time {
        font-weight: 500;
        color: #333;
    }
    
    .activity-count {
        font-size: 12px;
        color: #6c757d;
    }
    
    .user-actions {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }
    
    .user-actions .btn {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    
    .empty-state i {
        margin-bottom: 20px;
        color: #dee2e6;
    }
    
    .empty-state h4 {
        margin-bottom: 10px;
        color: #495057;
    }
    
    .empty-state p {
        margin-bottom: 20px;
    }
    
    .chart-container {
        margin-top: 30px;
    }
    
    .chart-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .chart-card h3 {
        margin: 0 0 20px 0;
        color: #333;
        font-size: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .chart-wrapper {
        height: 300px;
        position: relative;
    }
    </style>
    <?php
    include 'includes/footer.php';
}

function handleUserAdd() {
    global $pdo;
    
    $error = '';
    $success = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $phone = trim($_POST['phone']);
        $role_id = $_POST['role_id'];
        $status = $_POST['status'];
        $is_super_admin = isset($_POST['is_super_admin']) ? 1 : 0;
        
        $errors = [];
        
        // Validation
        if (empty($full_name)) $errors[] = 'Full name is required';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (empty($username)) $errors[] = 'Username is required';
        if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters';
        if ($password !== $confirm_password) $errors[] = 'Passwords do not match';
        
        // Check if email exists
        $check_stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = ?");
        $check_stmt->execute([$email]);
        if ($check_stmt->fetch()) $errors[] = 'Email already exists';
        
        // Check if username exists
        $check_stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
        $check_stmt->execute([$username]);
        if ($check_stmt->fetch()) $errors[] = 'Username already exists';
        
        if (empty($errors)) {
            try {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Generate verification token
                $verification_token = bin2hex(random_bytes(32));
                
                $stmt = $pdo->prepare("
                    INSERT INTO admin_users 
                    (full_name, email, username, password, phone, role_id, status, is_super_admin, 
                     verification_token, created_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                
                $stmt->execute([
                    $full_name, $email, $username, $hashed_password, $phone, $role_id, $status, $is_super_admin,
                    $verification_token, $_SESSION['admin_id']
                ]);
                
                $user_id = $pdo->lastInsertId();
                
                // Log activity
                logActivity('create', 'admin_users', $user_id, null, [
                    'full_name' => $full_name,
                    'email' => $email,
                    'role_id' => $role_id
                ]);
                
                // Send welcome email
                sendWelcomeEmail($email, $full_name, $username, $password);
                
                $success = 'User created successfully.';
                $_SESSION['message'] = $success;
                header('Location: users.php');
                exit();
                
            } catch (Exception $e) {
                $error = 'Error creating user: ' . $e->getMessage();
            }
        } else {
            $error = implode('<br>', $errors);
        }
    }
    
    // Get roles for dropdown
    $roles_stmt = $pdo->query("SELECT id, role_name FROM user_roles ORDER BY id");
    $roles = $roles_stmt->fetchAll();
    
    include 'includes/header.php';
    ?>
    <div class="admin-content">
        <div class="form-card">
            <h2>
                <i class="fas fa-user-plus"></i> Add New User
            </h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                        <small class="form-text text-muted">Used for login</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <div class="password-input-group">
                            <input type="password" name="password" id="password" class="form-control" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="form-text text-muted">Minimum 8 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Confirm Password *</label>
                        <div class="password-input-group">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Role *</label>
                        <select name="role_id" class="form-control" required>
                            <option value="">Select Role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['id']; ?>" 
                                    <?php echo ($_POST['role_id'] ?? '') == $role['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($role['role_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status *</label>
                        <select name="status" class="form-control" required>
                            <option value="active" <?php echo ($_POST['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($_POST['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="isSuperAdmin" name="is_super_admin" value="1"
                               <?php echo isset($_POST['is_super_admin']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="isSuperAdmin">
                            Grant Super Administrator privileges
                        </label>
                        <small class="form-text text-muted">Super admins have full system access</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="sendWelcomeEmail" name="send_welcome_email" value="1" checked>
                        <label class="form-check-label" for="sendWelcomeEmail">
                            Send welcome email with login credentials
                        </label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create User
                    </button>
                    <a href="users.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const toggleBtn = field.nextElementSibling;
        const icon = toggleBtn.querySelector('i');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    
    // Password strength checker
    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        const strengthIndicator = document.getElementById('password-strength');
        
        if (!strengthIndicator) {
            const div = document.createElement('div');
            div.id = 'password-strength';
            div.className = 'password-strength mt-2';
            this.parentNode.appendChild(div);
        }
        
        const strength = calculatePasswordStrength(password);
        updateStrengthIndicator(strength);
    });
    
    function calculatePasswordStrength(password) {
        let score = 0;
        
        if (password.length >= 8) score++;
        if (password.length >= 12) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        
        return Math.min(score, 5);
    }
    
    function updateStrengthIndicator(strength) {
        const indicator = document.getElementById('password-strength');
        const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
        const colors = ['#dc3545', '#ff6b6b', '#ffc107', '#28a745', '#20c997', '#198754'];
        
        indicator.innerHTML = `
            <div class="strength-bar">
                ${[1,2,3,4,5].map(i => `
                    <div class="strength-segment ${i <= strength ? 'active' : ''}" 
                         style="background: ${i <= strength ? colors[strength] : '#dee2e6'}"></div>
                `).join('')}
            </div>
            <div class="strength-text">
                <strong>Password Strength:</strong> ${labels[strength]}
            </div>
        `;
    }
    </script>
    
    <style>
    .password-input-group {
        position: relative;
    }
    
    .password-input-group input {
        padding-right: 40px;
    }
    
    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
        padding: 5px;
    }
    
    .password-strength {
        font-size: 14px;
    }
    
    .strength-bar {
        display: flex;
        gap: 2px;
        height: 6px;
        margin-bottom: 5px;
    }
    
    .strength-segment {
        flex: 1;
        background: #dee2e6;
        border-radius: 3px;
        transition: background 0.3s;
    }
    
    .strength-segment.active {
        background: #28a745;
    }
    
    .strength-text {
        font-size: 12px;
        color: #6c757d;
    }
    </style>
    <?php
    include 'includes/footer.php';
}

function handleUserEdit() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    
    // Fetch user
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = 'User not found.';
        header('Location: users.php');
        exit();
    }
    
    // Prevent editing of your own account through this page
    if ($user['id'] == $_SESSION['admin_id']) {
        header('Location: users.php?action=profile');
        exit();
    }
    
    $error = '';
    $success = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        $phone = trim($_POST['phone']);
        $role_id = $_POST['role_id'];
        $status = $_POST['status'];
        $is_super_admin = isset($_POST['is_super_admin']) ? 1 : 0;
        
        $errors = [];
        
        // Validation
        if (empty($full_name)) $errors[] = 'Full name is required';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (empty($username)) $errors[] = 'Username is required';
        
        // Check if email exists (excluding current user)
        $check_stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = ? AND id != ?");
        $check_stmt->execute([$email, $id]);
        if ($check_stmt->fetch()) $errors[] = 'Email already exists';
        
        // Check if username exists (excluding current user)
        $check_stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ? AND id != ?");
        $check_stmt->execute([$username, $id]);
        if ($check_stmt->fetch()) $errors[] = 'Username already exists';
        
        if (empty($errors)) {
            try {
                // Store old values for logging
                $oldValues = [
                    'full_name' => $user['full_name'],
                    'email' => $user['email'],
                    'username' => $user['username'],
                    'role_id' => $user['role_id'],
                    'status' => $user['status'],
                    'is_super_admin' => $user['is_super_admin']
                ];
                
                $stmt = $pdo->prepare("
                    UPDATE admin_users SET
                    full_name = ?, email = ?, username = ?, phone = ?, 
                    role_id = ?, status = ?, is_super_admin = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $full_name, $email, $username, $phone, $role_id, $status, $is_super_admin, $id
                ]);
                
                // Log activity
                logActivity('update', 'admin_users', $id, $oldValues, [
                    'full_name' => $full_name,
                    'email' => $email,
                    'role_id' => $role_id,
                    'status' => $status
                ]);
                
                $success = 'User updated successfully.';
                $_SESSION['message'] = $success;
                header('Location: users.php');
                exit();
                
            } catch (Exception $e) {
                $error = 'Error updating user: ' . $e->getMessage();
            }
        } else {
            $error = implode('<br>', $errors);
        }
    }
    
    // Get roles for dropdown
    $roles_stmt = $pdo->query("SELECT id, role_name FROM user_roles ORDER BY id");
    $roles = $roles_stmt->fetchAll();
    
    include 'includes/header.php';
    ?>
    <div class="admin-content">
        <div class="form-card">
            <h2>
                <i class="fas fa-user-edit"></i> Edit User
            </h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="user-info-card">
                <div class="user-avatar-large">
                    <?php if ($user['profile_picture']): ?>
                        <img src="../<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                             alt="<?php echo htmlspecialchars($user['full_name']); ?>">
                    <?php else: ?>
                        <div class="avatar-placeholder-large">
                            <?php echo strtoupper(substr($user['full_name'], 0, 2)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="user-details-summary">
                    <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                    <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><i class="fas fa-calendar"></i> Joined: <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                </div>
            </div>
            
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" class="form-control" 
                               value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Role *</label>
                        <select name="role_id" class="form-control" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['id']; ?>" 
                                    <?php echo $user['role_id'] == $role['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($role['role_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status *</label>
                        <select name="status" class="form-control" required>
                            <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="suspended" <?php echo $user['status'] === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="isSuperAdmin" name="is_super_admin" value="1"
                               <?php echo $user['is_super_admin'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="isSuperAdmin">
                            Super Administrator
                        </label>
                        <small class="form-text text-muted">Full system access</small>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update User
                    </button>
                    <a href="users.php" class="btn btn-secondary">Cancel</a>
                    <a href="users.php?action=reset-password&id=<?php echo $id; ?>" class="btn btn-warning">
                        <i class="fas fa-key"></i> Reset Password
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <style>
    .user-info-card {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 30px;
    }
    
    .user-avatar-large img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .avatar-placeholder-large {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 24px;
        border: 4px solid white;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .user-details-summary h4 {
        margin: 0 0 10px 0;
        color: #333;
    }
    
    .user-details-summary p {
        margin: 5px 0;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .user-details-summary i {
        width: 16px;
    }
    </style>
    <?php
    include 'includes/footer.php';
}

function handleUserView() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    
    // Fetch user with role information
    $stmt = $pdo->prepare("
        SELECT u.*, r.role_name, r.permissions
        FROM admin_users u
        LEFT JOIN user_roles r ON u.role_id = r.id
        WHERE u.id = ?
    ");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = 'User not found.';
        header('Location: users.php');
        exit();
    }
    
    // Get user activity logs
    $activity_stmt = $pdo->prepare("
        SELECT * FROM activity_logs 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $activity_stmt->execute([$id]);
    $activities = $activity_stmt->fetchAll();
    
    // Get user statistics
    $stats_stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_activities,
            COUNT(CASE WHEN action_type = 'login' THEN 1 END) as login_count,
            MAX(created_at) as last_activity,
            MIN(created_at) as first_activity
        FROM activity_logs 
        WHERE user_id = ?
    ");
    $stats_stmt->execute([$id]);
    $stats = $stats_stmt->fetch();
    
    include 'includes/header.php';
    ?>
    <div class="admin-content">
        <div class="user-profile-view">
            <div class="profile-header">
                <a href="users.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
                <div class="header-actions">
                    <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                        <a href="users.php?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <?php if ($user['status'] === 'active'): ?>
                            <a href="users.php?action=deactivate&id=<?php echo $user['id']; ?>" 
                               class="btn btn-warning"
                               onclick="return confirm('Deactivate this user?')">
                                <i class="fas fa-user-times"></i> Deactivate
                            </a>
                        <?php else: ?>
                            <a href="users.php?action=activate&id=<?php echo $user['id']; ?>" 
                               class="btn btn-success">
                                <i class="fas fa-user-check"></i> Activate
                            </a>
                        <?php endif; ?>
                        <a href="users.php?action=reset-password&id=<?php echo $user['id']; ?>" 
                           class="btn btn-secondary"
                           onclick="return confirm('Reset password for this user?')">
                            <i class="fas fa-key"></i> Reset Password
                        </a>
                    <?php else: ?>
                        <a href="users.php?action=profile" class="btn btn-primary">
                            <i class="fas fa-user"></i> Your Profile
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="profile-content">
                <!-- User Info Card -->
                <div class="profile-card">
                    <div class="profile-header-card">
                        <div class="profile-avatar-section">
                            <?php if ($user['profile_picture']): ?>
                                <img src="../<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                     alt="<?php echo htmlspecialchars($user['full_name']); ?>" 
                                     class="profile-avatar">
                            <?php else: ?>
                                <div class="profile-avatar-placeholder">
                                    <?php echo strtoupper(substr($user['full_name'], 0, 2)); ?>
                                </div>
                            <?php endif; ?>
                            <div class="profile-status">
                                <span class="status-badge status-<?php echo $user['status']; ?>">
                                    <?php echo strtoupper($user['status']); ?>
                                </span>
                                <?php if ($user['is_super_admin']): ?>
                                    <span class="badge badge-warning">
                                        <i class="fas fa-crown"></i> Super Admin
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="profile-info">
                            <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
                            <div class="profile-role">
                                <span class="role-badge role-<?php echo strtolower($user['role_name']); ?>">
                                    <?php echo htmlspecialchars($user['role_name']); ?>
                                </span>
                            </div>
                            <div class="profile-meta">
                                <div class="meta-item">
                                    <i class="fas fa-user"></i>
                                    <span><?php echo htmlspecialchars($user['username']); ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-envelope"></i>
                                    <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>">
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </a>
                                </div>
                                <?php if ($user['phone']): ?>
                                <div class="meta-item">
                                    <i class="fas fa-phone"></i>
                                    <span><?php echo htmlspecialchars($user['phone']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $stats['total_activities'] ?? 0; ?></div>
                            <div class="stat-label">Activities</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $stats['login_count'] ?? 0; ?></div>
                            <div class="stat-label">Logins</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">
                                <?php echo $user['last_login'] ? timeAgo($user['last_login']) : 'Never'; ?>
                            </div>
                            <div class="stat-label">Last Login</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">
                                <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                            </div>
                            <div class="stat-label">Joined</div>
                        </div>
                    </div>
                </div>
                
                <!-- User Details Grid -->
                <div class="details-grid">
                    <div class="detail-section">
                        <h3><i class="fas fa-info-circle"></i> Account Information</h3>
                        <div class="detail-list">
                            <div class="detail-item">
                                <label>User ID:</label>
                                <span>#<?php echo $user['id']; ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Username:</label>
                                <span><?php echo htmlspecialchars($user['username']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Email:</label>
                                <span><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Status:</label>
                                <span class="status-badge status-<?php echo $user['status']; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <label>Two-Factor Auth:</label>
                                <span><?php echo $user['two_factor_enabled'] ? 'Enabled' : 'Disabled'; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h3><i class="fas fa-history"></i> Account History</h3>
                        <div class="detail-list">
                            <div class="detail-item">
                                <label>Created:</label>
                                <span><?php echo date('F j, Y g:i A', strtotime($user['created_at'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Last Updated:</label>
                                <span><?php echo date('F j, Y g:i A', strtotime($user['updated_at'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Last Login:</label>
                                <span><?php echo $user['last_login'] ? date('F j, Y g:i A', strtotime($user['last_login'])) : 'Never'; ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Last Password Change:</label>
                                <span><?php echo $user['last_password_change'] ? date('F j, Y g:i A', strtotime($user['last_password_change'])) : 'Never'; ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Created By:</label>
                                <span>
                                    <?php 
                                    if ($user['created_by']) {
                                        $creator_stmt = $pdo->prepare("SELECT full_name FROM admin_users WHERE id = ?");
                                        $creator_stmt->execute([$user['created_by']]);
                                        $creator = $creator_stmt->fetch();
                                        echo $creator ? htmlspecialchars($creator['full_name']) : 'System';
                                    } else {
                                        echo 'System';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($user['role_name']): ?>
                    <div class="detail-section">
                        <h3><i class="fas fa-user-shield"></i> Role & Permissions</h3>
                        <div class="detail-list">
                            <div class="detail-item">
                                <label>Role:</label>
                                <span class="role-badge role-<?php echo strtolower($user['role_name']); ?>">
                                    <?php echo htmlspecialchars($user['role_name']); ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <label>Super Admin:</label>
                                <span><?php echo $user['is_super_admin'] ? 'Yes' : 'No'; ?></span>
                            </div>
                            <?php if ($user['permissions']): 
                                $permissions = json_decode($user['permissions'], true);
                                if (is_array($permissions) && !empty($permissions)):
                            ?>
                                <div class="detail-item">
                                    <label>Permissions:</label>
                                    <div class="permissions-list">
                                        <?php foreach ($permissions as $permission): ?>
                                            <span class="permission-badge"><?php echo htmlspecialchars($permission); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($user['phone'] || $user['address']): ?>
                    <div class="detail-section">
                        <h3><i class="fas fa-address-card"></i> Contact Information</h3>
                        <div class="detail-list">
                            <?php if ($user['phone']): ?>
                            <div class="detail-item">
                                <label>Phone:</label>
                                <span><?php echo htmlspecialchars($user['phone']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($user['address']): ?>
                            <div class="detail-item">
                                <label>Address:</label>
                                <span><?php echo nl2br(htmlspecialchars($user['address'])); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($user['department']): ?>
                            <div class="detail-item">
                                <label>Department:</label>
                                <span><?php echo htmlspecialchars($user['department']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($user['position']): ?>
                            <div class="detail-item">
                                <label>Position:</label>
                                <span><?php echo htmlspecialchars($user['position']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Recent Activity -->
                <div class="activity-section">
                    <h3><i class="fas fa-history"></i> Recent Activity</h3>
                    
                    <?php if (empty($activities)): ?>
                        <div class="no-activity">
                            <i class="fas fa-clock fa-2x"></i>
                            <p>No activity recorded yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="activity-timeline">
                            <?php foreach ($activities as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <?php 
                                        $icon = 'fas fa-info-circle';
                                        $color = '#6c757d';
                                        
                                        switch ($activity['action_type']) {
                                            case 'login': $icon = 'fas fa-sign-in-alt'; $color = '#28a745'; break;
                                            case 'logout': $icon = 'fas fa-sign-out-alt'; $color = '#dc3545'; break;
                                            case 'create': $icon = 'fas fa-plus-circle'; $color = '#20c997'; break;
                                            case 'update': $icon = 'fas fa-edit'; $color = '#ffc107'; break;
                                            case 'delete': $icon = 'fas fa-trash'; $color = '#dc3545'; break;
                                        }
                                        ?>
                                        <i class="<?php echo $icon; ?>" style="color: <?php echo $color; ?>"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-header">
                                            <strong><?php echo htmlspecialchars($activity['action_type']); ?></strong>
                                            <span class="activity-time"><?php echo timeAgo($activity['created_at']); ?></span>
                                        </div>
                                        <div class="activity-description">
                                            <?php echo htmlspecialchars($activity['description']); ?>
                                        </div>
                                        <?php if ($activity['ip_address']): ?>
                                        <div class="activity-meta">
                                            <small>IP: <?php echo htmlspecialchars($activity['ip_address']); ?></small>
                                            <?php if ($activity['user_agent']): ?>
                                                <small>• Browser: <?php echo htmlspecialchars(substr($activity['user_agent'], 0, 50)); ?>...</small>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="logs.php?user_id=<?php echo $id; ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-list"></i> View All Activities
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    .user-profile-view {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .profile-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #dee2e6;
    }
    
    .header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .profile-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        color: white;
        padding: 30px;
        margin-bottom: 30px;
    }
    
    .profile-header-card {
        display: flex;
        align-items: center;
        gap: 30px;
        margin-bottom: 30px;
    }
    
    .profile-avatar-section {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }
    
    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 6px solid rgba(255,255,255,0.3);
        box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    }
    
    .profile-avatar-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,0.9);
        color: #764ba2;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        font-weight: 700;
        border: 6px solid rgba(255,255,255,0.3);
        box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    }
    
    .profile-status {
        display: flex;
        gap: 8px;
    }
    
    .profile-info {
        flex: 1;
    }
    
    .profile-info h1 {
        margin: 0 0 10px 0;
        font-size: 32px;
        font-weight: 700;
    }
    
    .profile-role {
        margin-bottom: 15px;
    }
    
    .profile-meta {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 15px;
    }
    
    .meta-item i {
        width: 20px;
        text-align: center;
    }
    
    .meta-item a {
        color: white;
        text-decoration: none;
    }
    
    .meta-item a:hover {
        text-decoration: underline;
    }
    
    .profile-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 20px;
        padding-top: 20px;
        border-top: 1px solid rgba(255,255,255,0.3);
    }
    
    .stat-item {
        text-align: center;
    }
    
    .stat-number {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 14px;
        opacity: 0.9;
    }
    
    .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .detail-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
    }
    
    .detail-section h3 {
        margin: 0 0 15px 0;
        color: #333;
        font-size: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .detail-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .detail-item label {
        font-weight: 600;
        color: #666;
        font-size: 13px;
    }
    
    .detail-item span {
        color: #333;
        word-break: break-word;
    }
    
    .permissions-list {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 5px;
    }
    
    .permission-badge {
        display: inline-block;
        padding: 3px 8px;
        background: #e9ecef;
        color: #495057;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
    }
    
    .activity-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
    }
    
    .no-activity {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }
    
    .no-activity i {
        margin-bottom: 15px;
    }
    
    .activity-timeline {
        position: relative;
        padding-left: 40px;
    }
    
    .activity-timeline::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }
    
    .activity-item {
        position: relative;
        margin-bottom: 20px;
        display: flex;
        gap: 15px;
    }
    
    .activity-icon {
        position: absolute;
        left: -40px;
        width: 40px;
        height: 40px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #dee2e6;
        z-index: 1;
    }
    
    .activity-content {
        flex: 1;
        background: white;
        border-radius: 8px;
        padding: 15px;
        border: 1px solid #dee2e6;
    }
    
    .activity-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .activity-time {
        font-size: 12px;
        color: #6c757d;
    }
    
    .activity-description {
        color: #333;
        line-height: 1.5;
        margin-bottom: 10px;
    }
    
    .activity-meta {
        display: flex;
        gap: 10px;
        font-size: 11px;
        color: #6c757d;
    }
    </style>
    <?php
    include 'includes/footer.php';
}

function handleUserDelete() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    
    // Prevent deleting yourself
    if ($id == $_SESSION['admin_id']) {
        $_SESSION['error'] = 'You cannot delete your own account.';
        header('Location: users.php');
        exit();
    }
    
    try {
        // Get user details before deletion
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $_SESSION['error'] = 'User not found.';
            header('Location: users.php');
            exit();
        }
        
        // Log activity before deletion
        logActivity('delete', 'admin_users', $id, [
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'username' => $user['username']
        ], null);
        
        // Delete user
        $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['message'] = 'User deleted successfully.';
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error deleting user: ' . $e->getMessage();
    }
    
    header('Location: users.php');
    exit();
}

function handleUserActivation($activate = true) {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    $action = $_GET['action'];
    
    // Prevent deactivating yourself
    if ($id == $_SESSION['admin_id'] && !$activate) {
        $_SESSION['error'] = 'You cannot deactivate your own account.';
        header('Location: users.php');
        exit();
    }
    
    $status = $activate ? 'active' : 'inactive';
    $action_word = $activate ? 'activated' : 'deactivated';
    
    $stmt = $pdo->prepare("UPDATE admin_users SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    
    // Log activity
    $log_action = $activate ? 'activate' : 'deactivate';
    logActivity($log_action, 'admin_users', $id, ['status' => $activate ? 'inactive' : 'active'], ['status' => $status]);
    
    $_SESSION['message'] = "User $action_word successfully.";
    header('Location: users.php');
    exit();
}

function handlePasswordReset() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    
    // Generate new random password
    $new_password = bin2hex(random_bytes(8)); // 16 characters
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE admin_users SET password = ?, last_password_change = NOW() WHERE id = ?");
    $stmt->execute([$hashed_password, $id]);
    
    // Get user email
    $user_stmt = $pdo->prepare("SELECT email, full_name FROM admin_users WHERE id = ?");
    $user_stmt->execute([$id]);
    $user = $user_stmt->fetch();
    
    // Send password reset email
    if ($user) {
        sendPasswordResetEmail($user['email'], $user['full_name'], $new_password);
    }
    
    // Log activity
    logActivity('password_reset', 'admin_users', $id, null, ['reset_by' => $_SESSION['admin_name']]);
    
    $_SESSION['message'] = 'Password reset successfully. New password has been sent to user\'s email.';
    header('Location: users.php');
    exit();
}

function handleUserProfile() {
    global $pdo;
    
    $id = $_SESSION['admin_id'];
    
    // Fetch user
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = 'User not found.';
        header('Location: users.php');
        exit();
    }
    
    $error = '';
    $success = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            $full_name = trim($_POST['full_name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            
            $errors = [];
            
            if (empty($full_name)) $errors[] = 'Full name is required';
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
            
            // Check if email exists (excluding current user)
            $check_stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = ? AND id != ?");
            $check_stmt->execute([$email, $id]);
            if ($check_stmt->fetch()) $errors[] = 'Email already exists';
            
            if (empty($errors)) {
                try {
                    $oldValues = [
                        'full_name' => $user['full_name'],
                        'email' => $user['email'],
                        'phone' => $user['phone']
                    ];
                    
                    $stmt = $pdo->prepare("
                        UPDATE admin_users SET
                        full_name = ?, email = ?, phone = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    
                    $stmt->execute([$full_name, $email, $phone, $id]);
                    
                    // Update session
                    $_SESSION['admin_name'] = $full_name;
                    $_SESSION['admin_email'] = $email;
                    
                    logActivity('update_profile', 'admin_users', $id, $oldValues, [
                        'full_name' => $full_name,
                        'email' => $email
                    ]);
                    
                    $success = 'Profile updated successfully.';
                    $_SESSION['message'] = $success;
                    header('Location: users.php?action=profile');
                    exit();
                    
                } catch (Exception $e) {
                    $error = 'Error updating profile: ' . $e->getMessage();
                }
            } else {
                $error = implode('<br>', $errors);
            }
        }
        elseif ($action === 'change_password') {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            $errors = [];
            
            // Verify current password
            if (!password_verify($current_password, $user['password'])) {
                $errors[] = 'Current password is incorrect';
            }
            
            if (strlen($new_password) < 8) {
                $errors[] = 'New password must be at least 8 characters';
            }
            
            if ($new_password !== $confirm_password) {
                $errors[] = 'New passwords do not match';
            }
            
            if (empty($errors)) {
                try {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    $stmt = $pdo->prepare("
                        UPDATE admin_users SET 
                        password = ?, last_password_change = NOW(), updated_at = NOW()
                        WHERE id = ?
                    ");
                    
                    $stmt->execute([$hashed_password, $id]);
                    
                    logActivity('change_password', 'admin_users', $id, null, ['changed_by' => 'self']);
                    
                    $success = 'Password changed successfully.';
                    $_SESSION['message'] = $success;
                    header('Location: users.php?action=profile');
                    exit();
                    
                } catch (Exception $e) {
                    $error = 'Error changing password: ' . $e->getMessage();
                }
            } else {
                $error = implode('<br>', $errors);
            }
        }
        elseif ($action === 'upload_avatar') {
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
                $uploadResult = uploadAvatar($_FILES['avatar'], $id);
                
                if ($uploadResult['success']) {
                    // Delete old avatar if exists
                    if ($user['profile_picture']) {
                        unlink('../' . $user['profile_picture']);
                    }
                    
                    $stmt = $pdo->prepare("UPDATE admin_users SET profile_picture = ? WHERE id = ?");
                    $stmt->execute([$uploadResult['path'], $id]);
                    
                    $_SESSION['admin_avatar'] = $uploadResult['path'];
                    
                    logActivity('upload_avatar', 'admin_users', $id, null, ['avatar_path' => $uploadResult['path']]);
                    
                    $success = 'Profile picture updated successfully.';
                    $_SESSION['message'] = $success;
                    header('Location: users.php?action=profile');
                    exit();
                } else {
                    $error = $uploadResult['error'];
                }
            } else {
                $error = 'Please select an image to upload.';
            }
        }
    }
    
    include 'includes/header.php';
    ?>
    <div class="admin-content">
        <div class="user-profile-view">
            <div class="profile-header">
                <h1><i class="fas fa-user"></i> My Profile</h1>
                <a href="users.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-tabs">
                <div class="tab-buttons">
                    <button class="tab-btn active" data-tab="profile">Profile Information</button>
                    <button class="tab-btn" data-tab="password">Change Password</button>
                    <button class="tab-btn" data-tab="avatar">Profile Picture</button>
                    <button class="tab-btn" data-tab="security">Security</button>
                </div>
                
                <!-- Profile Information Tab -->
                <div class="tab-content active" id="profile-tab">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="full_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                <small class="form-text text-muted">Username cannot be changed</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Role</label>
                            <?php 
                            $role_stmt = $pdo->prepare("SELECT role_name FROM user_roles WHERE id = ?");
                            $role_stmt->execute([$user['role_id']]);
                            $role = $role_stmt->fetch();
                            ?>
                            <input type="text" class="form-control" 
                                   value="<?php echo htmlspecialchars($role['role_name'] ?? 'N/A'); ?>" disabled>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Change Password Tab -->
                <div class="tab-content" id="password-tab">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label class="form-label">Current Password *</label>
                            <div class="password-input-group">
                                <input type="password" name="current_password" class="form-control" required>
                                <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">New Password *</label>
                            <div class="password-input-group">
                                <input type="password" name="new_password" class="form-control" required>
                                <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted">Minimum 8 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Confirm New Password *</label>
                            <div class="password-input-group">
                                <input type="password" name="confirm_password" class="form-control" required>
                                <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Profile Picture Tab -->
                <div class="tab-content" id="avatar-tab">
                    <div class="avatar-upload-section">
                        <div class="current-avatar">
                            <h4>Current Profile Picture</h4>
                            <?php if ($user['profile_picture']): ?>
                                <img src="../<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                     alt="Current Avatar" class="profile-avatar-current">
                            <?php else: ?>
                                <div class="avatar-placeholder-current">
                                    <?php echo strtoupper(substr($user['full_name'], 0, 2)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="avatar-upload-form">
                            <h4>Upload New Picture</h4>
                            <form method="POST" action="" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="upload_avatar">
                                
                                <div class="form-group">
                                    <div class="avatar-upload-container">
                                        <input type="file" name="avatar" id="avatarUpload" 
                                               accept="image/*" class="form-control-file">
                                        <label for="avatarUpload" class="avatar-upload-label">
                                            <i class="fas fa-cloud-upload-alt fa-3x"></i>
                                            <span>Choose an image</span>
                                            <small>Max size: 2MB • JPG, PNG, GIF</small>
                                        </label>
                                        <div id="avatarPreview" class="avatar-preview"></div>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload"></i> Upload Picture
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Security Tab -->
                <div class="tab-content" id="security-tab">
                    <div class="security-settings">
                        <h4>Login History</h4>
                        <div class="login-history">
                            <?php 
                            $login_stmt = $pdo->prepare("
                                SELECT * FROM activity_logs 
                                WHERE user_id = ? AND action_type = 'login'
                                ORDER BY created_at DESC 
                                LIMIT 5
                            ");
                            $login_stmt->execute([$id]);
                            $logins = $login_stmt->fetchAll();
                            ?>
                            
                            <?php if (empty($logins)): ?>
                                <p class="text-muted">No login history available.</p>
                            <?php else: ?>
                                <div class="login-list">
                                    <?php foreach ($logins as $login): ?>
                                        <div class="login-item">
                                            <div class="login-time">
                                                <?php echo date('F j, Y g:i A', strtotime($login['created_at'])); ?>
                                            </div>
                                            <div class="login-details">
                                                <small>IP: <?php echo htmlspecialchars($login['ip_address']); ?></small>
                                                <?php if ($login['user_agent']): ?>
                                                    <small>• <?php echo htmlspecialchars(substr($login['user_agent'], 0, 50)); ?>...</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <h4 class="mt-4">Account Security</h4>
                        <div class="security-options">
                            <div class="security-item">
                                <div class="security-info">
                                    <h5>Two-Factor Authentication</h5>
                                    <p>Add an extra layer of security to your account</p>
                                </div>
                                <div class="security-action">
                                    <?php if ($user['two_factor_enabled']): ?>
                                        <span class="badge badge-success">Enabled</span>
                                        <button class="btn btn-sm btn-outline-danger">Disable</button>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Disabled</span>
                                        <button class="btn btn-sm btn-outline-success">Enable</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="security-item">
                                <div class="security-info">
                                    <h5>Session Management</h5>
                                    <p>View and manage your active sessions</p>
                                </div>
                                <div class="security-action">
                                    <button class="btn btn-sm btn-outline-primary">Manage Sessions</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Tab functionality
    document.querySelectorAll('.tab-btn').forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            document.getElementById(`${tabId}-tab`).classList.add('active');
        });
    });
    
    // Password toggle
    function togglePassword(button) {
        const input = button.parentNode.querySelector('input');
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    
    // Avatar preview
    document.getElementById('avatarUpload').addEventListener('change', function(e) {
        const file = this.files[0];
        const preview = document.getElementById('avatarPreview');
        
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="btn-remove-preview" onclick="removeAvatarPreview()">
                        <i class="fas fa-times"></i>
                    </button>
                `;
            };
            
            reader.readAsDataURL(file);
        }
    });
    
    function removeAvatarPreview() {
        document.getElementById('avatarUpload').value = '';
        document.getElementById('avatarPreview').innerHTML = '';
    }
    </script>
    
    <style>
    .profile-tabs {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .tab-buttons {
        display: flex;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    .tab-btn {
        padding: 15px 25px;
        background: none;
        border: none;
        border-right: 1px solid #dee2e6;
        font-weight: 500;
        color: #6c757d;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .tab-btn:last-child {
        border-right: none;
    }
    
    .tab-btn:hover {
        background: #e9ecef;
        color: #495057;
    }
    
    .tab-btn.active {
        background: white;
        color: #0e0c5e;
        border-bottom: 3px solid #0e0c5e;
    }
    
    .tab-content {
        display: none;
        padding: 30px;
    }
    
    .tab-content.active {
        display: block;
    }
    
    .avatar-upload-section {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 40px;
    }
    
    .current-avatar, .avatar-upload-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .current-avatar h4, .avatar-upload-form h4 {
        margin: 0 0 15px 0;
        color: #333;
    }
    
    .profile-avatar-current {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid #f8f9fa;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .avatar-placeholder-current {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        font-weight: 700;
        border: 5px solid #f8f9fa;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .avatar-upload-container {
        position: relative;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 40px;
        text-align: center;
        transition: border-color 0.3s;
    }
    
    .avatar-upload-container:hover {
        border-color: #0e0c5e;
    }
    
    .avatar-upload-container input[type="file"] {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        opacity: 0;
        cursor: pointer;
    }
    
    .avatar-upload-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        color: #6c757d;
        cursor: pointer;
    }
    
    .avatar-upload-label i {
        color: #0e0c5e;
    }
    
    .avatar-upload-label span {
        font-size: 16px;
        font-weight: 500;
    }
    
    .avatar-upload-label small {
        font-size: 12px;
        color: #adb5bd;
    }
    
    .avatar-preview {
        position: relative;
        margin-top: 20px;
        display: inline-block;
    }
    
    .avatar-preview img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .btn-remove-preview {
        position: absolute;
        top: -10px;
        right: -10px;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #dc3545;
        color: white;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .security-settings {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .login-history {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
    }
    
    .login-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .login-item {
        padding: 10px;
        background: white;
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }
    
    .login-time {
        font-weight: 500;
        color: #333;
        margin-bottom: 5px;
    }
    
    .login-details {
        display: flex;
        gap: 10px;
        font-size: 12px;
        color: #6c757d;
    }
    
    .security-options {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .security-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }
    
    .security-info h5 {
        margin: 0 0 5px 0;
        color: #333;
    }
    
    .security-info p {
        margin: 0;
        color: #6c757d;
        font-size: 14px;
    }
    
    .security-action {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    </style>
    <?php
    include 'includes/footer.php';
}

function handleUserPermissions() {
    // This function would handle permission management
    // For now, we'll redirect to users list
    header('Location: users.php');
    exit();
}

function uploadAvatar($file, $user_id) {
    $uploadDir = '../uploads/avatars/';
    
    // Create directories if they don't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Create user-specific directory
    $userDir = $uploadDir . $user_id . '/';
    if (!file_exists($userDir)) {
        mkdir($userDir, 0755, true);
    }
    
    // Check file size (2MB max)
    $maxSize = 2 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File too large. Maximum size: 2MB'];
    }
    
    // Check file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'error' => 'Only JPG, PNG, GIF, and WebP images are allowed'];
    }
    
    // Generate unique filename
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileName = 'avatar_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $filePath = $userDir . $fileName;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Resize image if needed (optional)
        if (function_exists('imagecreatefromjpeg')) {
            resizeImage($filePath, 400, 400);
        }
        
        return [
            'success' => true,
            'path' => 'uploads/avatars/' . $user_id . '/' . $fileName,
            'name' => $file['name'],
            'size' => $file['size']
        ];
    }
    
    return ['success' => false, 'error' => 'Failed to upload file'];
}

function resizeImage($filePath, $maxWidth, $maxHeight) {
    // Get image info
    list($width, $height, $type) = getimagesize($filePath);
    
    // Calculate new dimensions
    $ratio = $width / $height;
    if ($maxWidth / $maxHeight > $ratio) {
        $newWidth = $maxHeight * $ratio;
        $newHeight = $maxHeight;
    } else {
        $newWidth = $maxWidth;
        $newHeight = $maxWidth / $ratio;
    }
    
    // Create image resource based on type
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($filePath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($filePath);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($filePath);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($filePath);
            break;
        default:
            return false;
    }
    
    // Create new image
    $destination = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency for PNG and GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagecolortransparent($destination, imagecolorallocatealpha($destination, 0, 0, 0, 127));
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
    }
    
    // Resize image
    imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Save image
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($destination, $filePath, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($destination, $filePath, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($destination, $filePath);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($destination, $filePath, 90);
            break;
    }
    
    // Free memory
    imagedestroy($source);
    imagedestroy($destination);
    
    return true;
}

function sendWelcomeEmail($email, $name, $username, $password) {
    $subject = "Welcome to Sokatoto Muda Initiative Trust - Admin Portal";
    $organization = "Sokatoto Muda Initiative Trust";
    $login_url = "https://admin.sokatoto.org"; // Update with your actual admin URL
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . $organization . " <admin@sokatoto.org>\r\n";
    
    $body = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>' . $subject . '</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; }
            .header { background: #0e0c5e; color: white; padding: 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
            .credentials { background: white; padding: 20px; border-left: 4px solid #0e0c5e; margin: 20px 0; }
            .footer { background: #f1f1f1; padding: 20px; text-align: center; font-size: 12px; color: #666; }
            .important { color: #dc3545; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>' . $organization . '</h1>
        </div>
        
        <div class="content">
            <h2>Welcome to the Admin Portal!</h2>
            
            <p>Dear ' . htmlspecialchars($name) . ',</p>
            
            <p>Your administrator account has been created successfully. Below are your login credentials:</p>
            
            <div class="credentials">
                <p><strong>Admin Portal URL:</strong> <a href="' . $login_url . '">' . $login_url . '</a></p>
                <p><strong>Username:</strong> ' . htmlspecialchars($username) . '</p>
                <p><strong>Password:</strong> ' . htmlspecialchars($password) . '</p>
            </div>
            
            <p class="important">Please change your password immediately after your first login.</p>
            
            <p>For security reasons, please:</p>
            <ul>
                <li>Never share your login credentials</li>
                <li>Use a strong, unique password</li>
                <li>Log out after each session</li>
                <li>Contact the system administrator if you suspect any unauthorized access</li>
            </ul>
            
            <p>If you have any questions or need assistance, please contact the system administrator.</p>
            
            <p>Best regards,<br>
            <strong>System Administrator</strong><br>
            ' . $organization . '</p>
        </div>
        
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; ' . date('Y') . ' ' . $organization . '. All rights reserved.</p>
        </div>
    </body>
    </html>
    ';
    
    return mail($email, $subject, $body, $headers);
}

function sendPasswordResetEmail($email, $name, $new_password) {
    $subject = "Password Reset - Sokatoto Muda Initiative Trust Admin Portal";
    $organization = "Sokatoto Muda Initiative Trust";
    $login_url = "https://admin.sokatoto.org";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . $organization . " <admin@sokatoto.org>\r\n";
    
    $body = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>' . $subject . '</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; }
            .header { background: #0e0c5e; color: white; padding: 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
            .credentials { background: white; padding: 20px; border-left: 4px solid #0e0c5e; margin: 20px 0; }
            .footer { background: #f1f1f1; padding: 20px; text-align: center; font-size: 12px; color: #666; }
            .important { color: #dc3545; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Password Reset</h1>
        </div>
        
        <div class="content">
            <h2>Your Password Has Been Reset</h2>
            
            <p>Dear ' . htmlspecialchars($name) . ',</p>
            
            <p>Your password has been reset by the system administrator. Below are your new login credentials:</p>
            
            <div class="credentials">
                <p><strong>Admin Portal URL:</strong> <a href="' . $login_url . '">' . $login_url . '</a></p>
                <p><strong>New Password:</strong> ' . htmlspecialchars($new_password) . '</p>
            </div>
            
            <p class="important">For security reasons, please change your password immediately after logging in.</p>
            
            <p>To change your password:</p>
            <ol>
                <li>Login to the admin portal using the credentials above</li>
                <li>Go to "My Profile"</li>
                <li>Click on "Change Password" tab</li>
                <li>Enter your new password and confirm it</li>
            </ol>
            
            <p>If you did not request this password reset or have any concerns, please contact the system administrator immediately.</p>
            
            <p>Best regards,<br>
            <strong>System Administrator</strong><br>
            ' . $organization . '</p>
        </div>
        
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; ' . date('Y') . ' ' . $organization . '. All rights reserved.</p>
        </div>
    </body>
    </html>
    ';
    
    return mail($email, $subject, $body, $headers);
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}
?>