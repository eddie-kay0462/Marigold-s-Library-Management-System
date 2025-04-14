<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Margold Montessori Library</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Add SweetAlert2 CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Add this internal style for immediate effect -->
    <style>
        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .field-error {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 0.375rem;
            display: none;
            padding-left: 0.25rem;
        }

        .input-error {
            border-color: #dc2626 !important;
            background-color: #fef2f2 !important;
        }

        .input-error:focus {
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1) !important;
        }

        .password-strength {
            font-size: 0.875rem;
            margin-top: 0.375rem;
            padding-left: 0.25rem;
        }

        .validation-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #dc2626;
        }

        .validation-icon.valid {
            color: #16a34a;
        }

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
                <a href="../index.php">
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
                <h1>Create Account</h1>
                <p>Join our library community</p>
            </div>

            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-error">
                        <i class="fas fa-circle-exclamation"></i>
                        <div class="alert-content">' . htmlspecialchars($_SESSION['error']) . '</div>
                      </div>';
                unset($_SESSION['error']);
            }
            ?>

            <form class="auth-form" id="signupForm" action="../api/auth.php" method="POST">
                <input type="hidden" name="register" value="1">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstname">First Name</label>
                        <input type="text" id="firstname" name="firstname" required>
                        <i class="fas fa-exclamation-circle error-icon"></i>
                        <span class="field-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="lastname">Last Name</label>
                        <input type="text" id="lastname" name="lastname" required>
                        <i class="fas fa-exclamation-circle error-icon"></i>
                        <span class="field-error"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                    <i class="fas fa-exclamation-circle error-icon"></i>
                    <span class="field-error"></span>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                    <i class="fas fa-exclamation-circle error-icon"></i>
                    <span class="field-error"></span>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <i class="fas fa-exclamation-circle error-icon"></i>
                    <span class="field-error"></span>
                    <div class="password-strength" id="password-strength"></div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <i class="fas fa-exclamation-circle error-icon"></i>
                    <span class="field-error"></span>
                </div>

                <button type="submit" class="auth-button">Create Account</button>

                <div class="auth-footer">
                    <p>Already have an account? <a href="login.php">Login</a></p>
                </div>
            </form>
        </div>
    </div>

    <footer class="page-footer">
        <p>© 2025 Margold Montessori School Library. All rights reserved.</p>
    </footer>

    <!-- Updated JavaScript for signup validation -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('signupForm');
            const inputs = {
                firstname: document.getElementById('firstname'),
                lastname: document.getElementById('lastname'),
                username: document.getElementById('username'),
                email: document.getElementById('email'),
                password: document.getElementById('password'),
                confirmPassword: document.getElementById('confirm_password')
            };

            // Validation patterns
            const patterns = {
                firstname: /^[a-zA-Z]{2,30}$/,
                lastname: /^[a-zA-Z]{2,30}$/,
                username: /^[a-zA-Z0-9_]{4,20}$/,
                email: /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
                password: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/
            };

            // Error messages
            const errorMessages = {
                firstname: {
                    pattern: 'First name must be 2-30 letters only',
                    required: 'First name is required'
                },
                lastname: {
                    pattern: 'Last name must be 2-30 letters only',
                    required: 'Last name is required'
                },
                username: {
                    pattern: 'Username must be 4-20 characters (letters, numbers, underscore)',
                    required: 'Username is required'
                },
                email: {
                    pattern: 'Please enter a valid email address',
                    required: 'Email is required'
                },
                password: {
                    pattern: 'Password must have 8+ characters, uppercase, lowercase, number, and special character',
                    required: 'Password is required'
                },
                confirmPassword: {
                    match: 'Passwords do not match',
                    required: 'Please confirm your password'
                }
            };

            // Show error function
            function showError(input, message) {
                const formGroup = input.parentElement;
                const errorDisplay = formGroup.querySelector('.field-error');
                input.classList.add('input-error');
                errorDisplay.textContent = message;
                errorDisplay.style.display = 'block';
            }

            // Clear error function
            function clearError(input) {
                const formGroup = input.parentElement;
                const errorDisplay = formGroup.querySelector('.field-error');
                input.classList.remove('input-error');
                errorDisplay.textContent = '';
                errorDisplay.style.display = 'none';
            }

            // Validate field function
            function validateField(input, fieldName) {
                if (!input.value.trim()) {
                    showError(input, errorMessages[fieldName].required);
                    return false;
                }

                if (fieldName === 'confirmPassword') {
                    if (input.value !== inputs.password.value) {
                        showError(input, errorMessages[fieldName].match);
                        return false;
                    }
                } else if (patterns[fieldName] && !patterns[fieldName].test(input.value.trim())) {
                    showError(input, errorMessages[fieldName].pattern);
                    return false;
                }

                clearError(input);
                return true;
            }

            // Add input event listeners
            Object.keys(inputs).forEach(fieldName => {
                inputs[fieldName].addEventListener('input', () => {
                    validateField(inputs[fieldName], fieldName);
                });
            });

            // Password strength indicator
            function updatePasswordStrength(password) {
                const strengthDiv = document.getElementById('password-strength');
                let strength = 0;
                let message = '';
                let color = '';

                if (password.length >= 8) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[@$!%*?&]/.test(password)) strength++;

                switch (strength) {
                    case 0:
                    case 1:
                        message = 'Very Weak';
                        color = '#dc2626';
                        break;
                    case 2:
                        message = 'Weak';
                        color = '#f59e0b';
                        break;
                    case 3:
                        message = 'Medium';
                        color = '#fbbf24';
                        break;
                    case 4:
                        message = 'Strong';
                        color = '#34d399';
                        break;
                    case 5:
                        message = 'Very Strong';
                        color = '#059669';
                        break;
                }

                strengthDiv.textContent = `Password Strength: ${message}`;
                strengthDiv.style.color = color;
            }

            inputs.password.addEventListener('input', (e) => {
                updatePasswordStrength(e.target.value);
            });

            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                let isValid = true;
                Object.keys(inputs).forEach(fieldName => {
                    if (!validateField(inputs[fieldName], fieldName)) {
                        isValid = false;
                    }
                });

                if (isValid) {
                    const submitButton = form.querySelector('button[type="submit"]');
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';

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
                                title: 'Success!',
                                text: 'Account created successfully! Redirecting to login...',
                                timer: 2000,
                                showConfirmButton: false,
                                willClose: () => {
                                    window.location.href = '../pages/login.php';
                                }
                            });
                        } else {
                            throw new Error(data.message || 'Registration failed');
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: error.message || 'An error occurred during registration'
                        });
                    })
                    .finally(() => {
                        submitButton.disabled = false;
                        submitButton.innerHTML = 'Create Account';
                    });
                }
            });
        });
    </script>
</body>
</html> 