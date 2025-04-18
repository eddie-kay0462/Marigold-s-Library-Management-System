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
        case 'list':
            $stmt = $pdo->prepare("
                SELECT user_id, first_name, last_name, email, created_at 
                FROM users 
                ORDER BY user_id ASC
            ");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response['status'] = 'success';
            $response['data'] = $users;
            break;

        case 'search':
            $query = isset($_GET['query']) ? $_GET['query'] : '';
            if (empty($query)) {
                throw new Exception('Search query is required');
            }

            $stmt = $pdo->prepare("
                SELECT user_id, first_name, last_name, email, created_at 
                FROM users 
                WHERE first_name LIKE :query 
                OR last_name LIKE :query
                OR email LIKE :query 
                ORDER BY user_id ASC
            ");
            
            $searchQuery = "%$query%";
            $stmt->bindParam(':query', $searchQuery, PDO::PARAM_STR);
            $stmt->execute();
            
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response['status'] = 'success';
            $response['data'] = $users;
            break;

        case 'get':
            $userId = isset($_GET['user_id']) ? $_GET['user_id'] : '';
            if (empty($userId)) {
                throw new Exception('User ID is required');
            }

            $stmt = $pdo->prepare("
                SELECT user_id, first_name, last_name, email, created_at 
                FROM users 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new Exception('User not found');
            }

            $response['status'] = 'success';
            $response['data'] = $user;
            break;

        case 'update':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $userId = $_POST['user_id'] ?? '';
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($userId) || empty($firstName) || empty($lastName) || empty($email)) {
                throw new Exception('All fields are required');
            }

            // Check if email exists for other users
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM users 
                WHERE email = ? 
                AND user_id != ?
            ");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('Email already exists');
            }

            // Start building the update query
            $updateFields = ['first_name = ?', 'last_name = ?', 'email = ?'];
            $params = [$firstName, $lastName, $email];

            // Add password to update if provided
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateFields[] = 'password = ?';
                $params[] = $hashedPassword;
            }

            // Add user_id to params
            $params[] = $userId;

            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $response['status'] = 'success';
            $response['message'] = 'User updated successfully';
            break;

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $userId = $_POST['user_id'] ?? '';
            if (empty($userId)) {
                throw new Exception('User ID is required');
            }

            // Prevent deleting the logged-in user
            if ($userId == $_SESSION['user_id']) {
                throw new Exception('Cannot delete your own account');
            }

            // Check if user exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            if ($stmt->fetchColumn() == 0) {
                throw new Exception('User not found');
            }

            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);

            $response['status'] = 'success';
            $response['message'] = 'User deleted successfully';
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    error_log('Database error in user_handler.php: ' . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Error in user_handler.php: ' . $e->getMessage());
}

// Send JSON response
echo json_encode($response);
exit(); 