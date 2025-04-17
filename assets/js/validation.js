document.addEventListener('DOMContentLoaded', function() {
    // Login Form Validation
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            let isValid = true;
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            
            // Email validation
            if (!validateEmail(email.value)) {
                document.getElementById('emailError').textContent = 'Please enter a valid email address';
                isValid = false;
            } else {
                document.getElementById('emailError').textContent = '';
            }
            
            // Password validation
            if (password.value.length < 8) {
                document.getElementById('passwordError').textContent = 'Password must be at least 8 characters long';
                isValid = false;
            } else {
                document.getElementById('passwordError').textContent = '';
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    // Signup Form Validation
    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            let isValid = true;
            const firstname = document.getElementById('firstname');
            const lastname = document.getElementById('lastname');
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            // Firstname validation
            if (firstname.value.trim() === '') {
                document.getElementById('firstnameError').textContent = 'Please enter your first name';
                isValid = false;
            } else {
                document.getElementById('firstnameError').textContent = '';
            }
            
            // Lastname validation
            if (lastname.value.trim() === '') {
                document.getElementById('lastnameError').textContent = 'Please enter your last name';
                isValid = false;
            } else {
                document.getElementById('lastnameError').textContent = '';
            }
            
            // Email validation
            if (!validateEmail(email.value)) {
                document.getElementById('emailError').textContent = 'Please enter a valid email address';
                isValid = false;
            } else {
                document.getElementById('emailError').textContent = '';
            }
            
            // Password validation
            if (password.value.length < 8) {
                document.getElementById('passwordError').textContent = 'Password must be at least 8 characters long';
                isValid = false;
            } else {
                document.getElementById('passwordError').textContent = '';
            }
            
            // Confirm password validation
            if (password.value !== confirmPassword.value) {
                document.getElementById('confirmPasswordError').textContent = 'Passwords do not match';
                isValid = false;
            } else {
                document.getElementById('confirmPasswordError').textContent = '';
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    // Email validation helper function
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
}); 