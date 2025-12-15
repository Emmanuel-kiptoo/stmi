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
    
    if (!isset($data['action']) || !isset($data['ids']) || !is_array($data['ids'])) {
        $response['message'] = 'Invalid request data.';
        echo json_encode($response);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        $user_ids = array_map('intval', $data['ids']);
        $placeholders = implode(',', array_fill(0, count($user_ids), '?'));
        
        switch ($data['action']) {
            case 'update_status':
                if (!isset($data['status']) || !in_array($data['status'], ['active', 'inactive'])) {
                    throw new Exception('Invalid status.');
                }
                
                // Don't allow changing own status
                $user_ids = array_diff($user_ids, [$_SESSION['admin_id']]);
                if (empty($user_ids)) {
                    throw new Exception('You cannot change your own status.');
                }
                
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET status = ?, updated_at = NOW() 
                    WHERE id IN ($placeholders)
                ");
                
                $params = array_merge([$data['status']], $user_ids);
                $stmt->execute($params);
                
                $response['success'] = true;
                $response['message'] = count($user_ids) . ' user(s) status updated.';
                break;
                
            case 'delete':
                // Don't allow deleting self
                $user_ids = array_diff($user_ids, [$_SESSION['admin_id']]);
                if (empty($user_ids)) {
                    throw new Exception('You cannot delete your own account.');
                }
                
                // Check if deleting last admin
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as admin_count 
                    FROM users 
                    WHERE role = 'admin' 
                    AND id NOT IN ($placeholders)
                ");
                $stmt->execute($user_ids);
                $admin_count = $stmt->fetch()['admin_count'];
                
                if ($admin_count == 0) {
                    throw new Exception('Cannot delete the last administrator.');
                }
                
                $stmt = $pdo->prepare("DELETE FROM users WHERE id IN ($placeholders)");
                $stmt->execute($user_ids);
                
                $response['success'] = true;
                $response['message'] = count($user_ids) . ' user(s) deleted.';
                break;
                
            default:
                throw new Exception('Invalid action.');
        }
        
        $pdo->commit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $response['message'] = $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method']);
?>