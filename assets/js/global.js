// This file contains common functions used across multiple pages

// Check if user is logged in
function checkAuth() {
    fetch('../api/auth.php')
        .then(response => response.json())
        .then(data => {
            if (!data.loggedIn) {
                window.location.href = '../pages/login.php';
            }
        })
        .catch(error => {
            console.error('Error checking auth:', error);
            window.location.href = '../pages/login.php';
        });
}

// Format date to YYYY-MM-DD
function formatDate(date) {
    const d = new Date(date);
    let month = '' + (d.getMonth() + 1);
    let day = '' + d.getDate();
    const year = d.getFullYear();

    if (month.length < 2) 
        month = '0' + month;
    if (day.length < 2) 
        day = '0' + day;

    return [year, month, day].join('-');
}

// Calculate due date based on loan duration
function calculateDueDate(loanDuration) {
    const dueDate = new Date();
    dueDate.setDate(dueDate.getDate() + parseInt(loanDuration || 14));
    return formatDate(dueDate);
}

// Show notification message
function showNotification(message, type = 'info') {
    // Create notification element if it doesn't exist
    let notification = document.getElementById('notification');
    if (!notification) {
        notification = document.createElement('div');
        notification.id = 'notification';
        document.body.appendChild(notification);
    }
    
    // Set notification content and type
    notification.textContent = message;
    notification.className = `notification ${type}`;
    
    // Show notification
    notification.style.display = 'block';
    
    // Hide notification after 3 seconds
    setTimeout(() => {
        notification.style.display = 'none';
    }, 3000);
}

// Log out user
function logout() {
    fetch('../api/auth.php?logout=1')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '../index.php';
            }
        })
        .catch(error => console.error('Error during logout:', error));
}

// Display current user info
function displayUserInfo() {
    fetch('../api/auth.php?user_info=1')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const userInfoElement = document.getElementById('user-info');
                if (userInfoElement) {
                    userInfoElement.textContent = `Logged in as: ${data.user.name} (${data.user.role})`;
                }
            }
        })
        .catch(error => console.error('Error getting user info:', error));
}

// Add event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add logout event listener
    const logoutButton = document.getElementById('logout-btn');
    if (logoutButton) {
        logoutButton.addEventListener('click', function(e) {
            e.preventDefault();
            logout();
        });
    }
    
    // Display user info if on a protected page
    if (document.body.classList.contains('protected-page')) {
        displayUserInfo();
        checkAuth(); // Check if user is logged in
    }
});