<?php
require_once '../config/database.php';
requireAdmin();

// Only admins can view activity log
if ($_SESSION['admin_role'] !== 'admin') {
    header('Location: dashboard.php?error=unauthorized');
    exit;
}

// Get filter parameters
$user_id = $_GET['user'] ?? '';
$action = $_GET['action'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT al.*, u.username, u.full_name 
        FROM activity_log al 
        LEFT JOIN users u ON al.user_id = u.id 
        WHERE 1=1";
$params = [];

if (!empty($user_id) && is_numeric($user_id)) {
    $sql .= " AND al.user_id = ?";
    $params[] = $user_id;
}

if (!empty($action)) {
    $sql .= " AND al.action = ?";
    $params[] = $action;
}

if (!empty($date_from)) {
    $sql .= " AND DATE(al.created_at) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $sql .= " AND DATE(al.created_at) <= ?";
    $params[] = $date_to;
}

if (!empty($search)) {
    $sql .= " AND (al.details LIKE ? OR u.username LIKE ? OR u.full_name LIKE ?)";
    $search_term = "%$search%";
    array_push($params, $search_term, $search_term, $search_term);
}

$sql .= " ORDER BY al.created_at DESC LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$activities = $stmt->fetchAll();

// Get distinct actions for filter
$actions = $pdo->query("SELECT DISTINCT action FROM activity_log ORDER BY action")->fetchAll();

// Get users for filter
$users = $pdo->query("SELECT id, username, full_name FROM users ORDER BY username")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .log-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-group label {
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }
        
        .filter-group input,
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .filter-actions {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        
        .log-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            gap: 15px;
            align-items: flex-start;
        }
        
        .log-item:hover {
            background: #f8f9fa;
        }
        
        .log-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            flex-shrink: 0;
        }
        
        .log-content {
            flex: 1;
        }
        
        .log-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .log-user {
            font-weight: 600;
            color: #333;
        }
        
        .log-action {
            color: #0e0c5e;
            font-weight: 600;
        }
        
        .log-time {
            color: #666;
            font-size: 0.85rem;
        }
        
        .log-details {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
            margin-top: 5px;
        }
        
        .log-meta {
            display: flex;
            gap: 15px;
            margin-top: 10px;
            font-size: 0.8rem;
            color: #888;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        .export-btn {
            background: #57cc99;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .clear-btn {
            background: #f39c12;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1>Activity Log</h1>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="exportLog()">
                    <i class="fas fa-download"></i> Export Log
                </button>
                <button class="btn btn-danger" onclick="clearOldLogs()">
                    <i class="fas fa-trash"></i> Clear Old Logs
                </button>
            </div>
        </div>
        
        <div class="log-container">
            <!-- Filter Form -->
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label>User</label>
                    <select name="user">
                        <option value="">All Users</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>" 
                                <?php echo $user_id == $user['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['full_name'] . ' (@' . $user['username'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Action</label>
                    <select name="action">
                        <option value="">All Actions</option>
                        <?php foreach ($actions as $action_item): ?>
                            <option value="<?php echo $action_item['action']; ?>" 
                                <?php echo $action == $action_item['action'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($action_item['action']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Date From</label>
                    <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                
                <div class="filter-group">
                    <label>Date To</label>
                    <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                
                <div class="filter-group">
                    <label>Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search in details...">
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='activity_log.php'">
                        <i class="fas fa-redo"></i> Clear
                    </button>
                </div>
            </form>
            
            <!-- Activity List -->
            <?php if (empty($activities)): ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>No Activity Found</h3>
                    <p><?php echo !empty($search) ? 'Try a different search.' : 'Activity will appear here as users interact with the system.'; ?></p>
                </div>
            <?php else: ?>
                <?php foreach ($activities as $activity): ?>
                    <div class="log-item">
                        <div class="log-icon">
                            <i class="fas fa-<?php echo getActionIcon($activity['action']); ?>"></i>
                        </div>
                        
                        <div class="log-content">
                            <div class="log-header">
                                <div class="log-user">
                                    <?php echo htmlspecialchars($activity['full_name'] ?: $activity['username'] ?: 'System'); ?>
                                    <span class="log-action">
                                        <?php echo formatAction($activity['action']); ?>
                                    </span>
                                </div>
                                <div class="log-time">
                                    <?php echo date('M d, Y H:i:s', strtotime($activity['created_at'])); ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($activity['details'])): ?>
                                <div class="log-details">
                                    <?php echo htmlspecialchars($activity['details']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="log-meta">
                                <?php if ($activity['ip_address']): ?>
                                    <span><i class="fas fa-globe"></i> <?php echo htmlspecialchars($activity['ip_address']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
    
    <script>
        function exportLog() {
            const params = new URLSearchParams(window.location.search);
            window.open('handlers/export_activity.php?' + params.toString(), '_blank');
        }
        
        function clearOldLogs() {
            if (confirm('Clear activity logs older than 30 days?')) {
                fetch('handlers/clear_activity.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
            }
        }
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>

<?php
// Helper functions
function getActionIcon($action) {
    $icons = [
        'login' => 'sign-in-alt',
        'logout' => 'sign-out-alt',
        'create' => 'plus-circle',
        'update' => 'edit',
        'delete' => 'trash',
        'password_reset' => 'key',
        'send_reset_link' => 'envelope',
        'upload' => 'upload',
        'download' => 'download'
    ];
    return $icons[$action] ?? 'history';
}

function formatAction($action) {
    return ucwords(str_replace('_', ' ', $action));
}
?>