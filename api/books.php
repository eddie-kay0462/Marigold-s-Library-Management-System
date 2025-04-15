<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

require_once '../config/database.php';
require_once '../models/Book.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    
    $book = new Book($db);
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Get single book
                $result = $book->read_single($_GET['id']);
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'data' => $result
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Book not found'
                    ]);
                }
            } else {
                // Get all books
                $result = $book->read();
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'data' => $result
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'No books found'
                    ]);
                }
            }
            break;
            
        case 'POST':
            // Validate required fields
            $required_fields = ['isbn', 'title', 'author', 'category_id', 'total_copies', 'available_copies'];
            
            foreach ($required_fields as $field) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {
                    throw new Exception("Missing required field: {$field}");
                }
            }
            
            // Create book
            $book->isbn = $_POST['isbn'];
            $book->title = $_POST['title'];
            $book->author = $_POST['author'];
            $book->category_id = $_POST['category_id'];
            $book->total_copies = $_POST['total_copies'];
            $book->available_copies = $_POST['available_copies'];
            
            // Set optional fields to empty/null
            $book->publisher = '';
            $book->publication_year = null;
            $book->edition = '';
            
            if ($book->create()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Book created successfully'
                ]);
            } else {
                throw new Exception('Failed to create book');
            }
            break;
            
        case 'PUT':
            if (!isset($_GET['id'])) {
                throw new Exception("Book ID is required for update");
            }
            
            // Get the book ID from the URL
            $book_id = $_GET['id'];
            
            // Get PUT data from FormData
            $putData = [];
            if ($_SERVER["CONTENT_TYPE"] === "application/x-www-form-urlencoded") {
                parse_str(file_get_contents("php://input"), $putData);
            } else {
                // Handle multipart/form-data
                $putData = $_POST;
            }
            
            // Set book properties
            $book->book_id = $book_id;
            $book->isbn = $putData['isbn'] ?? null;
            $book->title = $putData['title'] ?? null;
            $book->author = $putData['author'] ?? null;
            $book->category_id = $putData['category_id'] ?? null;
            $book->total_copies = $putData['total_copies'] ?? null;
            $book->available_copies = $putData['available_copies'] ?? null;
            
            // Validate required fields
            $required_fields = ['isbn', 'title', 'author', 'category_id', 'total_copies', 'available_copies'];
            foreach ($required_fields as $field) {
                if (!isset($putData[$field]) || empty($putData[$field])) {
                    throw new Exception("Missing required field: {$field}");
                }
            }
            
            if ($book->update()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Book updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update book');
            }
            break;
            
        case 'DELETE':
            if (isset($_GET['id'])) {
                if ($book->delete($_GET['id'])) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Book deleted successfully'
                    ]);
                } else {
                    throw new Exception('Failed to delete book');
                }
            } else {
                throw new Exception('Book ID is required');
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>