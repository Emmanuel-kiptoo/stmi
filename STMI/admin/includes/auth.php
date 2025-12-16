<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Check if user still exists in database
$stmt = $pdo->prepare("SELECT id, status FROM admin_users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();

if (!$admin || $admin['status'] !== 'active') {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Check permissions function
function hasPermission($requiredRole) {
    $roleHierarchy = ['editor' => 1, 'admin' => 2, 'super_admin' => 3];
    $userRole = $_SESSION['admin_role'];
    
    return $roleHierarchy[$userRole] >= $roleHierarchy[$requiredRole];
}

// Redirect if no permission
function requirePermission($requiredRole) {
    if (!hasPermission($requiredRole)) {
        $_SESSION['error'] = 'You do not have permission to access this page.';
        header('Location: dashboard.php');
        exit();
    }
}

// Log activity function
function logActivity($action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        INSERT INTO admin_logs 
        (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $_SESSION['admin_id'],
        $action,
        $tableName,
        $recordId,
        $oldValues ? json_encode($oldValues) : null,
        $newValues ? json_encode($newValues) : null,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);
}
?>