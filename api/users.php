<?php
header('Content-Type: application/json');
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

require_once('../config/database.php');

// Create database connection
$database = new Database();
$conn = $database->getConnection();

// Helper function to send JSON response
function sendJsonResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            // Get single user if ID is provided, otherwise get all users
            if (isset($_GET['id'])) {
                $stmt = $conn->prepare("SELECT id, username, firstname, lastname, email, role_id, is_active FROM users WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    sendJsonResponse(true, 'User found', $user);
                } else {
                    sendJsonResponse(false, 'User not found');
                }
            } else {
                // Get all users
                $stmt = $conn->query("SELECT id, username, firstname, lastname, email, role_id, is_active FROM users ORDER BY id");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                sendJsonResponse(true, 'Users retrieved successfully', $users);
            }
            break;

        case 'POST':
            // Validate required fields
            $requiredFields = ['username', 'password', 'firstname', 'lastname', 'email', 'role_id'];
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {
                    sendJsonResponse(false, "Missing required field: $field");
                }
            }

            // Check if username or email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$_POST['username'], $_POST['email']]);
            if ($stmt->fetch()) {
                sendJsonResponse(false, 'Username or email already exists');
            }

            // Hash password
            $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, password, firstname, lastname, email, role_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['username'],
                $hashedPassword,
                $_POST['firstname'],
                $_POST['lastname'],
                $_POST['email'],
                $_POST['role_id']
            ]);

            sendJsonResponse(true, 'User created successfully');
            break;

        case 'PUT':
            if (!isset($_GET['id'])) {
                sendJsonResponse(false, 'User ID is required');
            }

            parse_str(file_get_contents("php://input"), $_PUT);

            $updates = [];
            $params = [];

            // Only update provided fields
            if (isset($_PUT['firstname'])) {
                $updates[] = "firstname = ?";
                $params[] = $_PUT['firstname'];
            }
            if (isset($_PUT['lastname'])) {
                $updates[] = "lastname = ?";
                $params[] = $_PUT['lastname'];
            }
            if (isset($_PUT['email'])) {
                $updates[] = "email = ?";
                $params[] = $_PUT['email'];
            }
            if (isset($_PUT['role_id'])) {
                $updates[] = "role_id = ?";
                $params[] = $_PUT['role_id'];
            }
            if (isset($_PUT['password'])) {
                $updates[] = "password = ?";
                $params[] = password_hash($_PUT['password'], PASSWORD_DEFAULT);
            }

            if (empty($updates)) {
                sendJsonResponse(false, 'No fields to update');
            }

            $params[] = $_GET['id'];
            $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() > 0) {
                sendJsonResponse(true, 'User updated successfully');
            } else {
                sendJsonResponse(false, 'User not found or no changes made');
            }
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                sendJsonResponse(false, 'User ID is required');
            }

            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$_GET['id']]);

            if ($stmt->rowCount() > 0) {
                sendJsonResponse(true, 'User deleted successfully');
            } else {
                sendJsonResponse(false, 'User not found');
            }
            break;

        default:
            sendJsonResponse(false, 'Method not allowed');
    }
} catch (Exception $e) {
    sendJsonResponse(false, 'An error occurred: ' . $e->getMessage());
} 