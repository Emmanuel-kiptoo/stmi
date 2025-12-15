<?php
require_once '../config/database.php';
requireAdmin();

// Check if user has permission (only admins can manage users)
if ($_SESSION['admin_role'] !== 'admin') {
    header('Location: dashboard.php?error=unauthorized');
    exit;
}

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

switch ($action) {
    case 'add':
        include 'views/users/add.php';
        break;
        
    case 'edit':
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        // Prevent editing yourself to avoid lockout
        if ($user['id'] == $_SESSION['admin_id']) {
            header('Location: users.php?error=self_edit');
            exit;
        }
        
        include 'views/users/edit.php';
        break;
        
    case 'delete':
        // Prevent deleting yourself
        if ($id == $_SESSION['admin_id']) {
            header('Location: users.php?error=self_delete');
            exit;
        }
        
        // Prevent deleting the last admin
        $stmt = $pdo->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin' AND id != ?");
        $stmt->execute([$id]);
        $admin_count = $stmt->fetch()['admin_count'];
        
        if ($admin_count == 0) {
            header('Location: users.php?error=last_admin');
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        header('Location: users.php?msg=deleted');
        exit;
        break;
        
    case 'toggle_status':
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if ($user && $id != $_SESSION['admin_id']) {
            $stmt = $pdo->prepare("UPDATE users SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?");
            $stmt->execute([$id]);
            
            header('Location: users.php?msg=status_updated');
            exit;
        }
        
        header('Location: users.php?error=self_toggle');
        exit;
        break;
        
    default:
        // Get all users
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (username LIKE ? OR full_name LIKE ? OR email LIKE ?)";
            $search_term = "%$search%";
            $params = array_fill(0, 3, $search_term);
        }
        
        if (!empty($role) && in_array($role, ['admin', 'editor', 'viewer'])) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }
        
        if (!empty($status) && in_array($status, ['active', 'inactive'])) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY 
            CASE role 
                WHEN 'admin' THEN 1
                WHEN 'editor' THEN 2
                WHEN 'viewer' THEN 3
                ELSE 4
            END, 
            full_name ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();
        
        include 'views/users/list.php';
        break;
}
?>