<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $response = ['success' => false, 'message' => ''];
    
    if (!isset($data['user_id']) || !isset($data['new_password'])) {
        $response['message'] = 'Missing required data.';
        echo json_encode($response);
        exit;
    }
    
    $user_id = intval($data['user_id']);
    $new_password = $data['new_password'];
    
    // Don't allow resetting own password
    if ($user_id == $_SESSION['admin_id']) {
        $response['message'] = 'Reset your own password from Profile page.';
        echo json_encode($response);
        exit;
    }
    
    if (strlen($new_password) < 6) {
        $response['message'] = 'Password must be at least 6 characters.';
        echo json_encode($response);
        exit;
    }
    
    try {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            UPDATE users 
            SET password = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$hashed_password, $user_id]);
        
        // Log this action
        logAction('password_reset', "Reset password for user ID: $user_id");
        
        $response['success'] = true;
        $response['message'] = 'Password reset successfully.';
        
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method']);

// Helper function to log actions
function logAction($action, $details) {
    global $pdo;
    $stmt = $pdo->prepare("
        INSERT INTO activity_log 
        (user_id, action, details, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_SESSION['admin_id'],
        $action,
        $details,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
}
?>