<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files'])) {
    $response = [
        'success' => true,
        'message' => '',
        'uploaded' => 0,
        'failed' => 0,
        'files' => []
    ];
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/avi', 'video/mov'];
    $max_size = 10 * 1024 * 1024; // 10MB
    
    foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['files']['error'][$key] === 0) {
            $file_name = $_FILES['files']['name'][$key];
            $file_size = $_FILES['files']['size'][$key];
            $file_type = $_FILES['files']['type'][$key];
            
            // Validate file
            if (!in_array($file_type, $allowed_types)) {
                $response['failed']++;
                $response['files'][] = ['name' => $file_name, 'status' => 'rejected', 'reason' => 'Invalid file type'];
                continue;
            }
            
            if ($file_size > $max_size) {
                $response['failed']++;
                $response['files'][] = ['name' => $file_name, 'status' => 'rejected', 'reason' => 'File too large'];
                continue;
            }
            
            // Determine media type
            $media_type = strpos($file_type, 'image/') === 0 ? 'photo' : 'video';
            $category = $media_type === 'photo' ? 1 : 4; // Default categories
            
            // Create upload directory
            $upload_dir = '../../uploads/gallery/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $unique_name = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file_name);
            $target_file = $upload_dir . $unique_name;
            
            // Move uploaded file
            if (move_uploaded_file($tmp_name, $target_file)) {
                // Insert into database
                $stmt = $pdo->prepare("
                    INSERT INTO gallery_items 
                    (title, file_path, media_type, category_id, uploaded_by)
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                $title = pathinfo($file_name, PATHINFO_FILENAME);
                $relative_path = 'uploads/gallery/' . $unique_name;
                
                $stmt->execute([
                    $title,
                    $relative_path,
                    $media_type,
                    $category,
                    $_SESSION['admin_id']
                ]);
                
                $response['uploaded']++;
                $response['files'][] = ['name' => $file_name, 'status' => 'uploaded', 'id' => $pdo->lastInsertId()];
            } else {
                $response['failed']++;
                $response['files'][] = ['name' => $file_name, 'status' => 'failed', 'reason' => 'Upload failed'];
            }
        }
    }
    
    $response['message'] = "Uploaded {$response['uploaded']} files, {$response['failed']} failed";
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

echo json_encode(['success' => false, 'message' => 'No files uploaded']);
?>