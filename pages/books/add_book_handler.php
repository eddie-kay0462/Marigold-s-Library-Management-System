<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to add books.";
    header("Location: ../login.php");
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = new Database();
        $conn = $db->getConnection();

        // Get form data
        $isbn = $_POST['isbn'] ?? '';
        $title = $_POST['title'] ?? '';
        $author = $_POST['author'] ?? '';
        $category_id = $_POST['category_id'] ?? '';
        $available_copies = $_POST['available_copies'] ?? 0;

        // Validate required fields
        if (empty($isbn) || empty($title) || empty($author) || empty($category_id)) {
            throw new Exception("Please fill in all required fields.");
        }

        // Validate ISBN format using regex
        $isbn_pattern = '/^(?:\d{3}-\d{1,5}-\d{1,7}-\d{1,7}-\d{1}|\d{13}|\d{3}-\d{10}|\d{10}|\d{9}[0-9X])$/';
        if (!preg_match($isbn_pattern, $isbn)) {
            throw new Exception("Invalid ISBN format. Please use a standard format like 123-4-567-89012-3 or 9781234567897.");
        }

        // Check if ISBN already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM books WHERE isbn = :isbn");
        $stmt->execute(['isbn' => $isbn]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("This ISBN already exists in the system. Please use a different ISBN.");
        }

        // Insert new book
        $stmt = $conn->prepare("
            INSERT INTO books (isbn, title, author, category_id, available_copies, total_copies)
            VALUES (:isbn, :title, :author, :category_id, :available_copies, :total_copies)
        ");

        $stmt->execute([
            'isbn' => $isbn,
            'title' => $title,
            'author' => $author,
            'category_id' => $category_id,
            'available_copies' => $available_copies,
            'total_copies' => $available_copies
        ]);

        $_SESSION['success'] = "Book added successfully!";
        header("Location: ../dashboard.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: add_book.php");
        exit();
    }
} else {
    header("Location: add_book.php");
    exit();
}
