<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

require_once '../config/config.php';
require_once '../models/User.php';
require_once '../config/database.php';

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $database = new Database();
        $db = $database->connect();
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
                echo json_encode([
                    'success' => true,
                    'message' => 'Account created successfully',
                    'redirect' => '../pages/login.php'
                ]);
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

                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect' => '../pages/dashboard.php'
                ]);
            } else {
                throw new Exception('Invalid username or password');
            }
        }

        else {
            throw new Exception('Invalid request');
        }
    } else {
        throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}