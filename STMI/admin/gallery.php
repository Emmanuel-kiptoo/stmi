<?php
require_once '../config/database.php';
requireAdmin();

// Handle actions
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

switch ($action) {
    case 'add':
        include 'views/gallery/add.php';
        break;
        
    case 'edit':
        $stmt = $pdo->prepare("SELECT * FROM gallery_items WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        include 'views/gallery/edit.php';
        break;
        
    case 'delete':
        // Get item info for file deletion
        $stmt = $pdo->prepare("SELECT file_path, thumbnail_path FROM gallery_items WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        
        // Delete files
        if ($item) {
            if ($item['file_path'] && file_exists('../' . $item['file_path'])) {
                unlink('../' . $item['file_path']);
            }
            if ($item['thumbnail_path'] && file_exists('../' . $item['thumbnail_path'])) {
                unlink('../' . $item['thumbnail_path']);
            }
        }
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM gallery_items WHERE id = ?");
        $stmt->execute([$id]);
        
        header('Location: gallery.php?msg=deleted');
        exit;
        break;
        
    case 'list':
    default:
        // Get filter
        $type = $_GET['type'] ?? 'all';
        $category = $_GET['category'] ?? '';
        
        // Build query
        $sql = "SELECT gi.*, gc.name as category_name FROM gallery_items gi 
                LEFT JOIN gallery_categories gc ON gi.category_id = gc.id 
                WHERE 1=1";
        $params = [];
        
        if ($type === 'photos') {
            $sql .= " AND gi.media_type = 'photo'";
        } elseif ($type === 'videos') {
            $sql .= " AND gi.media_type = 'video'";
        }
        
        if (!empty($category) && is_numeric($category)) {
            $sql .= " AND gi.category_id = ?";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY gi.uploaded_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();
        
        // Get categories for filter
        $categories = $pdo->query("SELECT * FROM gallery_categories ORDER BY type, display_order")->fetchAll();
        
        include 'views/gallery/list.php';
        break;
}
?>