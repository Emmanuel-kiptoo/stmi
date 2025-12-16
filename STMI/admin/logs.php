<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

// Check if user has admin permissions
requirePermission('admin');

$action = $_GET['action'] ?? 'list';
$type = $_GET['type'] ?? 'all';
$user_id = $_GET['user_id'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';

switch ($action) {
    case 'view':
        handleLogView();
        break;
    case 'clear':
        handleClearLogs();
        break;
    case 'export':
        handleExportLogs();
        break;
    case 'delete':
        handleDeleteLog();
        break;
    case 'bulk-delete':
        handleBulkDelete();
        break;
    default:
        listLogs();
}

function listLogs() {
    global $pdo, $type, $user_id, $date_from, $date_to, $search;
    
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 50;
    $offset = ($page - 1) * $per_page;
    
    // Check table structure
    $table_info = checkActivityLogsTable();
    
    // Build query based on available columns
    $log_type_column = $table_info['has_log_type'] ? 'l.log_type' : 'l.action_type as log_type';
    
    $sql = "SELECT 
                l.*, 
                $log_type_column,
                u.full_name as user_name,
                u.username as user_username
            FROM activity_logs l
            LEFT JOIN admin_users u ON l.user_id = u.id
            WHERE 1=1";
    
    $count_sql = "SELECT COUNT(*) as total FROM activity_logs l WHERE 1=1";
    $params = [];
    $count_params = [];
    
    if ($type !== 'all') {
        if ($table_info['has_log_type']) {
            $sql .= " AND l.log_type = ?";
            $count_sql .= " AND l.log_type = ?";
        } else {
            $sql .= " AND l.action_type = ?";
            $count_sql .= " AND l.action_type = ?";
        }
        $params[] = $type;
        $count_params[] = $type;
    }
    
    if ($user_id) {
        $sql .= " AND l.user_id = ?";
        $count_sql .= " AND l.user_id = ?";
        $params[] = $user_id;
        $count_params[] = $user_id;
    }
    
    if ($date_from) {
        $sql .= " AND DATE(l.created_at) >= ?";
        $count_sql .= " AND DATE(l.created_at) >= ?";
        $params[] = $date_from;
        $count_params[] = $date_from;
    }
    
    if ($date_to) {
        $sql .= " AND DATE(l.created_at) <= ?";
        $count_sql .= " AND DATE(l.created_at) <= ?";
        $params[] = $date_to;
        $count_params[] = $date_to;
    }
    
    if ($search) {
        $sql .= " AND (l.description LIKE ? OR l.details LIKE ? OR l.ip_address LIKE ?)";
        $count_sql .= " AND (l.description LIKE ? OR l.details LIKE ? OR l.ip_address LIKE ?)";
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
    $sql .= " ORDER BY l.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll();
    
    // Get statistics - using available columns
    $log_type_condition = $table_info['has_log_type'] ? 'log_type' : 'action_type';
    
    $stats_sql = "
        SELECT 
            COUNT(*) as total_logs,
            COUNT(CASE WHEN $log_type_condition = 'error' THEN 1 END) as error_count,
            COUNT(CASE WHEN $log_type_condition = 'warning' THEN 1 END) as warning_count,
            COUNT(CASE WHEN $log_type_condition = 'success' THEN 1 END) as success_count,
            COUNT(CASE WHEN $log_type_condition = 'info' THEN 1 END) as info_count,
            COUNT(CASE WHEN $log_type_condition = 'login' THEN 1 END) as login_count,
            COUNT(CASE WHEN $log_type_condition = 'activity' THEN 1 END) as activity_count,
            COUNT(CASE WHEN user_id IS NOT NULL THEN 1 END) as user_logs_count,
            COUNT(DISTINCT user_id) as unique_users,
            MIN(created_at) as first_log_date,
            MAX(created_at) as last_log_date
        FROM activity_logs
    ";
    
    // Apply filters to stats
    $stats_params = [];
    $stats_where = '';
    
    if ($type !== 'all') {
        $stats_where .= " WHERE $log_type_condition = ?";
        $stats_params[] = $type;
    }
    
    if ($date_from) {
        $stats_where .= (empty($stats_where) ? " WHERE" : " AND") . " DATE(created_at) >= ?";
        $stats_params[] = $date_from;
    }
    
    if ($date_to) {
        $stats_where .= (empty($stats_where) ? " WHERE" : " AND") . " DATE(created_at) <= ?";
        $stats_params[] = $date_to;
    }
    
    $stats_stmt = $pdo->prepare(str_replace("FROM activity_logs", "FROM activity_logs" . $stats_where, $stats_sql));
    $stats_stmt->execute($stats_params);
    $stats = $stats_stmt->fetch();
    
    // Get recent users for filter
    $users_stmt = $pdo->query("
        SELECT DISTINCT u.id, u.full_name, u.username 
        FROM activity_logs l 
        JOIN admin_users u ON l.user_id = u.id 
        ORDER BY u.full_name
        LIMIT 20
    ");
    $users = $users_stmt->fetchAll();
    
    // Get log types for filter
    $type_column = $table_info['has_log_type'] ? 'log_type' : 'action_type';
    $types_stmt = $pdo->query("SELECT DISTINCT $type_column FROM activity_logs ORDER BY $type_column");
    $log_types = $types_stmt->fetchAll();
    
    include 'includes/header.php';
    ?>
    <div class="admin-content">
        <div class="dashboard-header">
            <h1><i class="fas fa-history"></i> Activity Logs</h1>
            <p>Monitor system activities and user actions</p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #667eea;">
                    <i class="fas fa-history"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['total_logs']); ?></h3>
                    <p>Total Logs</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #dc3545;">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['error_count']); ?></h3>
                    <p>Errors</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #ffc107;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['warning_count']); ?></h3>
                    <p>Warnings</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #28a745;">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['unique_users']); ?></h3>
                    <p>Active Users</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="action-buttons">
                <a href="logs.php?type=error" class="btn btn-danger">
                    <i class="fas fa-exclamation-circle"></i> View Errors
                </a>
                <a href="logs.php?type=login" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login Logs
                </a>
                <a href="logs.php?type=create" class="btn btn-success">
                    <i class="fas fa-plus-circle"></i> Create Logs
                </a>
                <a href="logs.php?action=export<?php echo $type !== 'all' ? '&type=' . $type : ''; ?>" class="btn btn-info">
                    <i class="fas fa-download"></i> Export Logs
                </a>
                <a href="logs.php?action=clear" class="btn btn-warning" 
                   onclick="return confirm('Clear all activity logs? This action cannot be undone.')">
                    <i class="fas fa-trash"></i> Clear All Logs
                </a>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="logs-filters">
            <form method="GET" action="logs.php" id="filterForm">
                <input type="hidden" name="action" value="list">
                
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Log Type:</label>
                        <select name="type" class="form-control" onchange="this.form.submit()">
                            <option value="all" <?php echo $type === 'all' ? 'selected' : ''; ?>>All Types</option>
                            <?php foreach ($log_types as $lt): 
                                $log_type_value = $table_info['has_log_type'] ? $lt['log_type'] : $lt['action_type'];
                                $log_type_display = $table_info['has_log_type'] ? $lt['log_type'] : $lt['action_type'];
                            ?>
                                <option value="<?php echo htmlspecialchars($log_type_value); ?>" 
                                        <?php echo $type === $log_type_value ? 'selected' : ''; ?>>
                                    <?php echo ucfirst(htmlspecialchars($log_type_display)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>User:</label>
                        <select name="user_id" class="form-control" onchange="this.form.submit()">
                            <option value="">All Users</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>" 
                                        <?php echo $user_id == $user['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['full_name'] . ' (' . $user['username'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Date From:</label>
                        <input type="date" name="date_from" class="form-control" 
                               value="<?php echo htmlspecialchars($date_from); ?>"
                               onchange="this.form.submit()">
                    </div>
                    
                    <div class="filter-group">
                        <label>Date To:</label>
                        <input type="date" name="date_to" class="form-control" 
                               value="<?php echo htmlspecialchars($date_to); ?>"
                               onchange="this.form.submit()">
                    </div>
                </div>
                
                <div class="filter-row">
                    <div class="filter-group search-group">
                        <label>Search:</label>
                        <div class="search-box">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search in description, details, or IP..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                            <?php if ($type !== 'all' || $user_id || $date_from || $date_to || $search): ?>
                                <a href="logs.php" class="btn btn-sm btn-secondary ml-2">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Logs Table -->
        <div class="logs-container">
            <form method="POST" action="logs.php?action=bulk-delete" id="bulkForm">
                <div class="logs-header">
                    <div class="header-left">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                            <label class="form-check-label" for="selectAll">Select All</label>
                        </div>
                        <button type="submit" class="btn btn-sm btn-danger ml-2" 
                                onclick="return confirm('Delete selected logs?')">
                            <i class="fas fa-trash"></i> Delete Selected
                        </button>
                    </div>
                    <div class="header-right">
                        <span class="badge badge-info">Showing <?php echo number_format(count($logs)); ?> of <?php echo number_format($total_items); ?> logs</span>
                    </div>
                </div>
                
                <?php if (empty($logs)): ?>
                    <div class="empty-state">
                        <i class="fas fa-history fa-3x"></i>
                        <h4>No logs found</h4>
                        <p>No activity logs match your current filters.</p>
                        <?php if ($type !== 'all' || $user_id || $date_from || $date_to || $search): ?>
                            <a href="logs.php" class="btn btn-primary">
                                <i class="fas fa-times"></i> Clear Filters
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="logs-table-container">
                        <table class="logs-table">
                            <thead>
                                <tr>
                                    <th width="40"></th>
                                    <th width="80">ID</th>
                                    <th>Description</th>
                                    <th width="120">Type</th>
                                    <th width="150">User</th>
                                    <th width="150">IP Address</th>
                                    <th width="180">Date & Time</th>
                                    <th width="100">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): 
                                    $log_type = $log['log_type'] ?? $log['action_type'] ?? 'info';
                                    $type_class = strtolower($log_type);
                                ?>
                                    <tr class="log-row log-type-<?php echo $type_class; ?>" data-id="<?php echo $log['id']; ?>">
                                        <td>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input log-checkbox" name="log_ids[]" value="<?php echo $log['id']; ?>">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="log-id">#<?php echo $log['id']; ?></div>
                                        </td>
                                        <td>
                                            <div class="log-description">
                                                <strong><?php echo htmlspecialchars($log['description']); ?></strong>
                                                <?php if ($log['details']): ?>
                                                    <div class="log-details-preview" title="<?php echo htmlspecialchars($log['details']); ?>">
                                                        <?php echo htmlspecialchars(substr($log['details'], 0, 100)); ?>
                                                        <?php if (strlen($log['details']) > 100): ?>...<?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="log-type-badge log-type-<?php echo $type_class; ?>">
                                                <?php echo ucfirst($log_type); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($log['user_id'] && $log['user_name']): ?>
                                                <div class="log-user">
                                                    <div class="user-name"><?php echo htmlspecialchars($log['user_name']); ?></div>
                                                    <div class="user-username">@<?php echo htmlspecialchars($log['user_username']); ?></div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">System</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="log-ip">
                                                <?php if ($log['ip_address']): ?>
                                                    <span class="ip-address"><?php echo htmlspecialchars($log['ip_address']); ?></span>
                                                    <?php if ($log['user_agent']): ?>
                                                        <div class="user-agent">
                                                            <?php 
                                                            $browser = getBrowserInfo($log['user_agent']);
                                                            echo htmlspecialchars($browser['name'] . ' ' . $browser['version']);
                                                            ?>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="log-date">
                                                <div class="date"><?php echo date('M j, Y', strtotime($log['created_at'])); ?></div>
                                                <div class="time"><?php echo date('g:i A', strtotime($log['created_at'])); ?></div>
                                                <div class="time-ago"><?php echo timeAgo($log['created_at']); ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="log-actions">
                                                <a href="logs.php?action=view&id=<?php echo $log['id']; ?>" 
                                                   class="btn btn-sm btn-info" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="logs.php?action=delete&id=<?php echo $log['id']; ?>" 
                                                   class="btn btn-sm btn-danger" title="Delete"
                                                   onclick="return confirm('Delete this log entry?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </form>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <nav aria-label="Page navigation">
                    <ul class="pagination-list">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&type=<?php echo $type; ?>&user_id=<?php echo $user_id; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&search=<?php echo urlencode($search); ?>">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&type=<?php echo $type; ?>&user_id=<?php echo $user_id; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&search=<?php echo urlencode($search); ?>">
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
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&type=<?php echo $type; ?>&user_id=<?php echo $user_id; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&search=<?php echo urlencode($search); ?>">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                
                <div class="pagination-info">
                    Showing <?php echo min(($page - 1) * $per_page + 1, $total_items); ?> - 
                    <?php echo min($page * $per_page, $total_items); ?> of 
                    <?php echo number_format($total_items); ?> logs
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Log Statistics Chart -->
        <div class="chart-container">
            <div class="chart-card">
                <h3><i class="fas fa-chart-bar"></i> Log Statistics (Last 30 Days)</h3>
                <div class="chart-wrapper">
                    <canvas id="logStatsChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity Summary -->
        <div class="activity-summary">
            <div class="summary-card">
                <h3><i class="fas fa-chart-pie"></i> Log Type Distribution</h3>
                <div class="summary-content">
                    <div class="summary-chart">
                        <canvas id="logTypeChart"></canvas>
                    </div>
                    <div class="summary-stats">
                        <div class="stat-item">
                            <span class="stat-label">Total Logs:</span>
                            <span class="stat-value"><?php echo number_format($stats['total_logs']); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Errors:</span>
                            <span class="stat-value" style="color: #dc3545;"><?php echo number_format($stats['error_count']); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Warnings:</span>
                            <span class="stat-value" style="color: #ffc107;"><?php echo number_format($stats['warning_count']); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Success:</span>
                            <span class="stat-value" style="color: #28a745;"><?php echo number_format($stats['success_count']); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Info:</span>
                            <span class="stat-value" style="color: #17a2b8;"><?php echo number_format($stats['info_count']); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">First Log:</span>
                            <span class="stat-value"><?php echo $stats['first_log_date'] ? date('M j, Y', strtotime($stats['first_log_date'])) : 'N/A'; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Last Log:</span>
                            <span class="stat-value"><?php echo $stats['last_log_date'] ? timeAgo($stats['last_log_date']) : 'N/A'; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Bulk selection
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.log-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    
    // View log on row click
    document.querySelectorAll('.log-row').forEach(row => {
        row.addEventListener('click', function(e) {
            // Don't trigger if clicking on links/buttons/checkboxes
            if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || 
                e.target.type === 'checkbox' || e.target.closest('a') || 
                e.target.closest('button') || e.target.closest('.log-actions') ||
                e.target.closest('.form-check')) {
                return;
            }
            
            const logId = this.getAttribute('data-id');
            window.location.href = `logs.php?action=view&id=${logId}`;
        });
    });
    
    // Log Statistics Chart
    document.addEventListener('DOMContentLoaded', function() {
        // Daily logs chart
        const ctx1 = document.getElementById('logStatsChart').getContext('2d');
        const dailyChart = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: ['Jan 1', 'Jan 5', 'Jan 10', 'Jan 15', 'Jan 20', 'Jan 25', 'Jan 30'],
                datasets: [{
                    label: 'Logs per Day',
                    data: [45, 32, 67, 89, 54, 76, 92],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Errors',
                    data: [5, 3, 8, 12, 6, 9, 15],
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
                            text: 'Number of Logs'
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
        
        // Log type distribution chart
        const ctx2 = document.getElementById('logTypeChart').getContext('2d');
        const typeChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Info', 'Error', 'Warning', 'Success', 'Login', 'Create'],
                datasets: [{
                    data: [<?php echo $stats['info_count']; ?>, 
                           <?php echo $stats['error_count']; ?>, 
                           <?php echo $stats['warning_count']; ?>, 
                           <?php echo $stats['success_count']; ?>, 
                           <?php echo $stats['login_count']; ?>, 
                           <?php echo $stats['activity_count']; ?>],
                    backgroundColor: [
                        '#17a2b8', // Info
                        '#dc3545', // Error
                        '#ffc107', // Warning
                        '#28a745', // Success
                        '#007bff', // Login
                        '#6f42c1'  // Create/Activity
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? Math.round(context.raw * 100 / total) : 0;
                                label += context.raw + ' (' + percentage + '%)';
                                return label;
                            }
                        }
                    }
                }
            }
        });
    });
    
    function showNotification(message, type = 'info') {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show notification-alert`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        `;
        
        document.querySelector('.admin-content').prepend(alert);
        
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
    </script>
    
    <style>
    .logs-filters {
        background: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .filter-row {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }
    
    .filter-group {
        flex: 1;
        min-width: 200px;
    }
    
    .filter-group label {
        font-weight: 600;
        margin-bottom: 5px;
        color: #555;
        font-size: 14px;
        display: block;
    }
    
    .search-group {
        flex: 2;
        min-width: 300px;
    }
    
    .search-box {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .search-box input {
        flex: 1;
    }
    
    .search-btn {
        background: #0e0c5e;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
    }
    
    .logs-container {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .logs-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    .header-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .logs-table-container {
        overflow-x: auto;
    }
    
    .logs-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .logs-table th {
        background: #f8f9fa;
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #555;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }
    
    .logs-table td {
        padding: 15px;
        border-bottom: 1px solid #dee2e6;
        vertical-align: top;
    }
    
    .log-row {
        transition: background-color 0.2s;
        cursor: pointer;
    }
    
    .log-row:hover {
        background-color: #f8f9fa;
    }
    
    .log-row.log-type-error:hover {
        background-color: #ffeaea;
    }
    
    .log-row.log-type-warning:hover {
        background-color: #fff9e6;
    }
    
    .log-row.log-type-success:hover {
        background-color: #f0fff4;
    }
    
    .log-id {
        font-weight: 600;
        color: #6c757d;
        font-family: monospace;
    }
    
    .log-description {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .log-details-preview {
        font-size: 13px;
        color: #6c757d;
        background: #f8f9fa;
        padding: 8px;
        border-radius: 4px;
        border-left: 3px solid #dee2e6;
        cursor: help;
        max-width: 400px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .log-type-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .log-type-badge.log-type-error {
        background-color: #dc3545;
        color: white;
    }
    
    .log-type-badge.log-type-warning {
        background-color: #ffc107;
        color: #212529;
    }
    
    .log-type-badge.log-type-success {
        background-color: #28a745;
        color: white;
    }
    
    .log-type-badge.log-type-info {
        background-color: #17a2b8;
        color: white;
    }
    
    .log-type-badge.log-type-login {
        background-color: #007bff;
        color: white;
    }
    
    .log-type-badge.log-type-create {
        background-color: #6f42c1;
        color: white;
    }
    
    .log-type-badge.log-type-update {
        background-color: #fd7e14;
        color: white;
    }
    
    .log-type-badge.log-type-delete {
        background-color: #dc3545;
        color: white;
    }
    
    .log-user {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    
    .user-name {
        font-weight: 500;
        color: #333;
    }
    
    .user-username {
        font-size: 12px;
        color: #6c757d;
    }
    
    .log-ip {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    
    .ip-address {
        font-family: monospace;
        color: #333;
    }
    
    .user-agent {
        font-size: 11px;
        color: #6c757d;
    }
    
    .log-date {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    
    .date {
        font-weight: 500;
        color: #333;
    }
    
    .time {
        font-size: 13px;
        color: #6c757d;
    }
    
    .time-ago {
        font-size: 11px;
        color: #999;
    }
    
    .log-actions {
        display: flex;
        gap: 5px;
    }
    
    .log-actions .btn {
        width: 32px;
        height: 32px;
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
    
    .activity-summary {
        margin-top: 30px;
    }
    
    .summary-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .summary-card h3 {
        margin: 0 0 20px 0;
        color: #333;
        font-size: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .summary-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        align-items: center;
    }
    
    .summary-chart {
        height: 250px;
        position: relative;
    }
    
    .summary-stats {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .stat-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .stat-item:last-child {
        border-bottom: none;
    }
    
    .stat-label {
        font-weight: 500;
        color: #666;
    }
    
    .stat-value {
        font-weight: 600;
        color: #333;
    }
    
    .notification-alert {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        min-width: 300px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    @media (max-width: 768px) {
        .summary-content {
            grid-template-columns: 1fr;
        }
        
        .filter-row {
            flex-direction: column;
        }
        
        .filter-group {
            min-width: 100%;
        }
    }
    </style>
    <?php
    include 'includes/footer.php';
}

function checkActivityLogsTable() {
    global $pdo;
    
    $info = [
        'has_log_type' => false,
        'has_action_type' => false,
        'has_user_agent' => false,
        'has_ip_address' => false
    ];
    
    try {
        // Check table structure
        $stmt = $pdo->query("DESCRIBE activity_logs");
        $columns = $stmt->fetchAll();
        
        foreach ($columns as $column) {
            switch ($column['Field']) {
                case 'log_type':
                    $info['has_log_type'] = true;
                    break;
                case 'action_type':
                    $info['has_action_type'] = true;
                    break;
                case 'user_agent':
                    $info['has_user_agent'] = true;
                    break;
                case 'ip_address':
                    $info['has_ip_address'] = true;
                    break;
            }
        }
        
        // If log_type doesn't exist but action_type does, we can use action_type
        if (!$info['has_log_type'] && $info['has_action_type']) {
            // Add log_type column if it doesn't exist
            try {
                $pdo->exec("ALTER TABLE activity_logs ADD COLUMN log_type VARCHAR(20) DEFAULT 'info' AFTER action_type");
                $info['has_log_type'] = true;
                
                // Update existing records
                $pdo->exec("UPDATE activity_logs SET log_type = action_type WHERE log_type IS NULL OR log_type = ''");
            } catch (Exception $e) {
                // Column might already exist or other error - continue with action_type
            }
        }
        
    } catch (Exception $e) {
        // Table might not exist or other error
        error_log("Error checking activity_logs table: " . $e->getMessage());
    }
    
    return $info;
}

function handleLogView() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    
    // Check table structure
    $table_info = checkActivityLogsTable();
    $log_type_column = $table_info['has_log_type'] ? 'log_type' : 'action_type as log_type';
    
    // Fetch log details
    $stmt = $pdo->prepare("
        SELECT 
            l.*, 
            $log_type_column,
            u.full_name as user_name,
            u.username as user_username,
            u.email as user_email,
            u.profile_picture as user_avatar
        FROM activity_logs l
        LEFT JOIN admin_users u ON l.user_id = u.id
        WHERE l.id = ?
    ");
    $stmt->execute([$id]);
    $log = $stmt->fetch();
    
    if (!$log) {
        $_SESSION['error'] = 'Log entry not found.';
        header('Location: logs.php');
        exit();
    }
    
    // Get related logs (same user or similar action)
    $related_stmt = $pdo->prepare("
        SELECT * FROM activity_logs 
        WHERE (user_id = ? OR action_type = ?) AND id != ?
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $related_stmt->execute([$log['user_id'], $log['action_type'], $id]);
    $related_logs = $related_stmt->fetchAll();
    
    include 'includes/header.php';
    ?>
    <div class="admin-content">
        <div class="log-detail-view">
            <div class="log-header">
                <a href="logs.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Logs
                </a>
                <div class="header-actions">
                    <a href="logs.php?action=delete&id=<?php echo $log['id']; ?>" 
                       class="btn btn-danger"
                       onclick="return confirm('Delete this log entry?')">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </div>
            </div>
            
            <div class="log-detail-card">
                <div class="log-header-info">
                    <div class="log-icon-large">
                        <?php 
                        $log_type = $log['log_type'] ?? $log['action_type'] ?? 'info';
                        $icon = 'info-circle';
                        $color = '#6c757d';
                        
                        switch ($log_type) {
                            case 'error': $icon = 'exclamation-circle'; $color = '#dc3545'; break;
                            case 'warning': $icon = 'exclamation-triangle'; $color = '#ffc107'; break;
                            case 'success': $icon = 'check-circle'; $color = '#28a745'; break;
                            case 'login': $icon = 'sign-in-alt'; $color = '#007bff'; break;
                            case 'create': $icon = 'plus-circle'; $color = '#28a745'; break;
                            case 'update': $icon = 'edit'; $color = '#ffc107'; break;
                            case 'delete': $icon = 'trash'; $color = '#dc3545'; break;
                        }
                        ?>
                        <i class="fas fa-<?php echo $icon; ?>" style="color: <?php echo $color; ?>"></i>
                    </div>
                    <div class="log-title">
                        <h1><?php echo htmlspecialchars($log['description']); ?></h1>
                        <div class="log-meta">
                            <span class="log-type-badge log-type-<?php echo strtolower($log_type); ?>">
                                <?php echo ucfirst($log_type); ?>
                            </span>
                            <span class="log-time">
                                <i class="fas fa-clock"></i> <?php echo date('F j, Y g:i A', strtotime($log['created_at'])); ?>
                                (<?php echo timeAgo($log['created_at']); ?>)
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="log-details-grid">
                    <div class="detail-section">
                        <h3><i class="fas fa-info-circle"></i> Log Information</h3>
                        <div class="detail-list">
                            <div class="detail-item">
                                <label>Log ID:</label>
                                <span>#<?php echo $log['id']; ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Action Type:</label>
                                <span><?php echo htmlspecialchars($log['action_type']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Log Type:</label>
                                <span class="log-type-badge log-type-<?php echo strtolower($log_type); ?>">
                                    <?php echo ucfirst($log_type); ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <label>Created:</label>
                                <span><?php echo date('F j, Y g:i A', strtotime($log['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($log['user_id'] && $log['user_name']): ?>
                    <div class="detail-section">
                        <h3><i class="fas fa-user"></i> User Information</h3>
                        <div class="user-info">
                            <div class="user-avatar-small">
                                <?php if ($log['user_avatar']): ?>
                                    <img src="../<?php echo htmlspecialchars($log['user_avatar']); ?>" 
                                         alt="<?php echo htmlspecialchars($log['user_name']); ?>">
                                <?php else: ?>
                                    <div class="avatar-placeholder-small">
                                        <?php echo strtoupper(substr($log['user_name'], 0, 2)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="user-details">
                                <div class="detail-item">
                                    <label>Name:</label>
                                    <span><?php echo htmlspecialchars($log['user_name']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Username:</label>
                                    <span><?php echo htmlspecialchars($log['user_username']); ?></span>
                                </div>
                                <?php if ($log['user_email']): ?>
                                <div class="detail-item">
                                    <label>Email:</label>
                                    <span><?php echo htmlspecialchars($log['user_email']); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="detail-item">
                                    <label>User ID:</label>
                                    <span>#<?php echo $log['user_id']; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="user-actions">
                            <?php if ($log['user_id']): ?>
                            <a href="users.php?action=view&id=<?php echo $log['user_id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View Profile
                            </a>
                            <a href="logs.php?user_id=<?php echo $log['user_id']; ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-history"></i> View All Logs
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($log['ip_address'] ?? false): ?>
                    <div class="detail-section">
                        <h3><i class="fas fa-network-wired"></i> Network Information</h3>
                        <div class="detail-list">
                            <div class="detail-item">
                                <label>IP Address:</label>
                                <span class="ip-address"><?php echo htmlspecialchars($log['ip_address']); ?></span>
                            </div>
                            <?php if ($log['user_agent'] ?? false): 
                                $browser = getBrowserInfo($log['user_agent']);
                                $os = getOSInfo($log['user_agent']);
                            ?>
                            <div class="detail-item">
                                <label>Browser:</label>
                                <span><?php echo htmlspecialchars($browser['name'] . ' ' . $browser['version']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Operating System:</label>
                                <span><?php echo htmlspecialchars($os); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>User Agent:</label>
                                <div class="user-agent-detail">
                                    <code><?php echo htmlspecialchars($log['user_agent']); ?></code>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($log['details'] ?? false): ?>
                <div class="log-data-section">
                    <h3><i class="fas fa-code"></i> Log Data</h3>
                    <div class="log-data">
                        <pre><?php echo htmlspecialchars($log['details']); ?></pre>
                    </div>
                    <div class="data-actions">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="copyLogData()">
                            <i class="fas fa-copy"></i> Copy to Clipboard
                        </button>
                        <button type="button" class="btn btn-sm btn-info" onclick="toggleJsonView()">
                            <i class="fas fa-eye"></i> Toggle JSON View
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($related_logs)): ?>
                <div class="related-logs">
                    <h3><i class="fas fa-link"></i> Related Logs</h3>
                    <div class="related-list">
                        <?php foreach ($related_logs as $related): 
                            $related_log_type = $related['log_type'] ?? $related['action_type'] ?? 'info';
                        ?>
                            <div class="related-item">
                                <div class="related-icon">
                                    <?php 
                                    $icon = 'info-circle';
                                    switch ($related_log_type) {
                                        case 'error': $icon = 'exclamation-circle'; break;
                                        case 'warning': $icon = 'exclamation-triangle'; break;
                                        case 'success': $icon = 'check-circle'; break;
                                        case 'login': $icon = 'sign-in-alt'; break;
                                        case 'create': $icon = 'plus-circle'; break;
                                        case 'update': $icon = 'edit'; break;
                                        case 'delete': $icon = 'trash'; break;
                                    }
                                    ?>
                                    <i class="fas fa-<?php echo $icon; ?>"></i>
                                </div>
                                <div class="related-content">
                                    <div class="related-header">
                                        <a href="logs.php?action=view&id=<?php echo $related['id']; ?>" class="related-title">
                                            <?php echo htmlspecialchars($related['description']); ?>
                                        </a>
                                        <span class="related-time"><?php echo timeAgo($related['created_at']); ?></span>
                                    </div>
                                    <div class="related-meta">
                                        <span class="log-type-badge log-type-<?php echo strtolower($related_log_type); ?>">
                                            <?php echo ucfirst($related_log_type); ?>
                                        </span>
                                        <?php if ($related['user_id']): ?>
                                            <span class="related-user">User #<?php echo $related['user_id']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
    function copyLogData() {
        const logData = document.querySelector('.log-data pre').innerText;
        navigator.clipboard.writeText(logData).then(() => {
            alert('Log data copied to clipboard!');
        });
    }
    
    function toggleJsonView() {
        const pre = document.querySelector('.log-data pre');
        const text = pre.innerText;
        
        try {
            const json = JSON.parse(text);
            if (pre.classList.contains('pretty')) {
                pre.innerText = JSON.stringify(json);
                pre.classList.remove('pretty');
            } else {
                pre.innerText = JSON.stringify(json, null, 2);
                pre.classList.add('pretty');
            }
        } catch (e) {
            alert('Not a valid JSON format');
        }
    }
    </script>
    
    <style>
    .log-detail-view {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .log-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #dee2e6;
    }
    
    .log-detail-card {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }
    
    .log-header-info {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .log-icon-large {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        background: white;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .log-title {
        flex: 1;
    }
    
    .log-title h1 {
        margin: 0 0 10px 0;
        color: #333;
        font-size: 24px;
    }
    
    .log-meta {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .log-time {
        color: #6c757d;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .log-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
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
    
    .ip-address {
        font-family: monospace;
        background: white;
        padding: 2px 8px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }
    
    .user-info {
        display: flex;
        gap: 15px;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .user-avatar-small img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .avatar-placeholder-small {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 16px;
        border: 3px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .user-details {
        flex: 1;
    }
    
    .user-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .user-agent-detail {
        background: white;
        padding: 10px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        font-family: monospace;
        font-size: 12px;
        max-height: 100px;
        overflow-y: auto;
    }
    
    .log-data-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
    }
    
    .log-data {
        background: white;
        border-radius: 4px;
        padding: 15px;
        border: 1px solid #dee2e6;
        margin-bottom: 15px;
        max-height: 400px;
        overflow-y: auto;
    }
    
    .log-data pre {
        margin: 0;
        white-space: pre-wrap;
        word-break: break-word;
        font-family: 'Consolas', 'Monaco', monospace;
        font-size: 13px;
        line-height: 1.5;
    }
    
    .log-data pre.pretty {
        background: #f5f5f5;
        padding: 10px;
        border-radius: 4px;
    }
    
    .data-actions {
        display: flex;
        gap: 10px;
    }
    
    .related-logs {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
    }
    
    .related-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .related-item {
        display: flex;
        gap: 15px;
        padding: 15px;
        background: white;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        transition: transform 0.2s;
    }
    
    .related-item:hover {
        transform: translateX(5px);
        border-color: #0e0c5e;
    }
    
    .related-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: #0e0c5e;
    }
    
    .related-content {
        flex: 1;
    }
    
    .related-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 5px;
    }
    
    .related-title {
        font-weight: 500;
        color: #333;
        text-decoration: none;
        flex: 1;
    }
    
    .related-title:hover {
        color: #0e0c5e;
        text-decoration: underline;
    }
    
    .related-time {
        font-size: 12px;
        color: #6c757d;
        white-space: nowrap;
    }
    
    .related-meta {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .related-user {
        font-size: 12px;
        color: #6c757d;
        background: #f8f9fa;
        padding: 2px 8px;
        border-radius: 12px;
    }
    </style>
    <?php
    include 'includes/footer.php';
}

function handleClearLogs() {
    global $pdo;
    
    try {
        // Get count before deletion
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM activity_logs");
        $result = $stmt->fetch();
        $count = $result['count'];
        
        // Clear all logs
        $pdo->query("DELETE FROM activity_logs");
        
        // Log the clearing action
        logActivity('clear_logs', 'system', null, null, [
            'logs_cleared' => $count,
            'cleared_by' => $_SESSION['admin_name'] ?? 'System'
        ]);
        
        $_SESSION['message'] = "Successfully cleared {$count} log entries.";
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error clearing logs: ' . $e->getMessage();
    }
    
    header('Location: logs.php');
    exit;
}

function handleExportLogs() {
    global $pdo;
    
    $type = $_GET['type'] ?? 'all';
    $user_id = $_GET['user_id'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    
    // Check table structure
    $table_info = checkActivityLogsTable();
    $log_type_column = $table_info['has_log_type'] ? 'log_type' : 'action_type as log_type';
    
    // Build query with same filters as listLogs
    $sql = "SELECT 
                l.*, 
                $log_type_column,
                u.full_name as user_name,
                u.username as user_username
            FROM activity_logs l
            LEFT JOIN admin_users u ON l.user_id = u.id
            WHERE 1=1";
    
    $params = [];
    
    if ($type !== 'all') {
        if ($table_info['has_log_type']) {
            $sql .= " AND l.log_type = ?";
        } else {
            $sql .= " AND l.action_type = ?";
        }
        $params[] = $type;
    }
    
    if ($user_id) {
        $sql .= " AND l.user_id = ?";
        $params[] = $user_id;
    }
    
    if ($date_from) {
        $sql .= " AND DATE(l.created_at) >= ?";
        $params[] = $date_from;
    }
    
    if ($date_to) {
        $sql .= " AND DATE(l.created_at) <= ?";
        $params[] = $date_to;
    }
    
    $sql .= " ORDER BY l.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll();
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="activity_logs_' . date('Y-m-d_H-i-s') . '.csv"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, [
        'ID', 
        'Timestamp', 
        'Log Type', 
        'Action Type', 
        'Description', 
        'User ID', 
        'User Name', 
        'User Username', 
        'IP Address', 
        'Browser', 
        'Details'
    ]);
    
    // Add data rows
    foreach ($logs as $log) {
        $browser = getBrowserInfo($log['user_agent'] ?? '');
        $log_type = $log['log_type'] ?? $log['action_type'] ?? 'info';
        
        fputcsv($output, [
            $log['id'],
            $log['created_at'],
            $log_type,
            $log['action_type'],
            $log['description'],
            $log['user_id'] ?? '',
            $log['user_name'] ?? '',
            $log['user_username'] ?? '',
            $log['ip_address'] ?? '',
            $browser['name'] . ' ' . $browser['version'],
            substr($log['details'] ?? '', 0, 500) // Limit details length
        ]);
    }
    
    fclose($output);
    
    // Log export activity
    logActivity('export_logs', 'system', null, null, [
        'type' => $type,
        'count' => count($logs),
        'format' => 'csv'
    ]);
    
    exit;
}

function handleDeleteLog() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    
    if (!$id) {
        $_SESSION['error'] = 'No log ID specified.';
        header('Location: logs.php');
        exit();
    }
    
    try {
        // Get log details before deletion for logging
        $stmt = $pdo->prepare("SELECT * FROM activity_logs WHERE id = ?");
        $stmt->execute([$id]);
        $log = $stmt->fetch();
        
        if (!$log) {
            $_SESSION['error'] = 'Log entry not found.';
            header('Location: logs.php');
            exit();
        }
        
        // Delete the log
        $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE id = ?");
        $stmt->execute([$id]);
        
        // Log the deletion
        logActivity('delete_log', 'activity_logs', $id, [
            'description' => $log['description'],
            'action_type' => $log['action_type'],
            'user_id' => $log['user_id']
        ], null);
        
        $_SESSION['message'] = 'Log entry deleted successfully.';
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error deleting log: ' . $e->getMessage();
    }
    
    header('Location: logs.php');
    exit;
}

function handleBulkDelete() {
    global $pdo;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: logs.php');
        exit();
    }
    
    $log_ids = $_POST['log_ids'] ?? [];
    
    if (empty($log_ids)) {
        $_SESSION['error'] = 'No logs selected for deletion.';
        header('Location: logs.php');
        exit();
    }
    
    try {
        // Convert IDs to integers and create placeholders
        $ids = array_map('intval', $log_ids);
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        
        // Get count before deletion
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM activity_logs WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $result = $stmt->fetch();
        $count = $result['count'];
        
        // Delete logs
        $stmt = $pdo->prepare("DELETE FROM activity_logs WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        
        // Log bulk deletion
        logActivity('bulk_delete_logs', 'activity_logs', null, null, [
            'logs_deleted' => $count,
            'log_ids' => implode(',', $ids),
            'deleted_by' => $_SESSION['admin_name'] ?? 'System'
        ]);
        
        $_SESSION['message'] = "Successfully deleted {$count} log entries.";
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error deleting logs: ' . $e->getMessage();
    }
    
    header('Location: logs.php');
    exit;
}

function getBrowserInfo($user_agent) {
    $browser = [
        'name' => 'Unknown',
        'version' => ''
    ];
    
    if (empty($user_agent)) {
        return $browser;
    }
    
    // Check for common browsers
    if (strpos($user_agent, 'Firefox') !== false) {
        $browser['name'] = 'Firefox';
        if (preg_match('/Firefox\/([0-9\.]+)/', $user_agent, $matches)) {
            $browser['version'] = $matches[1];
        }
    } elseif (strpos($user_agent, 'Chrome') !== false) {
        $browser['name'] = 'Chrome';
        if (preg_match('/Chrome\/([0-9\.]+)/', $user_agent, $matches)) {
            $browser['version'] = $matches[1];
        }
    } elseif (strpos($user_agent, 'Safari') !== false) {
        $browser['name'] = 'Safari';
        if (preg_match('/Version\/([0-9\.]+)/', $user_agent, $matches)) {
            $browser['version'] = $matches[1];
        }
    } elseif (strpos($user_agent, 'MSIE') !== false || strpos($user_agent, 'Trident') !== false) {
        $browser['name'] = 'Internet Explorer';
        if (preg_match('/MSIE ([0-9\.]+)/', $user_agent, $matches)) {
            $browser['version'] = $matches[1];
        } elseif (preg_match('/rv:([0-9\.]+)/', $user_agent, $matches)) {
            $browser['version'] = $matches[1];
        }
    } elseif (strpos($user_agent, 'Edge') !== false) {
        $browser['name'] = 'Edge';
        if (preg_match('/Edge\/([0-9\.]+)/', $user_agent, $matches)) {
            $browser['version'] = $matches[1];
        }
    } elseif (strpos($user_agent, 'Opera') !== false) {
        $browser['name'] = 'Opera';
        if (preg_match('/Opera\/([0-9\.]+)/', $user_agent, $matches)) {
            $browser['version'] = $matches[1];
        }
    }
    
    return $browser;
}

function getOSInfo($user_agent) {
    if (empty($user_agent)) {
        return 'Unknown';
    }
    
    $os = 'Unknown';
    
    // Check for operating systems
    if (strpos($user_agent, 'Windows NT 10.0') !== false) {
        $os = 'Windows 10';
    } elseif (strpos($user_agent, 'Windows NT 6.3') !== false) {
        $os = 'Windows 8.1';
    } elseif (strpos($user_agent, 'Windows NT 6.2') !== false) {
        $os = 'Windows 8';
    } elseif (strpos($user_agent, 'Windows NT 6.1') !== false) {
        $os = 'Windows 7';
    } elseif (strpos($user_agent, 'Windows NT 6.0') !== false) {
        $os = 'Windows Vista';
    } elseif (strpos($user_agent, 'Windows NT 5.1') !== false) {
        $os = 'Windows XP';
    } elseif (strpos($user_agent, 'Macintosh') !== false) {
        $os = 'Mac OS';
    } elseif (strpos($user_agent, 'Linux') !== false) {
        $os = 'Linux';
    } elseif (strpos($user_agent, 'Android') !== false) {
        $os = 'Android';
    } elseif (strpos($user_agent, 'iPhone') !== false || strpos($user_agent, 'iPad') !== false) {
        $os = 'iOS';
    }
    
    return $os;
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