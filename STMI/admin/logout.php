<?php
session_start();

if (isset($_SESSION['admin_id'])) {
    // Log logout activity
    try {
        // Include the database configuration
        require_once '../config/database.php';
        
        // Check if admin_logs table exists
        $tableExists = $pdo->query("SHOW TABLES LIKE 'admin_logs'")->rowCount() > 0;
        
        if ($tableExists) {
            // Log the logout activity
            $stmt = $pdo->prepare("INSERT INTO admin_logs (user_id, action, ip_address, user_agent) VALUES (?, 'logout', ?, ?)");
            $stmt->execute([
                $_SESSION['admin_id'], 
                $_SERVER['REMOTE_ADDR'] ?? 'Unknown', 
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
        }
    } catch (Exception $e) {
        // If logging fails, still proceed with logout
        error_log("Logout logging failed: " . $e->getMessage());
    }
}

// Clear all session variables
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: login.php');
exit();
?>