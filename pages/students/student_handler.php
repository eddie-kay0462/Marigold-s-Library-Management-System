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
        case 'add':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validate and sanitize input
                $student_number = filter_input(INPUT_POST, 'student_number', FILTER_SANITIZE_STRING);
                $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
                $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
                $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
                $date_of_birth = filter_input(INPUT_POST, 'date_of_birth', FILTER_SANITIZE_STRING);
                $registration_date = filter_input(INPUT_POST, 'registration_date', FILTER_SANITIZE_STRING);

                // Validate required fields
                if (!$student_number || trim($student_number) === '' || !$first_name || !$last_name || !$email || !$date_of_birth || !$registration_date) {
                    throw new Exception('All fields are required. Student number cannot be empty.');
                }

                // Validate student number format (only allow alphanumeric characters and hyphens)
                if (!preg_match('/^[A-Za-z0-9-]+$/', trim($student_number))) {
                    throw new Exception('Student number can only contain letters, numbers, and hyphens');
                }

                // Check if student number already exists
                $stmt = $pdo->prepare("SELECT student_id FROM students WHERE student_number = ?");
                $stmt->execute([$student_number]);
                if ($stmt->fetch()) {
                    throw new Exception('Student number already exists');
                }

                // Check if email already exists
                $stmt = $pdo->prepare("SELECT student_id FROM students WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    throw new Exception('Email already exists');
                }

                // Insert new student
                $stmt = $pdo->prepare("INSERT INTO students (student_number, first_name, last_name, email, date_of_birth, registration_date) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$student_number, $first_name, $last_name, $email, $date_of_birth, $registration_date]);

                $response['status'] = 'success';
                $response['message'] = 'Student added successfully';
                $response['data'] = ['student_id' => $pdo->lastInsertId()];
            }
            break;

        case 'edit':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
                $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
                $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
                $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
                $date_of_birth = filter_input(INPUT_POST, 'date_of_birth', FILTER_SANITIZE_STRING);

                if (!$student_id || !$first_name || !$last_name || !$email || !$date_of_birth) {
                    throw new Exception('All fields are required');
                }

                // Check if email exists for other students
                $stmt = $pdo->prepare("SELECT student_id FROM students WHERE email = ? AND student_id != ?");
                $stmt->execute([$email, $student_id]);
                if ($stmt->fetch()) {
                    throw new Exception('Email already exists');
                }

                $stmt = $pdo->prepare("UPDATE students SET first_name = ?, last_name = ?, email = ?, date_of_birth = ? WHERE student_id = ?");
                $stmt->execute([$first_name, $last_name, $email, $date_of_birth, $student_id]);

                $response['status'] = 'success';
                $response['message'] = 'Student updated successfully';
            }
            break;

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);

                if (!$student_id) {
                    throw new Exception('Invalid student ID');
                }

                // Check if student has any active (non-returned) loans
                $stmt = $pdo->prepare("SELECT COUNT(*) as loan_count FROM active_loans WHERE student_id = ? AND status != 'Returned'");
                $stmt->execute([$student_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result['loan_count'] > 0) {
                    throw new Exception('Cannot delete student: This student has active loans. All books must be returned before deletion.');
                }

                // Start a transaction
                $pdo->beginTransaction();
                
                try {
                    // First, delete all loan history records for this student
                    $stmt = $pdo->prepare("DELETE FROM loan_history WHERE student_id = ?");
                    $stmt->execute([$student_id]);
                    
                    // Then, delete all returned loans from active_loans table
                    $stmt = $pdo->prepare("DELETE FROM active_loans WHERE student_id = ? AND status = 'Returned'");
                    $stmt->execute([$student_id]);
                    
                    // Finally, delete the student
                    $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
                    $stmt->execute([$student_id]);
                    
                    // Commit the transaction
                    $pdo->commit();
                    
                    $response['status'] = 'success';
                    $response['message'] = 'Student deleted successfully';
                } catch (Exception $e) {
                    // Rollback the transaction if any query fails
                    $pdo->rollBack();
                    throw new Exception('Error deleting student: ' . $e->getMessage());
                }
            }
            break;

        case 'get':
            $student_id = filter_input(INPUT_GET, 'student_id', FILTER_VALIDATE_INT);
            
            if (!$student_id) {
                throw new Exception('Invalid student ID');
            }

            $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
            $stmt->execute([$student_id]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$student) {
                throw new Exception('Student not found');
            }

            $response['status'] = 'success';
            $response['data'] = $student;
            break;

        case 'list':
            $stmt = $pdo->query("SELECT * FROM students ORDER BY registration_date ASC");
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['status'] = 'success';
            $response['data'] = $students;
            break;

        case 'get_student_loans':
            $student_id = filter_input(INPUT_GET, 'student_id', FILTER_VALIDATE_INT);
            if (!$student_id) {
                throw new Exception('Student ID is required');
            }

            $stmt = $pdo->prepare("
                SELECT 
                    l.loan_id,
                    l.book_id,
                    b.title,
                    l.loan_date,
                    l.due_date,
                    l.status
                FROM active_loans l
                JOIN books b ON l.book_id = b.book_id
                WHERE l.student_id = ?
                ORDER BY l.loan_date DESC
            ");
            $stmt->execute([$student_id]);
            $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response['status'] = 'success';
            $response['data'] = $loans;
            break;

        case 'check_active_loans':
            $student_id = filter_input(INPUT_GET, 'student_id', FILTER_VALIDATE_INT);
            
            if (!$student_id) {
                throw new Exception('Invalid student ID');
            }

            // Check if student has any active (non-returned) loans
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as loan_count 
                FROM active_loans 
                WHERE student_id = ? AND status != 'Returned'
            ");
            $stmt->execute([$student_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $response['status'] = 'success';
            $response['has_active_loans'] = ($result['loan_count'] > 0);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
