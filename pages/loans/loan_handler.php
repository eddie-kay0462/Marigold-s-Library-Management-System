<?php
session_start();
require_once '../../config/database.php';

// Set header to JSON
header('Content-Type: application/json');

// Initialize response array
$response = array(
    'status' => 'error',
    'message' => '',
    'data' => null
);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Please login to continue';
    echo json_encode($response);
    exit();
}

// Get the action from the request
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

try {
    // Initialize database connection
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        throw new Exception('Failed to connect to database');
    }

    switch ($action) {
        case 'search_students':
            $query = isset($_GET['query']) ? $_GET['query'] : '';
            if (empty($query)) {
                throw new Exception('Search query is required');
            }

            $stmt = $pdo->prepare("
                SELECT student_id, student_number, first_name, last_name, email 
                FROM students 
                WHERE student_number LIKE :query 
                OR first_name LIKE :query 
                OR last_name LIKE :query
                OR CONCAT(first_name, ' ', last_name) LIKE :query
                ORDER BY first_name, last_name
                LIMIT 10
            ");
            
            $searchQuery = "%$query%";
            $stmt->bindParam(':query', $searchQuery, PDO::PARAM_STR);
            $stmt->execute();
            
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response['status'] = 'success';
            $response['data'] = $students;
            break;

        case 'search_books':
            $query = isset($_GET['query']) ? $_GET['query'] : '';
            if (empty($query)) {
                throw new Exception('Search query is required');
            }

            $stmt = $pdo->prepare("
                SELECT b.book_id, b.isbn, b.title, b.author, b.available_copies, b.total_copies, c.category_name
                FROM books b
                LEFT JOIN categories c ON b.category_id = c.category_id
                WHERE b.isbn LIKE :query 
                OR b.title LIKE :query 
                OR b.author LIKE :query
                ORDER BY b.title
                LIMIT 10
            ");
            
            $searchQuery = "%$query%";
            $stmt->bindParam(':query', $searchQuery, PDO::PARAM_STR);
            $stmt->execute();
            
            $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response['status'] = 'success';
            $response['data'] = $books;
            break;

        case 'create_loan':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
            $book_id = filter_input(INPUT_POST, 'book_id', FILTER_VALIDATE_INT);
            $due_date = filter_input(INPUT_POST, 'due_date', FILTER_SANITIZE_STRING);

            if (!$student_id || !$book_id || !$due_date) {
                throw new Exception('All fields are required');
            }

            // Validate student exists
            $stmt = $pdo->prepare("SELECT student_id, first_name, last_name FROM students WHERE student_id = ?");
            $stmt->execute([$student_id]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$student) {
                throw new Exception('Student not found');
            }

            // Validate book exists and has available copies
            $stmt = $pdo->prepare("SELECT book_id, title, available_copies FROM books WHERE book_id = ?");
            $stmt->execute([$book_id]);
            $book = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$book) {
                throw new Exception('Book not found');
            }
            if ($book['available_copies'] <= 0) {
                throw new Exception('No available copies of this book');
            }

            // Start transaction
            $pdo->beginTransaction();

            try {
                // Insert new loan
                $loan_date = date('Y-m-d'); // Today's date
                $stmt = $pdo->prepare("
                    INSERT INTO active_loans (student_id, book_id, title, loan_date, due_date, status, student_number)
                    SELECT ?, ?, ?, ?, ?, 'Active', s.student_number
                    FROM students s
                    WHERE s.student_id = ?
                ");
                $stmt->execute([$student_id, $book_id, $book['title'], $loan_date, $due_date, $student_id]);
                $loan_id = $pdo->lastInsertId();

                // Update book available copies
                $stmt = $pdo->prepare("
                    UPDATE books 
                    SET available_copies = available_copies - 1
                    WHERE book_id = ?
                ");
                $stmt->execute([$book_id]);

                // Commit transaction
                $pdo->commit();

                $response['status'] = 'success';
                $response['message'] = "Book '{$book['title']}' has been loaned to {$student['first_name']} {$student['last_name']}";
                $response['data'] = ['loan_id' => $loan_id];
            } catch (Exception $e) {
                // Rollback transaction on error
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'get_active_loans':
            $stmt = $pdo->query("
                SELECT 
                    l.loan_id, 
                    l.book_id, 
                    b.title,  /* Get title from books table */
                    l.loan_date, 
                    l.due_date, 
                    l.status, 
                    s.student_id, 
                    s.student_number, 
                    s.first_name, 
                    s.last_name
                FROM active_loans l
                JOIN students s ON l.student_id = s.student_id
                JOIN books b ON l.book_id = b.book_id  /* Add JOIN with books table */
                WHERE l.status = 'Active' OR l.status = 'Overdue'
                ORDER BY l.due_date ASC
            ");
            $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Update status for overdue loans
            $today = date('Y-m-d');
            foreach ($loans as &$loan) {
                if ($loan['due_date'] < $today && $loan['status'] !== 'Returned') {
                    $loan['status'] = 'Overdue';
                    
                    // Update status in database if needed
                    if ($loan['status'] !== 'Overdue') {
                        $updateStmt = $pdo->prepare("
                            UPDATE active_loans 
                            SET status = 'Overdue'
                            WHERE loan_id = ?
                        ");
                        $updateStmt->execute([$loan['loan_id']]);
                    }
                }
            }

            $response['status'] = 'success';
            $response['data'] = $loans;
            break;

        case 'return_book':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $loan_id = filter_input(INPUT_POST, 'loan_id', FILTER_VALIDATE_INT);
            if (!$loan_id) {
                throw new Exception('Loan ID is required');
            }

            // Start transaction
            $pdo->beginTransaction();

            try {
                // Get loan details
                $stmt = $pdo->prepare("
                    SELECT l.loan_id, l.book_id, l.student_id, l.loan_date, l.due_date, l.status,
                           b.title, s.first_name, s.last_name
                    FROM active_loans l
                    JOIN books b ON l.book_id = b.book_id
                    JOIN students s ON l.student_id = s.student_id
                    WHERE l.loan_id = ?
                ");
                $stmt->execute([$loan_id]);
                $loan = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$loan) {
                    throw new Exception('Loan not found');
                }

                if ($loan['status'] === 'Returned') {
                    throw new Exception('This book has already been returned');
                }

                // Update loan status
                $returned_date = date('Y-m-d'); // Today's date
                $stmt = $pdo->prepare("
                    UPDATE active_loans 
                    SET status = 'Returned', returned_date = ?
                    WHERE loan_id = ?
                ");
                $stmt->execute([$returned_date, $loan_id]);

                // Update book available copies
                $stmt = $pdo->prepare("
                    UPDATE books 
                    SET available_copies = available_copies + 1
                    WHERE book_id = ?
                ");
                $stmt->execute([$loan['book_id']]);

                // Determine if the book was returned on time or late
                $return_status = ($returned_date <= $loan['due_date']) ? 'On Time' : 'Late';

                // Add to loan history
                $stmt = $pdo->prepare("
                    INSERT INTO loan_history (
                        loan_id, student_id, student_number, book_id, 
                        loan_date, due_date, returned_date, status
                    )
                    SELECT 
                        l.loan_id, l.student_id, 
                        CASE 
                            WHEN s.student_number IS NULL OR TRIM(s.student_number) = '' 
                            THEN CONCAT('TEMP-', l.student_id, '-', UNIX_TIMESTAMP()) 
                            ELSE s.student_number 
                        END as student_number,
                        l.book_id,
                        l.loan_date, l.due_date, ?, ?
                    FROM active_loans l
                    JOIN students s ON l.student_id = s.student_id
                    WHERE l.loan_id = ?
                ");
                $stmt->execute([$returned_date, $return_status, $loan_id]);

                // Commit transaction
                $pdo->commit();

                $response['status'] = 'success';
                $response['message'] = "Book '{$loan['title']}' has been returned by {$loan['first_name']} {$loan['last_name']}";
            } catch (Exception $e) {
                // Rollback transaction on error
                $pdo->rollBack();
                throw $e;
            }
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    error_log('Database error in loan_handler.php: ' . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Error in loan_handler.php: ' . $e->getMessage());
}

// Send JSON response
echo json_encode($response);
exit();
