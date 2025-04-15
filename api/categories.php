<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

require_once '../config/database.php';
require_once '../models/Category.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        error_log("Database connection failed");
        throw new Exception("Database connection failed");
    }
    
    $category = new Category($db);
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Get single category
                $result = $category->read_single($_GET['id']);
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'data' => $result
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Category not found'
                    ]);
                }
            } else {
                // Get all categories
                $result = $category->read();
                if ($result !== false && is_array($result)) {
                    echo json_encode([
                        'success' => true,
                        'data' => $result
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'No categories found'
                    ]);
                }
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    error_log("Categories API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 