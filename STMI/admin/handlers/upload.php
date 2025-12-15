<?php
require_once '../../config/database.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = [
        'success' => false,
        'message' => '',
        'file_path' => ''
    ];
    
    if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'video/mp4'];
        $max_size = 10 * 1024 * 1024; // 10MB
        
        if (!in_array($_FILES['file']['type'], $allowed_types)) {
            $response['message'] = 'File type not allowed';
        } elseif ($_FILES['file']['size'] > $max_size) {
            $response['message'] = 'File too large (max 10MB)';
        } else {
            $upload_dir = '../../uploads/';
            $category = $_POST['category'] ?? 'general';
            $category_dir = $upload_dir . $category . '/';
            
            if (!file_exists($category_dir)) {
                mkdir($category_dir, 0755, true);
            }
            
            $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['file']['name']);
            $target_file = $category_dir . $file_name;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
                // Save to database
                $relative_path = 'uploads/' . $category . '/' . $file_name;
                
                $stmt = $pdo->prepare("
                    INSERT INTO media_items 
                    (title, description, file_path, file_type, category, file_size, uploaded_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $_POST['title'] ?? $file_name,
                    $_POST['description'] ?? '',
                    $relative_path,
                    explode('/', $_FILES['file']['type'])[0],
                    $category,
                    $_FILES['file']['size'],
                    $_SESSION['admin_id']
                ]);
                
                $response['success'] = true;
                $response['message'] = 'File uploaded successfully';
                $response['file_path'] = $relative_path;
                $response['file_id'] = $pdo->lastInsertId();
            } else {
                $response['message'] = 'Failed to upload file';
            }
        }
    } else {
        $response['message'] = 'No file uploaded or upload error';
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>