<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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

            // First, check if the students table exists
            $tableCheck = $pdo->query("SHOW TABLES LIKE 'students'");
            if ($tableCheck->rowCount() == 0) {
                throw new Exception('Students table does not exist');
            }

            // Check table structure
            $columns = $pdo->query("SHOW COLUMNS FROM students");
            $requiredColumns = ['student_id', 'first_name', 'last_name', 'student_number'];
            $existingColumns = [];
            while ($column = $columns->fetch(PDO::FETCH_ASSOC)) {
                $existingColumns[] = $column['Field'];
            }
            
            $missingColumns = array_diff($requiredColumns, $existingColumns);
            if (!empty($missingColumns)) {
                throw new Exception('Students table is missing required columns: ' . implode(', ', $missingColumns));
            }

            // Perform the search
            $stmt = $pdo->prepare("
                SELECT 
                    student_id, 
                    CONCAT(first_name, ' ', last_name) as full_name, 
                    student_number 
                FROM students 
                WHERE 
                    CONCAT(first_name, ' ', last_name) LIKE :query 
                    OR student_number LIKE :query 
                LIMIT 5
            ");
            
            $searchQuery = "%$query%";
            $stmt->bindParam(':query', $searchQuery, PDO::PARAM_STR);
            $stmt->execute();
            
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($students)) {
                $response['status'] = 'success';
                $response['data'] = [];
                $response['message'] = 'No students found';
            } else {
                $response['status'] = 'success';
                $response['data'] = $students;
            }
            break;

        case 'search_books':
            $query = isset($_GET['query']) ? $_GET['query'] : '';
            if (empty($query)) {
                throw new Exception('Search query is required');
            }

            $stmt = $pdo->prepare("
                SELECT book_id, title, isbn, total_copies, available_copies 
                FROM books 
                WHERE (title LIKE :query OR isbn LIKE :query)
                AND available_copies > 0 
                LIMIT 5
            ");
            $stmt->execute(['query' => "%$query%"]);
            $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['status'] = 'success';
            $response['data'] = $books;
            break;

        case 'borrow':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $student_id = $_POST['student_id'] ?? '';
            $book_id = $_POST['book_id'] ?? '';
            $due_date = $_POST['due_date'] ?? '';

            if (empty($student_id) || empty($book_id) || empty($due_date)) {
                throw new Exception('Student, book, and due date are required');
            }

            // Start transaction
            $pdo->beginTransaction();

            try {
                // Get student number
                $stmt = $pdo->prepare("SELECT student_number FROM students WHERE student_id = ?");
                $stmt->execute([$student_id]);
                $student = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$student) {
                    throw new Exception('Student not found');
                }

                // Check if book is available
                $stmt = $pdo->prepare("
                    SELECT title, available_copies, total_copies 
                    FROM books 
                    WHERE book_id = ? 
                    AND available_copies > 0
                    FOR UPDATE
                ");
                $stmt->execute([$book_id]);
                $book = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$book) {
                    throw new Exception('Book is not available for borrowing');
                }

                // Insert into active_loans
                $stmt = $pdo->prepare("
                    INSERT INTO active_loans (student_id, book_id, student_number, loan_date, due_date, status) 
                    VALUES (?, ?, ?, CURRENT_DATE(), ?, 'Active')
                ");
                $stmt->execute([$student_id, $book_id, $student['student_number'], $due_date]);
                $loan_id = $pdo->lastInsertId();

                // Insert into loan_history
                $stmt = $pdo->prepare("
                    INSERT INTO loan_history (loan_id, student_id, book_id, student_number, loan_date, due_date, status) 
                    VALUES (?, ?, ?, ?, CURRENT_DATE(), ?, 'Active')
                ");
                $stmt->execute([$loan_id, $student_id, $book_id, $student['student_number'], $due_date]);

                // Decrement the available_copies count by 1
                $stmt = $pdo->prepare("
                    UPDATE books 
                    SET available_copies = available_copies - 1
                    WHERE book_id = ? 
                    AND available_copies > 0
                ");
                $stmt->execute([$book_id]);

                // Commit transaction
                $pdo->commit();

                $response['status'] = 'success';
                $response['message'] = 'Book borrowed successfully';
                $response['data'] = array(
                    'book_id' => $book_id
                );
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'list_active':
            $stmt = $pdo->prepare("
                SELECT 
                    al.loan_id,
                    al.student_id,
                    al.book_id,
                    DATE_FORMAT(al.loan_date, '%Y-%m-%d') as loan_date,
                    DATE_FORMAT(al.due_date, '%Y-%m-%d') as due_date,
                    CONCAT(s.first_name, ' ', s.last_name) as student_name,
                    b.title as book_title,
                    CASE 
                        WHEN al.due_date < CURDATE() THEN 'Overdue'
                        WHEN al.due_date = CURDATE() THEN 'Due Today'
                        ELSE 'Active'
                    END as status,
                    DATEDIFF(al.due_date, CURDATE()) as days_remaining
                FROM active_loans al
                JOIN students s ON al.student_id = s.student_id
                JOIN books b ON al.book_id = b.book_id
                WHERE al.status = 'Active'
                AND al.returned_date IS NULL
                ORDER BY al.due_date ASC
            ");
            $stmt->execute();
            $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response['status'] = 'success';
            $response['data'] = array(
                'loans' => $loans
            );
            break;

        case 'return':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $loan_id = $_POST['loan_id'] ?? '';
            if (empty($loan_id)) {
                throw new Exception('Loan ID is required');
            }

            // Start transaction
            $pdo->beginTransaction();

            try {
                // Get loan details and verify it's an active loan
                $stmt = $pdo->prepare("
                    SELECT al.loan_id, al.book_id, b.title as book_title, b.available_copies
                    FROM active_loans al
                    JOIN books b ON al.book_id = b.book_id
                    WHERE al.loan_id = ? 
                    AND al.status = 'Active'
                    AND al.returned_date IS NULL
                    FOR UPDATE
                ");
                $stmt->execute([$loan_id]);
                $loan = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$loan) {
                    throw new Exception('Active loan not found');
                }

                // Update loan_history with return date
                $stmt = $pdo->prepare("
                    UPDATE loan_history 
                    SET returned_date = CURRENT_DATE(),
                        status = 'Returned'
                    WHERE loan_id = ?
                ");
                $stmt->execute([$loan_id]);

                // Update active_loans status and return date
                $stmt = $pdo->prepare("
                    UPDATE active_loans 
                    SET returned_date = CURRENT_DATE(),
                        status = 'Returned'
                    WHERE loan_id = ?
                ");
                $stmt->execute([$loan_id]);

                // Increment the available_copies count by 1
                $stmt = $pdo->prepare("
                    UPDATE books 
                    SET available_copies = available_copies + 1
                    WHERE book_id = ? 
                    AND available_copies < total_copies
                ");
                $stmt->execute([$loan['book_id']]);

                // Commit transaction
                $pdo->commit();

                $response['status'] = 'success';
                $response['message'] = 'Book returned successfully';
            } catch (Exception $e) {
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