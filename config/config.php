<?php
// Application settings
define('APP_NAME', 'Marigold Library Management System');
define('APP_URL', 'http://localhost/Marigold-s-Library-Management-System');

// Session settings
session_start();

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to redirect
function redirect($path) {
    // Remove any leading slash or 'pages/' from the path
    $path = ltrim($path, '/');
    $path = preg_replace('/^pages\//', '', $path);
    
    header('Location: ' . APP_URL . '/' . $path);
    exit;
}