<?php
require_once '../includes/auth.php';
require_once '../../config/database.php';

$stmt = $pdo->query("SELECT COUNT(*) as count FROM admin_contacts WHERE status = 'unread'");
$result = $stmt->fetch();

header('Content-Type: application/json');
echo json_encode(['count' => $result['count']]);
?>