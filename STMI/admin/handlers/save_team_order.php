<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['order']) && is_array($data['order'])) {
        try {
            $pdo->beginTransaction();
            
            foreach ($data['order'] as $item) {
                $stmt = $pdo->prepare("UPDATE team_members SET display_order = ? WHERE id = ?");
                $stmt->execute([$item['order'], $item['id']]);
            }
            
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Order updated successfully']);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
    }
    exit;
}
?>