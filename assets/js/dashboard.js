document.addEventListener('DOMContentLoaded', function() {
    // Load users when page loads
    loadUsers();
    
    // Load books when page loads
    loadBooks();
    
    // Add event listener for Add Book button
    const addBookBtn = document.getElementById('add-book-btn');
    if (addBookBtn) {
        addBookBtn.addEventListener('click', function() {
            addBook();
        });
    }
    
    // Set up navigation
    const sidebarLinks = document.querySelectorAll('.sidebar-menu a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all links
            sidebarLinks.forEach(l => l.classList.remove('active'));
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Hide all sections
            document.querySelectorAll('.dashboard-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Show the target section
            const targetId = this.getAttribute('href').substring(1);
            const targetSection = document.getElementById(targetId);
            if (targetSection) {
                targetSection.classList.add('active');
            }
        });
    });
});

// Load users
function loadUsers() {
    fetch('../api/users.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const usersTable = document.querySelector('#users table tbody');
                if (!usersTable) return;
                
                usersTable.innerHTML = '';
                
                if (data.data.length > 0) {
                    data.data.forEach(user => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                            <td>${user.id}</td>
                            <td>${user.firstname} ${user.lastname}</td>
                            <td>${getRoleName(user.role_id)}</td>
                            <td>${user.email}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary" onclick="editUser('${user.id}')">
                                <i class="fas fa-edit"></i>
                            </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteUser('${user.id}')">
                                    <i class="fas fa-trash"></i>
                            </button>
                                <button type="button" class="btn btn-sm btn-info" onclick="viewUser('${user.id}')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    `;
                        usersTable.appendChild(row);
                });
            } else {
                    usersTable.innerHTML = '<tr><td colspan="5" class="text-center">No users found</td></tr>';
                }
            }
        })
        .catch(error => console.error('Error loading users:', error));
}

// Edit user
function editUser(id) {
    // Show loading state
    Swal.fire({
        title: 'Loading...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Fetch user data
    fetch(`../api/users.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.data;
                
                // Show user details in a modal
                Swal.fire({
                    title: 'Edit User',
                    html: `
                        <form id="edit-user-form">
                            <input type="hidden" id="edit-user-id" value="${user.id}">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" class="form-control" id="edit-username" value="${user.username}" readonly>
                            </div>
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" class="form-control" id="edit-firstname" value="${user.firstname}">
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" class="form-control" id="edit-lastname" value="${user.lastname}">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" id="edit-email" value="${user.email}">
                            </div>
                        </form>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Save Changes',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        const formData = new FormData();
                        formData.append('id', document.getElementById('edit-user-id').value);
                        formData.append('firstname', document.getElementById('edit-firstname').value);
                        formData.append('lastname', document.getElementById('edit-lastname').value);
                        formData.append('email', document.getElementById('edit-email').value);
                        
                        return fetch('../api/users.php', {
                            method: 'PUT',
                            body: formData
                        })
        .then(response => response.json())
        .then(data => {
                            if (!data.success) {
                                throw new Error(data.message || 'Failed to update user');
                            }
                            return data;
                        });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'User updated successfully',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => loadUsers());
                    }
                }).catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: error.message || 'Failed to update user'
                    });
                });
            } else {
                throw new Error(data.message || 'Failed to load user data');
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message || 'An error occurred while loading user data.'
            });
        });
}

// Delete user
function deleteUser(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Send delete request
            fetch(`../api/users.php?id=${id}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'User has been deleted.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        loadUsers();
                    });
            } else {
                    throw new Error(data.message || 'Failed to delete user');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error.message || 'An error occurred while deleting the user.'
                });
            });
        }
    });
}

// View user details
function viewUser(id) {
    // Show loading state
    Swal.fire({
        title: 'Loading...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Fetch user data
    fetch(`../api/users.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.data;
                
                // Show user details in a modal
                Swal.fire({
                    title: 'User Details',
                    html: `
                        <div class="text-start">
                            <p><strong>Username:</strong> ${user.username}</p>
                            <p><strong>Name:</strong> ${user.firstname} ${user.lastname}</p>
                            <p><strong>Email:</strong> ${user.email}</p>
                            <p><strong>Role:</strong> ${getRoleName(user.role_id)}</p>
                            <p><strong>Status:</strong> ${user.status ? 'Active' : 'Inactive'}</p>
                        </div>
                    `,
                    showCloseButton: true,
                    showConfirmButton: false
                });
            } else {
                throw new Error(data.message || 'Failed to load user data');
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message || 'An error occurred while loading user data.'
            });
        });
}

