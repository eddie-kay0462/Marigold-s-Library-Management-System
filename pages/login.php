<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Margold Montessori Library</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Add SweetAlert2 CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Add internal styles for alert -->
    <style>
        /* Alert/Error Styles */
        .alert {
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            line-height: 1.5;
        }

        .alert-error {
            background-color: #FEF2F2;
            color: #DC2626;
            border: 1px solid #FEE2E2;
        }

        .alert-success {
            background-color: #F0FDF4;
            color: #16A34A;
            border: 1px solid #DCFCE7;
        }

        .alert i {
            font-size: 16px;
        }

        /* Form Validation Styles */
        .field-error {
            color: #DC2626;
            font-size: 13px;
            margin-top: 4px;
            display: block;
            font-weight: 500;
        }

        .input-error {
            border-color: #DC2626 !important;
            background-color: #FEF2F2 !important;
        }

        .input-error:focus {
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1) !important;
        }

        /* Form Group Spacing */
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        /* Error Icon */
        .error-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #DC2626;
            display: none;
        }

        .input-error + .error-icon {
            display: block;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <div class="logo">
                <a href="index.php">
                    <img src="../assets/images/logo.png" alt="Margold Library Logo">
                </a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../pages/about.php">About</a></li>
                    <li><a href="../pages/contact.php">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1>Welcome Back</h1>
                <p>Login to access your library account</p>
            </div>

            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-error">
                        <i class="fas fa-circle-exclamation"></i>
                        <div class="alert-content">' . htmlspecialchars($_SESSION['error']) . '</div>
                      </div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">
                        <i class="fas fa-circle-check"></i>
                        <div class="alert-content">' . htmlspecialchars($_SESSION['success']) . '</div>
                      </div>';
                unset($_SESSION['success']);
            }
            ?>

            <form class="auth-form" id="loginForm" action="../api/auth.php" method="POST">
                <input type="hidden" name="login" value="1">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                    <div class="field-error"></div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <div class="field-error"></div>
                </div>

                <div class="form-links">
                    <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="auth-button">Login</button>

                <div class="auth-footer">
                    <p>Don't have an account? <a href="../pages/signup.php">Sign Up</a></p>
                </div>
            </form>
        </div>
    </div>

    <footer class="page-footer">
        <p>© 2025 Margold Montessori School Library. All rights reserved.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            
            // Basic form validation
            function validateForm() {
                const username = document.getElementById('username');
                const password = document.getElementById('password');
                let isValid = true;

                if (!username.value.trim()) {
                    showError(username, 'Username is required');
                    isValid = false;
                } else {
                    clearError(username);
                }

                if (!password.value.trim()) {
                    showError(password, 'Password is required');
                    isValid = false;
                } else {
                    clearError(password);
                }

                return isValid;
            }

            // Show error message
            function showError(input, message) {
                const formGroup = input.parentElement;
                const errorDisplay = formGroup.querySelector('.field-error');
                input.classList.add('input-error');
                errorDisplay.textContent = message;
                errorDisplay.style.display = 'block';
            }

            // Clear error message
            function clearError(input) {
                const formGroup = input.parentElement;
                const errorDisplay = formGroup.querySelector('.field-error');
                input.classList.remove('input-error');
                errorDisplay.textContent = '';
                errorDisplay.style.display = 'none';
            }

            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                if (validateForm()) {
                    const submitButton = form.querySelector('button[type="submit"]');
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';

                    const formData = new FormData(form);

                    fetch('../api/auth.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Welcome!',
                                text: 'Login successful! Redirecting to dashboard...',
                                timer: 2000,
                                showConfirmButton: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                                willClose: () => {
                                    window.location.href = '../pages/dashboard.php';
                                }
                            });
                        } else {
                            throw new Error(data.message || 'Login failed');
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Failed',
                            text: error.message || 'Invalid username or password',
                            confirmButtonColor: '#dc2626'
                        });
                    })
                    .finally(() => {
                        submitButton.disabled = false;
                        submitButton.innerHTML = 'Login';
                    });
                }
            });

            // Clear errors on input
            const inputs = form.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('input', () => clearError(input));
            });
        });
    </script>
</body>
</html> 