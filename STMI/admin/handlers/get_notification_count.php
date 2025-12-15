<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // Get unread contact messages
    $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'");
    $unread_messages = $stmt->fetchColumn();
    
    // Get pending donations
    $stmt = $pdo->query("SELECT COUNT(*) FROM donations WHERE status = 'pending'");
    $pending_donations = $stmt->fetchColumn();
    
    // Get today's events
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE event_date = ?");
    $stmt->execute([$today]);
    $today_events = $stmt->fetchColumn();
    
    $total_count = $unread_messages + $pending_donations + $today_events;
    
    echo json_encode([
        'success' => true,
        'count' => $total_count,
        'breakdown' => [
            'messages' => $unread_messages,
            'donations' => $pending_donations,
            'events' => $today_events
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>