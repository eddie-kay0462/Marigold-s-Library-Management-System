document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('.auth-form');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                alert('Please enter both username and password');
                return;
            }
            
            const formData = new FormData();
            formData.append('username', username);
            formData.append('password', password);
            formData.append('login', '1');
            
            fetch('../api/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to dashboard on successful login
                    window.location.href = '../pages/dashboard.html';
                } else {
                    alert(data.message || 'Login failed. Please check your credentials.');
                }
            })
            .catch(error => {
                console.error('Error during login:', error);
                alert('An error occurred during login. Please try again.');
            });
        });
    }
});