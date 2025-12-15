<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $category = $_GET['category'] ?? 'all';
        $limit = $_GET['limit'] ?? 10;
        
        $sql = "SELECT * FROM events WHERE status = 'published'";
        
        if ($category !== 'all') {
            $sql .= " AND category = ?";
            $params = [$category];
        } else {
            $params = [];
        }
        
        $sql .= " ORDER BY event_date DESC LIMIT ?";
        $params[] = (int)$limit;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $events = $stmt->fetchAll();
        
        // Format dates
        foreach ($events as &$event) {
            $event['formatted_date'] = date('d M Y', strtotime($event['event_date']));
        }
        
        echo json_encode([
            'success' => true,
            'data' => $events,
            'count' => count($events)
        ]);
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>