// Helper function to get role name
function getRoleName(roleId) {
    const roles = {
        1: 'Administrator',
        2: 'Librarian',
        3: 'Staff'
    };
    return roles[roleId] || 'Unknown';
}

// Load books
function loadBooks() {
    fetch('../api/books.php')
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector('#books table tbody');
            if (!tableBody) return;

            tableBody.innerHTML = ''; // Clear the table body

            if (data.success && data.data && data.data.length > 0) {
                // Sort books by ID in ascending order
                const sortedBooks = data.data.sort((a, b) => a.book_id - b.book_id);
                
                sortedBooks.forEach(book => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${book.book_id}</td>
                        <td>${book.isbn}</td>
                        <td>${book.title}</td>
                        <td>${book.author}</td>
                        <td>${book.category_name || 'N/A'}</td>
                        <td>${book.available_copies}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary" onclick="editBook('${book.book_id}')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteBook('${book.book_id}')">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-info" onclick="viewBook('${book.book_id}')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            } else {
                tableBody.innerHTML = '<tr><td colspan="7" class="text-center">No books found</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading books:', error);
            const tableBody = document.querySelector('#books table tbody');
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading books</td></tr>';
            }
        });
}

// Add this function at the top level
function updateDashboardStats() {
    fetch('../api/dashboard_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update all stat cards
                document.querySelector('.stat-card:nth-child(1) .number').textContent = data.active_members;
                document.querySelector('.stat-card:nth-child(2) .number').textContent = data.students;
                document.querySelector('.stat-card:nth-child(3) .number').textContent = data.staff;
                document.querySelector('.stat-card:nth-child(4) .number').textContent = data.books;
            }
        })
        .catch(error => console.error('Error updating stats:', error));
}

// Add book
function addBook() {
    // First fetch categories
    fetch('../api/categories.php')
        .then(response => response.json())
        .then(categoryData => {
            if (!categoryData.success) {
                throw new Error(categoryData.message || 'Failed to load categories');
            }

            const categories = categoryData.data || [];
            const categoryOptions = categories.map(category => 
                `<option value="${category.category_id}">${category.name}</option>`
            ).join('');

            // Show the add book form
            Swal.fire({
                title: 'Add New Book',
                html: `
                    <form id="add-book-form" class="text-start">
                        <div class="form-group mb-3">
                            <label>ISBN</label>
                            <input type="text" id="add-isbn" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label>Title</label>
                            <input type="text" id="add-title" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label>Author</label>
                            <input type="text" id="add-author" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label>Category</label>
                            <select id="add-category" class="form-control" required>
                                <option value="">Select Category</option>
                                ${categoryOptions}
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label>Total Copies</label>
                            <input type="number" id="add-total-copies" class="form-control" min="1" value="1" required>
                        </div>
                    </form>
                `,
                showCancelButton: true,
                confirmButtonText: 'Add Book',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    // Validate form fields
                    const isbn = document.getElementById('add-isbn').value.trim();
                    const title = document.getElementById('add-title').value.trim();
                    const author = document.getElementById('add-author').value.trim();
                    const categoryId = document.getElementById('add-category').value;
                    const totalCopies = document.getElementById('add-total-copies').value;

                    // Validate required fields
                    if (!isbn || !title || !author || !categoryId || !totalCopies) {
                        Swal.showValidationMessage('Please fill in all required fields');
                        return false;
                    }

                    // Create form data
                    const formData = new FormData();
                    formData.append('isbn', isbn);
                    formData.append('title', title);
                    formData.append('author', author);
                    formData.append('category_id', categoryId);
                    formData.append('total_copies', totalCopies);
                    formData.append('available_copies', totalCopies);
                    
                    // Send request
                    return fetch('../api/books.php', {
                        method: 'POST',
                        body: formData
        })
        .then(response => response.json())
        .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || 'Failed to add book');
                        }
                        return data;
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Book added successfully',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        loadBooks();
                        updateDashboardStats();
                    });
                }
            });
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message || 'Failed to process request'
            });
        });
}

