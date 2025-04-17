<?php
// Prevent any output before our JSON response
ob_start();

// Set error handling to not display errors, but log them
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

try {
    require_once '../../config/database.php';
    
    $db = new Database();
    $conn = $db->getConnection();

    // Join with categories table to get category name
    $stmt = $conn->prepare("
        SELECT b.*, c.category_name as category_name 
        FROM books b 
        LEFT JOIN categories c ON b.category_id = c.category_id 
        ORDER BY b.book_id ASC
    ");
    
    $stmt->execute();
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Clear any output buffers
    ob_clean();
    
    echo json_encode([
        'success' => true, 
        'data' => $books
    ]);
    
} catch (PDOException $e) {
    // Clear any output buffers
    ob_clean();
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Clear any output buffers
    ob_clean();
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'General error: ' . $e->getMessage()
    ]);
}

// End output buffer
ob_end_flush(); 