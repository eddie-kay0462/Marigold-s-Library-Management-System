<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields";
        header("Location: ../login.php");
        exit();
    }

    // Debug information
    error_log("Login attempt for username: " . $username);

    try {
        $database = new Database();
        $pdo = $database->getConnection();

        if (!$pdo) {
            throw new Exception("Database connection failed");
        }

        $query = "SELECT user_id, first_name, last_name, username, password FROM users WHERE username = :username";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        error_log("Query executed, found rows: " . $stmt->rowCount());

        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['first_name'] = $row['first_name'];
                $_SESSION['last_name'] = $row['last_name'];
                $_SESSION['username'] = $row['username'];
                
                error_log("Login successful for user: " . $username);
                $_SESSION['success'] = "Login successful! Welcome back, " . htmlspecialchars($row['username']) . "!";
                header("Location: ../dashboard.php");
                exit();
            } else {
                error_log("Invalid password for user: " . $username);
                $_SESSION['error'] = "Invalid username or password";
                header("Location: ../login.php");
                exit();
            }
        } else {
            error_log("Username not found: " . $username);
            $_SESSION['error'] = "Invalid username or password";
            header("Location: ../login.php");
            exit();
        }
    } catch(Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred. Please try again later.";
        header("Location: ../login.php");
        exit();
    }
} else {
    header("Location: ../login.php");
    exit();
}
?> 