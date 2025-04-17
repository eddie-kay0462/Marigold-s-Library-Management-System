<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

// Initialize response array
$response = array(
    'status' => 'error',
    'message' => '',
    'data' => null
);

// Get the action from the request
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $database = new Database();
    $pdo = $database->getConnection();

    switch ($action) {
        case 'list':
            $stmt = $pdo->query("
                SELECT b.*, c.category_name 
                FROM books b 
                LEFT JOIN categories c ON b.category_id = c.category_id 
                ORDER BY b.book_id ASC
            ");
            $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['status'] = 'success';
            $response['data'] = $books;
            break;

        case 'get':
            $book_id = filter_input(INPUT_GET, 'book_id', FILTER_VALIDATE_INT);
            
            if (!$book_id) {
                throw new Exception('Invalid book ID');
            }

            $stmt = $pdo->prepare("
                SELECT b.*, c.category_name 
                FROM books b 
                LEFT JOIN categories c ON b.category_id = c.category_id 
                WHERE b.book_id = ?
            ");
            $stmt->execute([$book_id]);
            $book = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$book) {
                throw new Exception('Book not found');
            }

            $response['status'] = 'success';
            $response['data'] = $book;
            break;

        case 'edit':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validate required fields
                $required_fields = ['book_id', 'isbn', 'title', 'author', 'category_id', 'available_copies', 'total_copies'];
                foreach ($required_fields as $field) {
                    if (!isset($_POST[$field]) || empty($_POST[$field])) {
                        throw new Exception("$field is required");
                    }
                }

                // Validate copies
                if ($_POST['available_copies'] > $_POST['total_copies']) {
                    throw new Exception('Available copies cannot be greater than total copies');
                }

                // Update the book
                $stmt = $pdo->prepare("
                    UPDATE books 
                    SET isbn = ?,
                        title = ?,
                        author = ?,
                        category_id = ?,
                        available_copies = ?,
                        total_copies = ?
                    WHERE book_id = ?
                ");

                $stmt->execute([
                    $_POST['isbn'],
                    $_POST['title'],
                    $_POST['author'],
                    $_POST['category_id'],
                    $_POST['available_copies'],
                    $_POST['total_copies'],
                    $_POST['book_id']
                ]);

                $response['status'] = 'success';
                $response['message'] = 'Book updated successfully';
            }
            break;

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $book_id = filter_input(INPUT_POST, 'book_id', FILTER_VALIDATE_INT);

                if (!$book_id) {
                    throw new Exception('Invalid book ID');
                }

                // Check if book is currently borrowed
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as loan_count 
                    FROM loans 
                    WHERE book_id = ? 
                    AND return_date IS NULL
                ");
                $stmt->execute([$book_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result['loan_count'] > 0) {
                    throw new Exception('Cannot delete book: it is currently borrowed');
                }

                // Delete the book
                $stmt = $pdo->prepare("DELETE FROM books WHERE book_id = ?");
                $stmt->execute([$book_id]);

                $response['status'] = 'success';
                $response['message'] = 'Book deleted successfully';
            }
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

echo json_encode($response); 