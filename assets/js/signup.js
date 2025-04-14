document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.querySelector('.auth-form');
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
        firstname: /^[a-zA-Z]{2,30}$/, // 2-30 letters
        lastname: /^[a-zA-Z]{2,30}$/, // 2-30 letters
        username: /^[a-zA-Z0-9_]{4,20}$/, // 4-20 characters, alphanumeric and underscore
        email: /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/, // Email pattern
        password: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/ // Strong password
    };

    // Error messages
    const errorMessages = {
        firstname: {
            pattern: 'First name must be 2-30 letters long',
            required: 'First name is required'
        },
        lastname: {
            pattern: 'Last name must be 2-30 letters long',
            required: 'Last name is required'
        },
        username: {
            pattern: 'Username must be 4-20 characters long and can only contain letters, numbers, and underscores',
            required: 'Username is required'
        },
        email: {
            pattern: 'Please enter a valid email address',
            required: 'Email is required'
        },
        password: {
            pattern: 'Password must be at least 8 characters long and include uppercase, lowercase, number, and special character',
            required: 'Password is required'
        },
        confirmPassword: {
            match: 'Passwords do not match',
            required: 'Please confirm your password'
        }
    };

    // Validate field
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

        // Special validation for confirm password
        if (fieldName === 'confirmPassword') {
            if (!field.value) {
                showError(field, errorMessages[fieldName].required);
                return false;
            } else if (field.value !== inputs.password.value) {
                showError(field, errorMessages[fieldName].match);
                return false;
            } else {
                clearError(field);
                return true;
            }
        }

        // Regular validation
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
    Object.keys(inputs).forEach(fieldName => {
        inputs[fieldName].addEventListener('input', () => validateField(inputs[fieldName], fieldName));
    });

    // Password strength indicator
    inputs.password.addEventListener('input', function() {
        const strength = checkPasswordStrength(this.value);
        updatePasswordStrengthIndicator(strength);
    });

    function checkPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        return strength;
    }

    function updatePasswordStrengthIndicator(strength) {
        const indicator = document.getElementById('password-strength');
        const messages = ['Very Weak', 'Weak', 'Medium', 'Strong', 'Very Strong'];
        const colors = ['#ff4444', '#ffbb33', '#ffeb3b', '#00C851', '#007E33'];
        
        indicator.textContent = messages[strength - 1];
        indicator.style.color = colors[strength - 1];
    }

    // Form submission
    signupForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Validate all fields
        let isValid = true;
        Object.keys(inputs).forEach(fieldName => {
            if (!validateField(inputs[fieldName], fieldName)) {
                isValid = false;
            }
        });

        if (isValid) {
            // Show loading state
            const submitButton = signupForm.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';

            const firstname = document.getElementById('firstname').value;
            const lastname = document.getElementById('lastname').value;
            const email = document.getElementById('email').value;
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            const formData = new FormData();
            formData.append('firstname', firstname);
            formData.append('lastname', lastname);
            formData.append('email', email);
            formData.append('username', username);
            formData.append('password', password);
            formData.append('register', '1');
            
            fetch('../api/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Account created successfully. Please login.');
                    window.location.href = '../pages/login.php';
                } else {
                    alert(data.message || 'Registration failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error during registration:', error);
                alert('An error occurred during registration. Please try again.');
            })
            .finally(() => {
                // Reset button state
                submitButton.disabled = false;
                submitButton.innerHTML = 'Create Account';
            });
        }
    });
});