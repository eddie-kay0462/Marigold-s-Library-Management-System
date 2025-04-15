<?php
header('Content-Type: application/json');
require_once '../config/config.php';
require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->connect();
    
    // Get recent activities (modify this query according to your database structure)
    $query = "SELECT * FROM activities ORDER BY created_at DESC LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $activities = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $activities[] = [
            'description' => $row['description'],
            'time' => date('M d, Y H:i', strtotime($row['created_at']))
        ];
    }
    
    echo json_encode([
        'success' => true,
        'activities' => $activities
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error loading activities'
    ]);
}
?> 