<?php
@ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set headers first
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Helper function to send JSON response
function sendJsonResponse($success, $message = '', $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Create database connection
try {
    require_once('../config/database.php');
    $database = new Database();
    $conn = $database->getConnection();
} catch (Exception $e) {
    sendJsonResponse(false, 'Database connection failed: ' . $e->getMessage());
}

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            if (isset($_GET['id'])) {
                // Get single student
                $stmt = $conn->prepare("
                    SELECT * FROM students WHERE student_id = ?
                ");
                $stmt->execute([$_GET['id']]);
                $student = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($student) {
                    sendJsonResponse(true, '', $student);
                } else {
                    sendJsonResponse(false, 'Student not found');
                }
            } else {
                // Get all students
                $stmt = $conn->query("SELECT * FROM students ORDER BY student_id");
                $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                sendJsonResponse(true, '', $students);
            }
        } catch (Exception $e) {
            sendJsonResponse(false, 'Error fetching students: ' . $e->getMessage());
        }
        break;

    case 'POST':
        try {
            // Validate required fields
            $required = ['student_number', 'first_name', 'last_name', 'email', 'date_of_birth'];
            foreach ($required as $field) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {
                    sendJsonResponse(false, ucfirst(str_replace('_', ' ', $field)) . ' is required');
                }
            }

            // Check if student number already exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM students WHERE student_number = ?");
            $stmt->execute([$_POST['student_number']]);
            if ($stmt->fetchColumn() > 0) {
                sendJsonResponse(false, 'Student number already exists');
            }

            // Check if email already exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM students WHERE email = ?");
            $stmt->execute([$_POST['email']]);
            if ($stmt->fetchColumn() > 0) {
                sendJsonResponse(false, 'Email already exists');
            }

            // Insert new student
            $stmt = $conn->prepare("
                INSERT INTO students (
                    student_number, first_name, last_name, email, 
                    date_of_birth, registration_date, is_active, created_at
                ) VALUES (?, ?, ?, ?, ?, NOW(), 1, NOW())
            ");
            
            $stmt->execute([
                $_POST['student_number'],
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['email'],
                $_POST['date_of_birth']
            ]);

            sendJsonResponse(true, 'Student added successfully');
        } catch (Exception $e) {
            sendJsonResponse(false, 'Error adding student: ' . $e->getMessage());
        }
        break;

    case 'PUT':
        try {
            if (!isset($_GET['id'])) {
                sendJsonResponse(false, 'Student ID is required');
            }

            parse_str(file_get_contents("php://input"), $_PUT);

            // Validate required fields
            $required = ['student_number', 'first_name', 'last_name', 'email', 'date_of_birth'];
            foreach ($required as $field) {
                if (!isset($_PUT[$field]) || empty($_PUT[$field])) {
                    sendJsonResponse(false, ucfirst(str_replace('_', ' ', $field)) . ' is required');
                }
            }

            // Check if student number exists for other students
            $stmt = $conn->prepare("SELECT COUNT(*) FROM students WHERE student_number = ? AND student_id != ?");
            $stmt->execute([$_PUT['student_number'], $_GET['id']]);
            if ($stmt->fetchColumn() > 0) {
                sendJsonResponse(false, 'Student number already exists');
            }

            // Check if email exists for other students
            $stmt = $conn->prepare("SELECT COUNT(*) FROM students WHERE email = ? AND student_id != ?");
            $stmt->execute([$_PUT['email'], $_GET['id']]);
            if ($stmt->fetchColumn() > 0) {
                sendJsonResponse(false, 'Email already exists');
            }

            // Update student
            $stmt = $conn->prepare("
                UPDATE students 
                SET student_number = ?,
                    first_name = ?, 
                    last_name = ?, 
                    email = ?, 
                    date_of_birth = ?,
                    updated_at = NOW()
                WHERE student_id = ?
            ");
            
            $stmt->execute([
                $_PUT['student_number'],
                $_PUT['first_name'],
                $_PUT['last_name'],
                $_PUT['email'],
                $_PUT['date_of_birth'],
                $_GET['id']
            ]);

            if ($stmt->rowCount() === 0) {
                sendJsonResponse(false, 'Student not found or no changes made');
            }

            sendJsonResponse(true, 'Student updated successfully');
        } catch (Exception $e) {
            sendJsonResponse(false, 'Error updating student: ' . $e->getMessage());
        }
        break;

    case 'DELETE':
        try {
            if (!isset($_GET['id'])) {
                sendJsonResponse(false, 'Student ID is required');
            }

            $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
            $stmt->execute([$_GET['id']]);

            if ($stmt->rowCount() === 0) {
                sendJsonResponse(false, 'Student not found');
            }

            sendJsonResponse(true, 'Student deleted successfully');
        } catch (Exception $e) {
            sendJsonResponse(false, 'Error deleting student: ' . $e->getMessage());
        }
        break;

    default:
        sendJsonResponse(false, 'Invalid request method');
}
?>