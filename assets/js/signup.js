document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.querySelector('.auth-form');
    
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const firstname = document.getElementById('firstname').value;
            const lastname = document.getElementById('lastname').value;
            const email = document.getElementById('email').value;
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            
            // Validate form
            if (!firstname || !lastname || !email || !username || !password) {
                alert('Please fill in all required fields');
                return;
            }
            
            if (password !== confirmPassword) {
                alert('Passwords do not match');
                return;
            }
            
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
                    window.location.href = '../pages/login.html';
                } else {
                    alert(data.message || 'Registration failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error during registration:', error);
                alert('An error occurred during registration. Please try again.');
            });
        });
    }
});