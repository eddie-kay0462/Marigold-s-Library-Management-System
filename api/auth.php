<?php
// Prevent any output before headers
ob_start();

// Set headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../config/config.php';
require_once '../models/User.php';
require_once '../config/database.php';

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function sendResponse($success, $message, $redirect = null) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    if ($redirect) {
        $response['redirect'] = $redirect;
    }
    echo json_encode($response);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    // Handle Registration
    if (isset($_POST['register'])) {
        // Validate required fields
        $required_fields = ['username', 'password', 'firstname', 'lastname', 'email'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception("$field is required");
            }
        }

        // Set user properties
        $user->username = $_POST['username'];
        $user->password = $_POST['password'];
        $user->first_name = $_POST['firstname'];
        $user->last_name = $_POST['lastname'];
        $user->email = $_POST['email'];
        $user->role_id = 5; // Default role (Staff)

        if ($user->create()) {
            sendResponse(true, 'Account created successfully', '../pages/login.php');
        } else {
            throw new Exception('Failed to create account');
        }
    }
    // Handle Login
    else if (isset($_POST['login'])) {
        if (!isset($_POST['username']) || !isset($_POST['password'])) {
            throw new Exception('Username and password are required');
        }

        $user->username = $_POST['username'];
        $user->password = $_POST['password'];

        if ($user->login()) {
            $_SESSION['user_id'] = $user->user_id;
            $_SESSION['username'] = $user->username;
            $_SESSION['name'] = $user->first_name . ' ' . $user->last_name;
            $_SESSION['role_id'] = $user->role_id;

            sendResponse(true, 'Login successful', '../pages/dashboard.php');
        } else {
            throw new Exception('Invalid username or password');
        }
    }
    else {
        throw new Exception('Invalid request');
    }
} catch (Exception $e) {
    http_response_code(400);
    sendResponse(false, $e->getMessage());
}

// Clear any buffered output
ob_end_clean();