<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Validate required fields
    $required_fields = ['book_id', 'isbn', 'title', 'author', 'category_id', 'available_copies', 'total_copies'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Validate ISBN format
    $isbn = $_POST['isbn'];
    $isbn_pattern = '/^(?:\d{3}-\d{1,5}-\d{1,7}-\d{1,7}-\d{1}|\d{13}|\d{3}-\d{10}|\d{10}|\d{9}[0-9X])$/';
    if (!preg_match($isbn_pattern, $isbn)) {
        throw new Exception("Invalid ISBN format. Please use a standard format like 123-4-567-89012-3 or 9781234567897.");
    }

    // Validate copies
    if ($_POST['available_copies'] > $_POST['total_copies']) {
        throw new Exception('Available copies cannot be greater than total copies');
    }

    $db = new Database();
    $conn = $db->getConnection();

    // Start transaction
    $conn->beginTransaction();

    try {
        // Check if book exists
        $stmt = $conn->prepare("SELECT book_id FROM books WHERE book_id = :book_id");
        $stmt->execute(['book_id' => $_POST['book_id']]);
        
        if (!$stmt->fetch()) {
            throw new Exception('Book not found');
        }

        // Check if ISBN already exists for another book
        $stmt = $conn->prepare("SELECT book_id FROM books WHERE isbn = :isbn AND book_id != :book_id");
        $stmt->execute(['isbn' => $_POST['isbn'], 'book_id' => $_POST['book_id']]);
        if ($stmt->fetch()) {
            throw new Exception('This ISBN already exists for another book. Please use a different ISBN.');
        }

        // Update the book
        $stmt = $conn->prepare("
            UPDATE books 
            SET isbn = :isbn,
                title = :title,
                author = :author,
                category_id = :category_id,
                available_copies = :available_copies,
                total_copies = :total_copies
            WHERE book_id = :book_id
        ");

        $stmt->execute([
            'book_id' => $_POST['book_id'],
            'isbn' => $_POST['isbn'],
            'title' => $_POST['title'],
            'author' => $_POST['author'],
            'category_id' => $_POST['category_id'],
            'available_copies' => $_POST['available_copies'],
            'total_copies' => $_POST['total_copies']
        ]);

        // Get the updated book data with category name
        $stmt = $conn->prepare("
            SELECT b.*, c.category_name 
            FROM books b 
            LEFT JOIN categories c ON b.category_id = c.category_id 
            WHERE b.book_id = :book_id
        ");
        $stmt->execute(['book_id' => $_POST['book_id']]);
        $updatedBook = $stmt->fetch(PDO::FETCH_ASSOC);

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Book updated successfully',
            'data' => $updatedBook
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
