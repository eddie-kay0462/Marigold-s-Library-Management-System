<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $categories
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error loading categories: ' . $e->getMessage()
    ]);
}