// Edit book
function editBook(id) {
    // First fetch categories
    Promise.all([
        fetch(`../api/books.php?id=${id}`),
        fetch('../api/categories.php')
    ])
    .then(responses => Promise.all(responses.map(response => response.json())))
    .then(([bookData, categoryData]) => {
        if (!bookData.success || !categoryData.success) {
            throw new Error(bookData.message || categoryData.message || 'Failed to load data');
        }

        const book = bookData.data;
        const categories = categoryData.data || [];
        const categoryOptions = categories.map(category => 
            `<option value="${category.category_id}" ${book.category_id == category.category_id ? 'selected' : ''}>${category.name}</option>`
        ).join('');

        Swal.fire({
            title: 'Edit Book',
            html: `
                <form id="edit-book-form" class="text-start">
                    <div class="form-group mb-3">
                        <label>ISBN</label>
                        <input type="text" id="edit-isbn" class="form-control" value="${book.isbn}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Title</label>
                        <input type="text" id="edit-title" class="form-control" value="${book.title}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Author</label>
                        <input type="text" id="edit-author" class="form-control" value="${book.author}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Category</label>
                        <select id="edit-category" class="form-control" required>
                            <option value="">Select Category</option>
                            ${categoryOptions}
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>Total Copies</label>
                        <input type="number" id="edit-total-copies" class="form-control" value="${book.total_copies}" min="1" required>
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Save Changes',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                // Validate form fields
                const isbn = document.getElementById('edit-isbn').value.trim();
                const title = document.getElementById('edit-title').value.trim();
                const author = document.getElementById('edit-author').value.trim();
                const categoryId = document.getElementById('edit-category').value;
                const totalCopies = document.getElementById('edit-total-copies').value;

                // Validate required fields
                if (!isbn || !title || !author || !categoryId || !totalCopies) {
                    Swal.showValidationMessage('Please fill in all required fields');
                    return false;
                }

                // Create URL-encoded body
                const params = new URLSearchParams();
                params.append('isbn', isbn);
                params.append('title', title);
                params.append('author', author);
                params.append('category_id', categoryId);
                params.append('total_copies', totalCopies);
                params.append('available_copies', totalCopies);
                
                // Send request
                return fetch(`../api/books.php?id=${id}`, {
                    method: 'PUT',
                    body: params.toString(),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Failed to update book');
                    }
                    return data;
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Book updated successfully',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => loadBooks());
            }
        }).catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message || 'Failed to update book'
            });
        });
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Failed to load book details'
        });
    });
}

// Delete book
function deleteBook(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`../api/books.php?id=${id}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Book has been deleted.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        loadBooks();
                        updateDashboardStats();
                    });
                } else {
                    throw new Error(data.message || 'Failed to delete book');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error.message || 'Failed to delete book'
                });
            });
        }
    });
}

// View book details
function viewBook(id) {
    fetch(`../api/books.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                const book = data.data;
                Swal.fire({
                    title: 'Book Details',
                    html: `
                        <div class="text-start">
                            <p><strong>Book ID:</strong> ${book.book_id}</p>
                            <p><strong>ISBN:</strong> ${book.isbn}</p>
                            <p><strong>Title:</strong> ${book.title}</p>
                            <p><strong>Author:</strong> ${book.author}</p>
                            <p><strong>Category:</strong> ${book.category_name || 'N/A'}</p>
                            <p><strong>Available Copies:</strong> ${book.available_copies}</p>
                            <p><strong>Description:</strong> ${book.description || 'No description available'}</p>
                        </div>
                    `,
                    showCloseButton: true,
                    showConfirmButton: false
                });
                } else {
                throw new Error(data.message || 'Failed to load book details');
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message || 'Failed to load book details'
            });
        });
} 