<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => ''];
    
    try {
        if ($action === 'add') {
            // Add new campaign
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $target_amount = floatval($_POST['target_amount']);
            $current_amount = floatval($_POST['current_amount'] ?? 0);
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $status = $_POST['status'];
            $campaign_type = $_POST['campaign_type'] ?? 'general';
            $impact_statement = trim($_POST['impact_statement'] ?? '');
            
            // Handle file upload
            $featured_image = '';
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
                $upload_dir = '../../uploads/campaigns/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_name = time() . '_' . basename($_FILES['featured_image']['name']);
                $target_file = $upload_dir . $file_name;
                
                // Validate image
                $image_info = getimagesize($_FILES['featured_image']['tmp_name']);
                if ($image_info !== false && $_FILES['featured_image']['size'] <= 2 * 1024 * 1024) {
                    if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
                        $featured_image = 'uploads/campaigns/' . $file_name;
                    }
                }
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO donation_campaigns 
                (title, description, target_amount, current_amount, start_date, end_date, 
                 status, featured_image, campaign_type, impact_statement, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $title, $description, $target_amount, $current_amount, $start_date, $end_date,
                $status, $featured_image, $campaign_type, $impact_statement, $_SESSION['admin_id']
            ]);
            
            $campaign_id = $pdo->lastInsertId();
            
            // Save goals if any
            if (isset($_POST['goals']) && is_array($_POST['goals'])) {
                foreach ($_POST['goals'] as $goal) {
                    $goal = trim($goal);
                    if (!empty($goal)) {
                        $stmt = $pdo->prepare("
                            INSERT INTO campaign_goals (campaign_id, goal_text)
                            VALUES (?, ?)
                        ");
                        $stmt->execute([$campaign_id, $goal]);
                    }
                }
            }
            
            $response['success'] = true;
            $response['message'] = 'Campaign created successfully.';
            $response['campaign_id'] = $campaign_id;
            
        } elseif ($action === 'edit') {
            // Edit existing campaign
            $campaign_id = $_POST['campaign_id'];
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $target_amount = floatval($_POST['target_amount']);
            $current_amount = floatval($_POST['current_amount']);
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $status = $_POST['status'];
            $campaign_type = $_POST['campaign_type'] ?? 'general';
            $impact_statement = trim($_POST['impact_statement'] ?? '');
            $remove_image = isset($_POST['remove_image']) && $_POST['remove_image'] == '1';
            
            // Get current campaign for image
            $stmt = $pdo->prepare("SELECT featured_image FROM donation_campaigns WHERE id = ?");
            $stmt->execute([$campaign_id]);
            $current_campaign = $stmt->fetch();
            
            $featured_image = $current_campaign['featured_image'];
            
            // Handle file upload or removal
            if ($remove_image && $featured_image && file_exists('../../' . $featured_image)) {
                unlink('../../' . $featured_image);
                $featured_image = '';
            }
            
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
                // Remove old image if exists
                if ($featured_image && file_exists('../../' . $featured_image)) {
                    unlink('../../' . $featured_image);
                }
                
                $upload_dir = '../../uploads/campaigns/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_name = time() . '_' . basename($_FILES['featured_image']['name']);
                $target_file = $upload_dir . $file_name;
                
                // Validate image
                $image_info = getimagesize($_FILES['featured_image']['tmp_name']);
                if ($image_info !== false && $_FILES['featured_image']['size'] <= 2 * 1024 * 1024) {
                    if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
                        $featured_image = 'uploads/campaigns/' . $file_name;
                    }
                }
            }
            
            $stmt = $pdo->prepare("
                UPDATE donation_campaigns 
                SET title = ?, description = ?, target_amount = ?, current_amount = ?, 
                    start_date = ?, end_date = ?, status = ?, featured_image = ?, 
                    campaign_type = ?, impact_statement = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $title, $description, $target_amount, $current_amount, $start_date, $end_date,
                $status, $featured_image, $campaign_type, $impact_statement, $campaign_id
            ]);
            
            $response['success'] = true;
            $response['message'] = 'Campaign updated successfully.';
            
        } else {
            throw new Exception('Invalid action.');
        }
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    // If it's an AJAX request, return JSON
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        // If it's a regular form submission, redirect
        if ($response['success']) {
            header('Location: ../donation_campaigns.php?msg=' . ($action === 'add' ? 'added' : 'updated'));
        } else {
            header('Location: ../donation_campaigns.php?error=' . urlencode($response['message']));
        }
    }
    exit;
}

// If not POST, redirect
header('Location: ../donation_campaigns.php');
exit;
?>