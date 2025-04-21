// DOM Elements
const userSearch = document.getElementById('user-search');
const usersTableBody = document.getElementById('users-table-body');

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
    
    // Search functionality
    userSearch.addEventListener('input', debounce((e) => {
        loadUsers(e.target.value.trim());
    }, 300));
});

// Load users
function loadUsers(searchQuery = '') {
    const url = searchQuery
        ? `../pages/users/user_handler.php?action=search&query=${encodeURIComponent(searchQuery)}`
        : '../pages/users/user_handler.php?action=list';

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                usersTableBody.innerHTML = data.data.map(user => `
                    <tr>
                        <td>${user.user_id}</td>
                        <td>${user.first_name} ${user.last_name}</td>
                        <td>${user.email}</td>
                    </tr>
                `).join('');
            } else {
                showError('Error loading users');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Error loading users');
        });
}

// Utility function for debouncing
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const context = this;
        const later = () => {
            clearTimeout(timeout);
            func.apply(context, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
} 