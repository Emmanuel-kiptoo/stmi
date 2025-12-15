<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .users-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .users-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 8px 15px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .filter-btn.active {
            background: #0e0c5e;
            color: white;
            border-color: #0e0c5e;
        }
        
        .filter-btn:hover {
            background: #f8f9fa;
        }
        
        .search-box {
            display: flex;
            gap: 10px;
            flex: 1;
            max-width: 400px;
        }
        
        .search-box input {
            flex: 1;
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .search-box button {
            background: #0e0c5e;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .users-table {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 1px solid #eee;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0e0c5e, #ff9d0b);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-details h4 {
            margin: 0 0 5px 0;
            color: #333;
        }
        
        .user-details p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .role-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .role-admin {
            background: #0e0c5e;
            color: white;
        }
        
        .role-editor {
            background: #3498db;
            color: white;
        }
        
        .role-viewer {
            background: #57cc99;
            color: white;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .btn-action {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-edit { background: #3498db; color: white; }
        .btn-delete { background: #e74c3c; color: white; }
        .btn-toggle { background: #f39c12; color: white; }
        .btn-reset { background: #9b59b6; color: white; }
        
        .last-login {
            font-size: 0.8rem;
            color: #666;
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
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .page-btn {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .page-btn.active {
            background: #0e0c5e;
            color: white;
            border-color: #0e0c5e;
        }
        
        .bulk-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .bulk-select {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .stat-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            color: #0e0c5e;
            margin-bottom: 10px;
        }
        
        .stat-card p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .stat-card.admin { border-left: 4px solid #0e0c5e; }
        .stat-card.editor { border-left: 4px solid #3498db; }
        .stat-card.viewer { border-left: 4px solid #57cc99; }
        .stat-card.active { border-left: 4px solid #28a745; }
        .stat-card.inactive { border-left: 4px solid #dc3545; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1>User Management</h1>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="window.location.href='users.php?action=add'">
                    <i class="fas fa-user-plus"></i> Add New User
                </button>
            </div>
        </div>
        
        <!-- Error/Success Messages -->
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php
                $errors = [
                    'unauthorized' => 'You do not have permission to manage users.',
                    'self_edit' => 'You cannot edit your own account from here. Use Profile page.',
                    'self_delete' => 'You cannot delete your own account.',
                    'last_admin' => 'Cannot delete the last administrator.',
                    'self_toggle' => 'You cannot change your own status.',
                    'password_mismatch' => 'Passwords do not match.',
                    'username_exists' => 'Username already exists.',
                    'email_exists' => 'Email already exists.'
                ];
                echo $errors[$_GET['error']] ?? 'An error occurred.';
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success">
                <?php
                $messages = [
                    'added' => 'User added successfully.',
                    'updated' => 'User updated successfully.',
                    'deleted' => 'User deleted successfully.',
                    'status_updated' => 'User status updated.',
                    'password_reset' => 'Password reset successfully.'
                ];
                echo $messages[$_GET['msg']] ?? 'Operation completed successfully.';
                ?>
            </div>
        <?php endif; ?>
        
        <!-- Stats Cards -->
        <?php
        $stmt = $pdo->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
            SUM(CASE WHEN role = 'editor' THEN 1 ELSE 0 END) as editors,
            SUM(CASE WHEN role = 'viewer' THEN 1 ELSE 0 END) as viewers,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive
            FROM users");
        $stats = $stmt->fetch();
        ?>
        
        <div class="stat-cards">
            <div class="stat-card admin">
                <h3><?php echo $stats['admins']; ?></h3>
                <p>Administrators</p>
            </div>
            <div class="stat-card editor">
                <h3><?php echo $stats['editors']; ?></h3>
                <p>Editors</p>
            </div>
            <div class="stat-card viewer">
                <h3><?php echo $stats['viewers']; ?></h3>
                <p>Viewers</p>
            </div>
            <div class="stat-card active">
                <h3><?php echo $stats['active']; ?></h3>
                <p>Active Users</p>
            </div>
            <div class="stat-card inactive">
                <h3><?php echo $stats['inactive']; ?></h3>
                <p>Inactive Users</p>
            </div>
        </div>
        
        <!-- Search and Filters -->
        <div class="users-header">
            <div class="filters">
                <button class="filter-btn <?php echo empty($role) ? 'active' : ''; ?>" 
                        onclick="window.location.href='users.php'">
                    All Users
                </button>
                <button class="filter-btn <?php echo $role === 'admin' ? 'active' : ''; ?>" 
                        onclick="window.location.href='users.php?role=admin'">
                    Admins
                </button>
                <button class="filter-btn <?php echo $role === 'editor' ? 'active' : ''; ?>" 
                        onclick="window.location.href='users.php?role=editor'">
                    Editors
                </button>
                <button class="filter-btn <?php echo $role === 'viewer' ? 'active' : ''; ?>" 
                        onclick="window.location.href='users.php?role=viewer'">
                    Viewers
                </button>
                <button class="filter-btn <?php echo $status === 'active' ? 'active' : ''; ?>" 
                        onclick="window.location.href='users.php?status=active'">
                    Active
                </button>
                <button class="filter-btn <?php echo $status === 'inactive' ? 'active' : ''; ?>" 
                        onclick="window.location.href='users.php?status=inactive'">
                    Inactive
                </button>
            </div>
            
            <div class="search-box">
                <form method="GET" action="" style="display: flex; gap: 10px; width: 100%;">
                    <input type="text" name="search" placeholder="Search users..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Bulk Actions -->
        <div class="bulk-actions">
            <div class="bulk-select">
                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                <label for="selectAll">Select All</label>
            </div>
            
            <select id="bulkAction" class="filter-btn">
                <option value="">Bulk Actions</option>
                <option value="activate">Activate</option>
                <option value="deactivate">Deactivate</option>
                <option value="delete">Delete</option>
                <option value="export">Export Selected</option>
            </select>
            
            <button class="btn btn-secondary" onclick="applyBulkAction()">
                Apply
            </button>
        </div>
        
        <!-- Users Table -->
        <div class="users-container">
            <?php if (empty($users)): ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>No Users Found</h3>
                    <p><?php echo !empty($search) ? 'Try a different search term.' : 'Add your first user to get started.'; ?></p>
                    <?php if (empty($search)): ?>
                        <button class="btn btn-primary" onclick="window.location.href='users.php?action=add'">
                            <i class="fas fa-user-plus"></i> Add First User
                        </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="users-table">
                    <table>
                        <thead>
                            <tr>
                                <th width="50"></th>
                                <th>User</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="user-checkbox" value="<?php echo $user['id']; ?>">
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <?php 
                                                $initials = '';
                                                if ($user['full_name']) {
                                                    $nameParts = explode(' ', $user['full_name']);
                                                    $initials = strtoupper(substr($nameParts[0], 0, 1));
                                                    if (count($nameParts) > 1) {
                                                        $initials .= strtoupper(substr($nameParts[1], 0, 1));
                                                    }
                                                } else {
                                                    $initials = substr(strtoupper($user['username']), 0, 2);
                                                }
                                                echo $initials;
                                                ?>
                                            </div>
                                            <div class="user-details">
                                                <h4><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></h4>
                                                <p><?php echo htmlspecialchars($user['email']); ?></p>
                                                <p><small>@<?php echo htmlspecialchars($user['username']); ?></small></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="role-badge role-<?php echo $user['role']; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $user['status']; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td class="last-login">
                                        <?php if ($user['last_login']): ?>
                                            <?php echo date('M d, Y H:i', strtotime($user['last_login'])); ?>
                                            <br>
                                            <small><?php echo time_ago($user['last_login']); ?> ago</small>
                                        <?php else: ?>
                                            <em>Never logged in</em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-edit" 
                                                    onclick="window.location.href='users.php?action=edit&id=<?php echo $user['id']; ?>'"
                                                    <?php echo $user['id'] == $_SESSION['admin_id'] ? 'disabled title="Edit your profile from Profile page"' : ''; ?>>
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            
                                            <button class="btn-action btn-toggle" 
                                                    onclick="toggleStatus(<?php echo $user['id']; ?>)"
                                                    <?php echo $user['id'] == $_SESSION['admin_id'] ? 'disabled title="Cannot change your own status"' : ''; ?>>
                                                <i class="fas fa-power-off"></i> 
                                                <?php echo $user['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>
                                            </button>
                                            
                                            <button class="btn-action btn-reset" 
                                                    onclick="resetPassword(<?php echo $user['id']; ?>)"
                                                    <?php echo $user['id'] == $_SESSION['admin_id'] ? 'disabled title="Reset your password from Profile page"' : ''; ?>>
                                                <i class="fas fa-key"></i> Reset
                                            </button>
                                            
                                            <button class="btn-action btn-delete" 
                                                    onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')"
                                                    <?php echo $user['id'] == $_SESSION['admin_id'] ? 'disabled title="Cannot delete your own account"' : ''; ?>>
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <script>
        // Select All checkbox
        function toggleSelectAll(checkbox) {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = checkbox.checked;
            });
        }
        
        // Apply bulk action
        function applyBulkAction() {
            const action = document.getElementById('bulkAction').value;
            const selectedUsers = Array.from(document.querySelectorAll('.user-checkbox:checked'))
                .map(cb => cb.value);
            
            if (selectedUsers.length === 0) {
                alert('Please select at least one user.');
                return;
            }
            
            if (!action) {
                alert('Please select an action.');
                return;
            }
            
            switch (action) {
                case 'activate':
                    if (confirm(`Activate ${selectedUsers.length} user(s)?`)) {
                        updateUsersStatus(selectedUsers, 'active');
                    }
                    break;
                    
                case 'deactivate':
                    if (confirm(`Deactivate ${selectedUsers.length} user(s)?`)) {
                        updateUsersStatus(selectedUsers, 'inactive');
                    }
                    break;
                    
                case 'delete':
                    if (confirm(`Delete ${selectedUsers.length} user(s)? This action cannot be undone.`)) {
                        deleteUsers(selectedUsers);
                    }
                    break;
                    
                case 'export':
                    exportUsers(selectedUsers);
                    break;
            }
        }
        
        // Update users status
        function updateUsersStatus(userIds, status) {
            fetch('handlers/bulk_update_users.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'update_status',
                    ids: userIds,
                    status: status
                })
            })
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
        
        // Delete users
        function deleteUsers(userIds) {
            fetch('handlers/bulk_update_users.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'delete',
                    ids: userIds
                })
            })
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
        
        // Export users
        function exportUsers(userIds) {
            const params = new URLSearchParams();
            userIds.forEach(id => params.append('ids[]', id));
            
            window.open('handlers/export_users.php?' + params.toString(), '_blank');
        }
        
        // Individual user actions
        function toggleStatus(userId) {
            if (confirm('Change user status?')) {
                window.location.href = 'users.php?action=toggle_status&id=' + userId;
            }
        }
        
        function resetPassword(userId) {
            const newPassword = prompt('Enter new password (min 6 characters):');
            if (newPassword && newPassword.length >= 6) {
                const confirmPassword = prompt('Confirm new password:');
                if (newPassword === confirmPassword) {
                    fetch('handlers/reset_password.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            user_id: userId,
                            new_password: newPassword
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Password reset successfully!');
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
                } else {
                    alert('Passwords do not match.');
                }
            } else if (newPassword !== null) {
                alert('Password must be at least 6 characters.');
            }
        }
        
        function deleteUser(userId, username) {
            if (confirm(`Are you sure you want to delete user "${username}"? This action cannot be undone.`)) {
                window.location.href = 'users.php?action=delete&id=' + userId;
            }
        }
        
        // Time ago function
        function time_ago(datetime) {
            const date = new Date(datetime);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);
            
            let interval = Math.floor(seconds / 31536000);
            if (interval >= 1) return interval + " year" + (interval > 1 ? "s" : "");
            
            interval = Math.floor(seconds / 2592000);
            if (interval >= 1) return interval + " month" + (interval > 1 ? "s" : "");
            
            interval = Math.floor(seconds / 86400);
            if (interval >= 1) return interval + " day" + (interval > 1 ? "s" : "");
            
            interval = Math.floor(seconds / 3600);
            if (interval >= 1) return interval + " hour" + (interval > 1 ? "s" : "");
            
            interval = Math.floor(seconds / 60);
            if (interval >= 1) return interval + " minute" + (interval > 1 ? "s" : "");
            
            return Math.floor(seconds) + " second" + (seconds > 1 ? "s" : "");
        }
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>

<?php
// Helper function for time ago
function time_ago($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) return $diff . " seconds ago";
    $diff = round($diff / 60);
    if ($diff < 60) return $diff . " minutes ago";
    $diff = round($diff / 60);
    if ($diff < 24) return $diff . " hours ago";
    $diff = round($diff / 24);
    if ($diff < 7) return $diff . " days ago";
    $diff = round($diff / 7);
    if ($diff < 4) return $diff . " weeks ago";
    
    return date('M d, Y', $time);
}
?>