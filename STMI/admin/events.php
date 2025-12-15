<?php
require_once '../config/database.php';
requireAdmin();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

switch ($action) {
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = sanitize($_POST['title']);
            $description = sanitize($_POST['description']);
            $event_date = $_POST['event_date'];
            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];
            $location = sanitize($_POST['location']);
            $category = $_POST['category'];
            $status = $_POST['status'];
            $registration_link = sanitize($_POST['registration_link']);
            
            // Handle file upload
            $featured_image = '';
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
                $upload_dir = '../uploads/events/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_name = time() . '_' . basename($_FILES['featured_image']['name']);
                $target_file = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
                    $featured_image = 'uploads/events/' . $file_name;
                }
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO events 
                (title, description, event_date, start_time, end_time, location, category, status, featured_image, registration_link, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $title, $description, $event_date, $start_time, $end_time,
                $location, $category, $status, $featured_image, $registration_link,
                $_SESSION['admin_id']
            ]);
            
            header('Location: events.php?msg=added');
            exit;
        }
        include 'views/events/add.php';
        break;
        
    case 'edit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Similar to add but with UPDATE
        }
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);
        $event = $stmt->fetch();
        include 'views/events/edit.php';
        break;
        
    case 'delete':
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: events.php?msg=deleted');
        exit;
        break;
        
    default:
        // List events
        $filter = $_GET['filter'] ?? 'all';
        $sql = "SELECT * FROM events";
        
        if ($filter === 'upcoming') {
            $sql .= " WHERE category = 'upcoming' AND event_date >= CURDATE()";
        } elseif ($filter === 'past') {
            $sql .= " WHERE category = 'past' OR event_date < CURDATE()";
        } elseif ($filter === 'ongoing') {
            $sql .= " WHERE category = 'ongoing'";
        }
        
        $sql .= " ORDER BY event_date DESC";
        $stmt = $pdo->query($sql);
        $events = $stmt->fetchAll();
        include 'views/events/list.php';
        break;
}
?>