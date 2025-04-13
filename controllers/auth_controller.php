<?php
require_once '../config/config.php';
require_once '../models/User.php';
require_once '../config/database.php';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $database = new Database();
    $db = $database->connect();
    
    $user = new User($db);
    $user->username = $_POST['username'];
    $user->password = $_POST['password'];
    
    if ($user->login()) {
        // Set session variables
        $_SESSION['user_id'] = $user->user_id;
        $_SESSION['username'] = $user->username;
        $_SESSION['name'] = $user->first_name . ' ' . $user->last_name;
        $_SESSION['role_id'] = $user->role_id;
        
        redirect('pages/dashboard.html');
    } else {
        $_SESSION['error'] = 'Invalid username or password';
        redirect('pages/login.html');
    }
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $database = new Database();
    $db = $database->connect();
    
    $user = new User($db);
    $user->username = $_POST['username'];
    $user->password = $_POST['password'];
    $user->first_name = $_POST['firstname'];
    $user->last_name = $_POST['lastname'];
    $user->email = $_POST['email'];
    $user->role_id = 5; // Default role (Staff)
    
    if ($user->create()) {
        $_SESSION['success'] = 'Account created successfully. Please login.';
        redirect('pages/login.html');
    } else {
        $_SESSION['error'] = 'Failed to create account';
        redirect('pages/signup.html');
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    redirect('index.html');
}