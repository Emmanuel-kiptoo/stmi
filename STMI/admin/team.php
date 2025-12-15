<?php
require_once '../config/database.php';
requireAdmin();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

switch ($action) {
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $position = trim($_POST['position']);
            $bio = trim($_POST['bio']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $social_links = json_encode([
                'linkedin' => trim($_POST['linkedin'] ?? ''),
                'twitter' => trim($_POST['twitter'] ?? ''),
                'facebook' => trim($_POST['facebook'] ?? '')
            ]);
            $display_order = intval($_POST['display_order']);
            $status = $_POST['status'];
            
            // Handle photo upload
            $photo = '';
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
                $upload_dir = '../uploads/team/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_name = time() . '_' . basename($_FILES['photo']['name']);
                $target_file = $upload_dir . $file_name;
                
                // Validate image
                $image_info = getimagesize($_FILES['photo']['tmp_name']);
                if ($image_info !== false) {
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                        $photo = 'uploads/team/' . $file_name;
                    }
                }
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO team_members 
                (name, position, bio, photo, email, phone, social_links, display_order, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $name, $position, $bio, $photo, $email, $phone,
                $social_links, $display_order, $status
            ]);
            
            header('Location: team.php?msg=added');
            exit;
        }
        include 'views/team/add.php';
        break;
        
    case 'edit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Similar to add with UPDATE
        }
        $stmt = $pdo->prepare("SELECT * FROM team_members WHERE id = ?");
        $stmt->execute([$id]);
        $member = $stmt->fetch();
        $member['social_links'] = json_decode($member['social_links'] ?? '{}', true);
        include 'views/team/edit.php';
        break;
        
    case 'delete':
        $stmt = $pdo->prepare("SELECT photo FROM team_members WHERE id = ?");
        $stmt->execute([$id]);
        $member = $stmt->fetch();
        
        // Delete photo file
        if ($member && $member['photo'] && file_exists('../' . $member['photo'])) {
            unlink('../' . $member['photo']);
        }
        
        $stmt = $pdo->prepare("DELETE FROM team_members WHERE id = ?");
        $stmt->execute([$id]);
        
        header('Location: team.php?msg=deleted');
        exit;
        break;
        
    default:
        $members = $pdo->query("SELECT * FROM team_members ORDER BY display_order, name")->fetchAll();
        include 'views/team/list.php';
        break;
}
?>