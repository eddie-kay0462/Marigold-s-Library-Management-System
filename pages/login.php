<?php
session_start();
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Marigold Library</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #FFE6C7, #FFFFFF);
        }

        .page-wrapper {
            min-height: 100%;
            display: flex;
            flex-direction: column;
        }

        .header {
            background-color: rgba(255, 255, 255, 0.98);
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo img {
            height: 45px;
            width: auto;
        }

        .main-nav ul {
            display: flex;
            list-style: none;
            gap: 2.5rem;
            margin: 0;
            padding: 0;
        }

        .main-nav a {
            text-decoration: none;
            color: #2C3E50;
            font-weight: 500;
        }

        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .auth-box {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-header h1 {
            margin: 0 0 0.5rem 0;
            color: #2C3E50;
        }

        .auth-header p {
            margin: 0;
            color: #6c757d;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2C3E50;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 1rem;
        }

        .form-group input:focus {
            outline: none;
            border-color: #FF8303;
        }

        .auth-button {
            width: 100%;
            padding: 0.75rem;
            background: #FF8303;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }

        .auth-button:hover {
            background: #FF6000;
        }

        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #dee2e6;
        }

        .auth-footer a {
            color: #FF8303;
            text-decoration: none;
            font-weight: 500;
        }

        .auth-footer a:hover {
            color: #FF6000;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .success-message {
            color: #198754;
            background-color: #d1e7dd;
            border: 1px solid #badbcc;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .page-footer {
            background: white;
            padding: 1rem;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }

        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }

            .main-nav ul {
                gap: 1.5rem;
                flex-wrap: wrap;
                justify-content: center;
            }

            .main-content {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <header class="header">
            <div class="nav-container">
                <div class="logo">
                    <a href="../index.php">
                        <img src="../assets/images/logo.png" alt="Margold Library Logo">
                    </a>
                </div>
                <nav class="main-nav">
                    <ul>
                        <li><a href="../index.php">Home</a></li>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <li><a href="../pages/dashboard.php">Dashboard</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </header>

        <main class="main-content">
            <div class="auth-box">
                <div class="auth-header">
                    <h1>Login</h1>
                    <p>Welcome back to our library community</p>
                </div>

                <?php 
                if(isset($_SESSION['success'])) {
                    $successMessage = $_SESSION['success'];
                    unset($_SESSION['success']);
                    echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showSuccess('" . htmlspecialchars($successMessage, ENT_QUOTES) . "');
                        });
                    </script>";
                }
                ?>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="error-message">
                        <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <form id="loginForm" action="auth/login_handler.php" method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                        <span class="error-message" id="usernameError"></span>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                        <span class="error-message" id="passwordError"></span>
                    </div>
                    <button type="submit" class="auth-button">Login</button>
                </form>

                <div class="auth-footer">
                    <p>Don't have an account? <a href="signup.php">Sign up</a></p>
                </div>
            </div>
        </main>

        <footer class="page-footer">
            <p>© 2024 Margold Montessori School Library. All rights reserved.</p>
        </footer>
    </div>
    <script src="../assets/js/validation.js"></script>
    <script src="../assets/js/messages.js"></script>
    <script>
    // Add success message functionality
    function showSuccess(message) {
        // Create success message container if it doesn't exist
        let successContainer = document.querySelector('.success-message-container');
        if (!successContainer) {
            successContainer = document.createElement('div');
            successContainer.className = 'success-message-container';
            document.body.appendChild(successContainer);
        }

        // Create the success message element
        const successDiv = document.createElement('div');
        successDiv.className = 'success-message';
        successDiv.innerHTML = `
            <i class="fas fa-check-circle"></i>
            <span>${message}</span>
        `;
        
        // Add the message to the container
        successContainer.appendChild(successDiv);

        // Add animation class
        setTimeout(() => {
            successDiv.classList.add('show');
        }, 10);

        // Remove the message after 3 seconds
        setTimeout(() => {
            successDiv.classList.add('hide');
            setTimeout(() => {
                successDiv.remove();
                // Remove container if no more messages
                if (successContainer.children.length === 0) {
                    successContainer.remove();
                }
            }, 300); // Wait for fade out animation
        }, 3000);
    }

    // Add the success message styles
    const style = document.createElement('style');
    style.textContent = `
        .success-message-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .success-message {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateX(120%);
            transition: transform 0.3s ease-out;
            opacity: 0;
        }

        .success-message i {
            font-size: 1.2rem;
        }

        .success-message.show {
            transform: translateX(0);
            opacity: 1;
        }

        .success-message.hide {
            transform: translateX(120%);
            opacity: 0;
        }
    `;
    document.head.appendChild(style);
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</body>
</html> 