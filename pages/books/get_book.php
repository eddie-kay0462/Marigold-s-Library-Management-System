<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    if (!isset($_GET['book_id'])) {
        throw new Exception('Book ID is required');
    }

    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("
        SELECT b.*, c.category_name 
        FROM books b 
        LEFT JOIN categories c ON b.category_id = c.category_id 
        WHERE b.book_id = :book_id
    ");

    $stmt->execute(['book_id' => $_GET['book_id']]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        throw new Exception('Book not found');
    }

    echo json_encode([
        'success' => true,
        'data' => $book
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 