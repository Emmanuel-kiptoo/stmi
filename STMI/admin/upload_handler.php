<?php
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $uploadDir = '../uploads/';
    
    // Create uploads directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Create yearly and monthly subdirectories
    $yearDir = $uploadDir . date('Y') . '/';
    $monthDir = $yearDir . date('m') . '/';
    
    if (!file_exists($yearDir)) mkdir($yearDir, 0755, true);
    if (!file_exists($monthDir)) mkdir($monthDir, 0755, true);
    
    $file = $_FILES['file'];
    $fileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $file['name']);
    $filePath = $monthDir . $fileName;
    
    // Allowed file types
    $allowedTypes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf',
        'video/mp4', 'video/mpeg', 'video/quicktime'
    ];
    
    // Maximum file size (10MB)
    $maxSize = 10 * 1024 * 1024;
    
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['error' => 'File type not allowed.']);
        exit;
    }
    
    if ($file['size'] > $maxSize) {
        echo json_encode(['error' => 'File too large. Maximum size: 10MB']);
        exit;
    }
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Get relative path for database
        $relativePath = 'uploads/' . date('Y') . '/' . date('m') . '/' . $fileName;
        
        echo json_encode([
            'success' => true,
            'path' => $relativePath,
            'name' => $file['name'],
            'size' => $file['size'],
            'type' => $file['type']
        ]);
    } else {
        echo json_encode(['error' => 'Failed to upload file.']);
    }
} else {
    echo json_encode(['error' => 'No file uploaded.']);
}
?>