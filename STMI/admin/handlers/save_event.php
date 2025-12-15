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
            // Add new event
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $event_date = $_POST['event_date'];
            $start_time = $_POST['start_time'] ?? null;
            $end_time = $_POST['end_time'] ?? null;
            $location = trim($_POST['location']);
            $category = $_POST['category'];
            $status = $_POST['status'];
            $registration_link = trim($_POST['registration_link'] ?? '');
            $additional_info = trim($_POST['additional_info'] ?? '');
            $tags = trim($_POST['tags'] ?? '');
            
            // Handle file upload
            $featured_image = '';
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
                $upload_dir = '../../uploads/events/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_name = time() . '_' . basename($_FILES['featured_image']['name']);
                $target_file = $upload_dir . $file_name;
                
                // Validate image
                $image_info = getimagesize($_FILES['featured_image']['tmp_name']);
                if ($image_info !== false && $_FILES['featured_image']['size'] <= 2 * 1024 * 1024) {
                    if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
                        $featured_image = 'uploads/events/' . $file_name;
                    }
                }
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO events 
                (title, description, event_date, start_time, end_time, location, 
                 category, status, featured_image, registration_link, 
                 additional_info, tags, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $title, $description, $event_date, $start_time, $end_time,
                $location, $category, $status, $featured_image, $registration_link,
                $additional_info, $tags, $_SESSION['admin_id']
            ]);
            
            $response['success'] = true;
            $response['message'] = 'Event added successfully.';
            $response['event_id'] = $pdo->lastInsertId();
            
        } elseif ($action === 'edit') {
            // Edit existing event
            $event_id = $_POST['event_id'];
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $event_date = $_POST['event_date'];
            $start_time = $_POST['start_time'] ?? null;
            $end_time = $_POST['end_time'] ?? null;
            $location = trim($_POST['location']);
            $category = $_POST['category'];
            $status = $_POST['status'];
            $registration_link = trim($_POST['registration_link'] ?? '');
            $additional_info = trim($_POST['additional_info'] ?? '');
            $tags = trim($_POST['tags'] ?? '');
            $remove_image = isset($_POST['remove_image']) && $_POST['remove_image'] == '1';
            
            // Get current event for image
            $stmt = $pdo->prepare("SELECT featured_image FROM events WHERE id = ?");
            $stmt->execute([$event_id]);
            $current_event = $stmt->fetch();
            
            $featured_image = $current_event['featured_image'];
            
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
                
                $upload_dir = '../../uploads/events/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_name = time() . '_' . basename($_FILES['featured_image']['name']);
                $target_file = $upload_dir . $file_name;
                
                // Validate image
                $image_info = getimagesize($_FILES['featured_image']['tmp_name']);
                if ($image_info !== false && $_FILES['featured_image']['size'] <= 2 * 1024 * 1024) {
                    if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
                        $featured_image = 'uploads/events/' . $file_name;
                    }
                }
            }
            
            $stmt = $pdo->prepare("
                UPDATE events 
                SET title = ?, description = ?, event_date = ?, start_time = ?, end_time = ?, 
                    location = ?, category = ?, status = ?, featured_image = ?, 
                    registration_link = ?, additional_info = ?, tags = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $title, $description, $event_date, $start_time, $end_time,
                $location, $category, $status, $featured_image, $registration_link,
                $additional_info, $tags, $event_id
            ]);
            
            $response['success'] = true;
            $response['message'] = 'Event updated successfully.';
            
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
            header('Location: ../events.php?msg=' . ($action === 'add' ? 'added' : 'updated'));
        } else {
            header('Location: ../events.php?error=' . urlencode($response['message']));
        }
    }
    exit;
}

// If not POST, redirect
header('Location: ../events.php');
exit;
?>