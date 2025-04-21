<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to add books.";
    header("Location: ../login.php");
    exit();
}

// Initialize database connection
$database = new Database();
$pdo = $database->getConnection();

// Fetch categories for dropdown
$categories = [];
try {
    $stmt = $pdo->query("SELECT  category_name FROM categories ORDER BY category_name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching categories: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isbn = trim($_POST['isbn'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $available_copies = (int)($_POST['available_copies'] ?? 0);

    // Validate input
    if (empty($isbn) || empty($title) || empty($author) || $available_copies < 0) {
        $_SESSION['error'] = "Please fill in all fields correctly";
        header("Location: add_book.php");
        exit();
    }

    try {
        // Check if ISBN already exists
        $stmt = $pdo->prepare("SELECT book_id FROM books WHERE isbn = :isbn");
        $stmt->execute(['isbn' => $isbn]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "A book with this ISBN already exists";
            header("Location: add_book.php");
            exit();
        }

        // Insert new book
        $stmt = $pdo->prepare("INSERT INTO books (isbn, title, author, category_name, available_copies) VALUES (:isbn, :title, :author, :category_name, :available_copies)");
        $stmt->execute([
            'isbn' => $isbn,
            'title' => $title,
            'author' => $author,
            'category_name' => $category_name,
            'available_copies' => $available_copies
        ]);

        $_SESSION['success'] = "Book added successfully!";
        header("Location: ../dashboard.php#books");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error adding book: " . $e->getMessage();
        header("Location: add_book.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Book - Marigold Library</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        .page-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
            width: 100%;
        }

        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .form-header h1 {
            color: #2C3E50;
            margin-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2C3E50;
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .success-message {
            color: #198754;
            background-color: #d1e7dd;
            border: 1px solid #badbcc;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <main class="main-content">
            <div class="form-container">
                <div class="form-header">
                    <h1>Add New Book</h1>
                    <p>Fill in the details to add a new book to the library</p>
                </div>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="error-message">
                        <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['success'])): ?>
                    <div class="success-message">
                        <?php 
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <form action="add_book_handler.php" method="POST">
                    <div class="form-group">
                        <label for="isbn">ISBN</label>
                        <input type="text" id="isbn" name="isbn" pattern="(?:\d{3}-\d{1,5}-\d{1,7}-\d{1,7}-\d{1}|\d{13}|\d{3}-\d{10}|\d{10}|\d{9}[0-9X])" placeholder="e.g., 978-3-16-148410-0" required>
                        <small class="form-text text-muted">Enter a valid ISBN format (e.g., 978-3-16-148410-0, 9781234567897, or 0-306-40615-2)</small>
                    </div>
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="author">Author</label>
                        <input type="text" id="author" name="author" required>
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php
                            $db = new Database();
                            $conn = $db->getConnection();
                            $stmt = $conn->query("SELECT * FROM categories");
                            while($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='{$category['category_id']}'>{$category['category_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="available_copies">Available Copies</label>
                        <input type="number" id="available_copies" name="available_copies" min="0" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Book
                        </button>
                        <a href="../dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
