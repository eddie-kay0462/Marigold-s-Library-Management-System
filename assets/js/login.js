document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('.auth-form');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');

    // Input validation patterns
    const patterns = {
        username: /^[a-zA-Z0-9_]{4,20}$/, // 4-20 characters, alphanumeric and underscore
        password: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/ // Strong password
    };

    // Error messages
    const errorMessages = {
        username: {
            pattern: 'Username must be 4-20 characters long and can only contain letters, numbers, and underscores',
            required: 'Username is required'
        },
        password: {
            pattern: 'Password must be at least 8 characters long and include uppercase, lowercase, number, and special character',
            required: 'Password is required'
        }
    };

    // Real-time validation
    function validateField(input, fieldName) {
        const field = input;
        const pattern = patterns[fieldName];
        const errorContainer = field.parentElement.querySelector('.field-error');
        
        // Create error container if it doesn't exist
        if (!errorContainer) {
            const error = document.createElement('div');
            error.className = 'field-error';
            field.parentElement.appendChild(error);
        }

        // Validate
        if (!field.value) {
            showError(field, errorMessages[fieldName].required);
            return false;
        } else if (!pattern.test(field.value)) {
            showError(field, errorMessages[fieldName].pattern);
            return false;
        } else {
            clearError(field);
            return true;
        }
    }

    // Show error message
    function showError(field, message) {
        field.classList.add('input-error');
        const errorDiv = field.parentElement.querySelector('.field-error');
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }

    // Clear error message
    function clearError(field) {
        field.classList.remove('input-error');
        const errorDiv = field.parentElement.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
    }

    // Add input event listeners for real-time validation
    usernameInput.addEventListener('input', () => validateField(usernameInput, 'username'));
    passwordInput.addEventListener('input', () => validateField(passwordInput, 'password'));

    // Form submission
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Validate all fields
        const isUsernameValid = validateField(usernameInput, 'username');
        const isPasswordValid = validateField(passwordInput, 'password');

        if (isUsernameValid && isPasswordValid) {
            // Show loading state
            const submitButton = loginForm.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';

            // Get form data
            const formData = new FormData(loginForm);

            // Send request
            fetch('../api/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect || '../pages/dashboard.php';
                } else {
                    throw new Error(data.message || 'Login failed');
                }
            })
            .catch(error => {
                showAlert('error', error.message || 'An error occurred during login');
            })
            .finally(() => {
                // Reset button state
                submitButton.disabled = false;
                submitButton.innerHTML = 'Login';
            });
        }
    });
});