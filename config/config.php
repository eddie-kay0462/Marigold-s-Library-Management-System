<?php
// Application settings
define('APP_NAME', 'Marigold Library Management System');
define('APP_URL', 'http://localhost/marigold-library');

// Session settings
session_start();

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to redirect
function redirect($page) {
    header('Location: ' . APP_URL . '/' . $page);
    exit;
}