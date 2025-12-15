<?php
require_once '../config/database.php';
requireAdmin();

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $target_amount = floatval($_POST['target_amount']);
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $status = $_POST['status'];
            
            $stmt = $pdo->prepare("
                INSERT INTO donation_campaigns 
                (title, description, target_amount, start_date, end_date, status, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $title, $description, $target_amount, $start_date, $end_date, $status,
                $_SESSION['admin_id']
            ]);
            
            header('Location: donation_campaigns.php?msg=added');
            exit;
        }
        include 'views/campaigns/add.php';
        break;
        
    case 'edit':
        // Similar to add
        break;
        
    case 'list':
    default:
        $campaigns = $pdo->query("
            SELECT *, 
            ROUND((current_amount / target_amount * 100), 1) as progress 
            FROM donation_campaigns 
            ORDER BY status, created_at DESC
        ")->fetchAll();
        include 'views/campaigns/list.php';
        break;
}
?>