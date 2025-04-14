<?php
require_once '../config/config.php';
require_once '../models/Book.php';
require_once '../config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('pages/login.php');
}

// Get all books
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['id'])) {
    $database = new Database();
    $db = $database->connect();
    
    $book = new Book($db);
    $result = $book->read();
    
    if ($result->rowCount() > 0) {
        $books_arr = array();
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $book_item = array(
                'id' => $row['book_id'],
                'isbn' => $row['isbn'],
                'title' => $row['title'],
                'author' => $row['author'],
                'category' => $row['category_name'],
                'available' => $row['available_copies']
            );
            
            array_push($books_arr, $book_item);
        }
        
        echo json_encode($books_arr);
    } else {
        echo json_encode(array('message' => 'No books found'));
    }
}

// Add new book
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->connect();
    
    $book = new Book($db);
    $book->isbn = $_POST['isbn'];
    $book->title = $_POST['title'];
    $book->author = $_POST['author'];
    $book->category_id = $_POST['category'];
    $book->available_copies = $_POST['copies'];
    $book->total_copies = $_POST['copies'];
    
    if ($book->create()) {
        echo json_encode(array('message' => 'Book added successfully'));
    } else {
        echo json_encode(array('message' => 'Failed to add book'));
    }
}