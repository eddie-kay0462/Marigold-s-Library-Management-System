// Update current date
document.addEventListener('DOMContentLoaded', function() {
    const currentDate = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('current-date').textContent = currentDate.toLocaleDateString('en-US', options);
    
    // Load initial data for dashboard
    loadDashboardStats();
    loadRecentActivities();
    
    // Set up event listeners for forms
    setupFormListeners();
});

// Sidebar navigation
document.addEventListener('DOMContentLoaded', function() {
    const menuItems = document.querySelectorAll('.sidebar-menu a');
    const sections = document.querySelectorAll('.dashboard-section');
    
    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all menu items
            menuItems.forEach(i => i.classList.remove('active'));
            
            // Add active class to clicked menu item
            this.classList.add('active');
            
            // Hide all sections
            sections.forEach(section => section.classList.remove('active'));
            
            // Show the corresponding section
            const targetId = this.getAttribute('href').substring(1);
            const targetSection = document.getElementById(targetId);
            
            if (targetSection) {
                targetSection.classList.add('active');
                
                // Load appropriate data for the selected section
                if (targetId === 'books') {
                    loadBooks();
                } else if (targetId === 'students') {
                    loadStudents();
                } else if (targetId === 'loans') {
                    loadActiveLoans();
                } else if (targetId === 'reports') {
                    loadReports();
                }
            }
        });
    });
});

// ===== DASHBOARD OVERVIEW FUNCTIONS =====

// Load dashboard statistics
function loadDashboardStats() {
    fetch('../api/dashboard.php?action=stats')
        .then(response => response.json())
        .then(data => {
            // Update stats on the dashboard
            if (data.success) {
                document.querySelector('.stat-card:nth-child(1) .number').textContent = data.totalBooks;
                document.querySelector('.stat-card:nth-child(2) .number').textContent = data.activeMembers;
                document.querySelector('.stat-card:nth-child(3) .number').textContent = data.booksLoaned;
                document.querySelector('.stat-card:nth-child(4) .number').textContent = data.overdueBooks;
            }
        })
        .catch(error => console.error('Error loading dashboard stats:', error));
}

// Load recent activities
function loadRecentActivities() {
    fetch('../api/dashboard.php?action=activities')
        .then(response => response.json())
        .then(data => {
            const activitiesTable = document.querySelector('#overview table tbody');
            activitiesTable.innerHTML = '';
            
            if (data.length > 0) {
                data.forEach(activity => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${activity.date}</td>
                        <td>${activity.activity}</td>
                        <td>${activity.member || '-'}</td>
                        <td>${activity.book || '-'}</td>
                    `;
                    activitiesTable.appendChild(row);
                });
            } else {
                activitiesTable.innerHTML = `<tr><td colspan="4">No recent activities found</td></tr>`;
            }
        })
        .catch(error => console.error('Error loading recent activities:', error));
}

// ===== BOOKS MANAGEMENT FUNCTIONS =====

// Load all books
function loadBooks() {
    fetch('../api/books.php')
        .then(response => response.json())
        .then(data => {
            const booksTable = document.querySelector('#books table tbody');
            booksTable.innerHTML = '';
            
            if (data.length > 0) {
                data.forEach(book => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${book.id}</td>
                        <td>${book.isbn}</td>
                        <td>${book.title}</td>
                        <td>${book.author}</td>
                        <td>${book.category}</td>
                        <td>${book.available}</td>
                        <td>
                            <button type="button" class="btn btn-secondary" onclick="editBook('${book.id}')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger" onclick="deleteBook('${book.id}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            <button type="button" class="btn btn-info" onclick="viewBook('${book.id}')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    `;
                    booksTable.appendChild(row);
                });
            } else {
                booksTable.innerHTML = `<tr><td colspan="7">No books found</td></tr>`;
            }
        })
        .catch(error => console.error('Error loading books:', error));
}

// Add a new book
function addBook() {
    openModal('book-form-modal');
    document.getElementById('book-form-title').textContent = 'Add New Book';
    document.getElementById('book-form').reset();
}

// Edit a book
function editBook(id) {
    fetch(`../api/books.php?id=${id}`)
        .then(response => response.json())
        .then(book => {
            if (book) {
                openModal('book-form-modal');
                document.getElementById('book-form-title').textContent = 'Edit Book';
                
                // Populate form fields
                document.getElementById('book-id').value = book.id;
                document.getElementById('book-isbn').value = book.isbn;
                document.getElementById('book-title').value = book.title;
                document.getElementById('book-author').value = book.author;
                document.getElementById('book-category').value = book.category_id;
                document.getElementById('book-copies').value = book.available_copies;
                document.getElementById('book-description').value = book.description || '';
            }
        })
        .catch(error => console.error('Error fetching book details:', error));
}

// Delete a book
function deleteBook(id) {
    if (confirm('Are you sure you want to delete this book?')) {
        fetch(`../api/books.php?id=${id}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Book deleted successfully');
                loadBooks();
            } else {
                alert('Failed to delete book: ' + data.message);
            }
        })
        .catch(error => console.error('Error deleting book:', error));
    }
}

// View book details
function viewBook(id) {
    fetch(`../api/books.php?id=${id}`)
        .then(response => response.json())
        .then(book => {
            if (book) {
                // Implement book details view logic here
                // You can open a modal or navigate to a details page
                alert(`Book: ${book.title} by ${book.author}`);
            }
        })
        .catch(error => console.error('Error fetching book details:', error));
}

// ===== STUDENTS MANAGEMENT FUNCTIONS =====

// Load all students
function loadStudents() {
    fetch('../api/students.php')
        .then(response => response.json())
        .then(data => {
            const studentsTable = document.querySelector('#students table tbody');
            studentsTable.innerHTML = '';
            
            if (data.length > 0) {
                data.forEach(student => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${student.id}</td>
                        <td>${student.name}</td>
                        <td>${student.borrowed_books || 0}</td>
                        <td>
                            <button type="button" class="btn btn-secondary" onclick="editStudent('${student.id}')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger" onclick="deleteStudent('${student.id}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            <button type="button" class="btn btn-info" onclick="viewStudent('${student.id}')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    `;
                    studentsTable.appendChild(row);
                });
            } else {
                studentsTable.innerHTML = `<tr><td colspan="4">No students found</td></tr>`;
            }
        })
        .catch(error => console.error('Error loading students:', error));
}

// Add a new student
function addStudent() {
    openModal('student-form-modal');
    document.getElementById('student-form-title').textContent = 'Register New Student';
    document.getElementById('student-form').reset();
}

// Edit a student
function editStudent(id) {
    fetch(`../api/students.php?id=${id}`)
        .then(response => response.json())
        .then(student => {
            if (student) {
                openModal('student-form-modal');
                document.getElementById('student-form-title').textContent = 'Edit Student';
                
                // Populate form fields
                document.getElementById('student-id').value = student.id;
                document.getElementById('student-name').value = student.name;
                document.getElementById('student-email').value = student.email;
                document.getElementById('student-phone').value = student.phone || '';
                document.getElementById('student-address').value = student.address || '';
            }
        })
        .catch(error => console.error('Error fetching student details:', error));
}

// Delete a student
function deleteStudent(id) {
    if (confirm('Are you sure you want to delete this student?')) {
        fetch(`../api/students.php?id=${id}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Student deleted successfully');
                loadStudents();
            } else {
                alert('Failed to delete student: ' + data.message);
            }
        })
        .catch(error => console.error('Error deleting student:', error));
    }
}

// View student details
function viewStudent(id) {
    fetch(`../api/students.php?id=${id}`)
        .then(response => response.json())
        .then(student => {
            if (student) {
                // Implement student details view logic here
                alert(`Student: ${student.name}, Email: ${student.email}`);
            }
        })
        .catch(error => console.error('Error fetching student details:', error));
}

// ===== LOANS MANAGEMENT FUNCTIONS =====

// Load all active loans
function loadActiveLoans() {
    fetch('../api/loans.php')
        .then(response => response.json())
        .then(data => {
            const loansTable = document.querySelector('#loans table tbody');
            loansTable.innerHTML = '';
            
            if (data.length > 0) {
                data.forEach(loan => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${loan.book_id}</td>
                        <td>${loan.title}</td>
                        <td>${loan.student}</td>
                        <td>${loan.loan_date}</td>
                        <td>${loan.due_date}</td>
                        <td><span class="status-badge status-${loan.status.toLowerCase()}">${loan.status}</span></td>
                        <td>
                            <button type="button" class="btn btn-primary" onclick="returnBook('${loan.id}')">
                                <i class="fas fa-check"></i>
                            </button>
                        </td>
                    `;
                    loansTable.appendChild(row);
                });
            } else {
                loansTable.innerHTML = `<tr><td colspan="7">No active loans found</td></tr>`;
            }
        })
        .catch(error => console.error('Error loading active loans:', error));
}

// Borrow a book
function borrowBook() {
    const form = document.getElementById('borrow-form');
    const formData = new FormData(form);
    formData.append('borrow', '1');
    
    fetch('../api/loans.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Book borrowed successfully');
            form.reset();
            loadActiveLoans();
        } else {
            alert('Failed to borrow book: ' + data.message);
        }
    })
    .catch(error => console.error('Error borrowing book:', error));
}

// Return a book
function returnBook(id) {
    if (confirm('Are you sure you want to return this book?')) {
        const formData = new FormData();
        formData.append('return', '1');
        formData.append('loan_id', id);
        
        fetch('../api/loans.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Book returned successfully');
                loadActiveLoans();
            } else {
                alert('Failed to return book: ' + data.message);
            }
        })
        .catch(error => console.error('Error returning book:', error));
    }
}

// ===== USER MANAGEMENT FUNCTIONS =====

// Load all users
function loadUsers() {
    fetch('../api/users.php')
        .then(response => response.json())
        .then(data => {
            const usersTable = document.querySelector('#users table tbody');
            usersTable.innerHTML = '';
            
            if (data.length > 0) {
                data.forEach(user => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${user.id}</td>
                        <td>${user.name}</td>
                        <td>${user.role}</td>
                        <td>${user.email}</td>
                        <td>
                            <button type="button" class="btn btn-secondary" onclick="editUser('${user.id}')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger" onclick="deleteUser('${user.id}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            <button type="button" class="btn btn-info" onclick="viewUser('${user.id}')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    `;
                    usersTable.appendChild(row);
                });
            } else {
                usersTable.innerHTML = `<tr><td colspan="5">No users found</td></tr>`;
            }
        })
        .catch(error => console.error('Error loading users:', error));
}

// Add a new user
function addUser() {
    openModal('user-form-modal');
    document.getElementById('user-form-title').textContent = 'Add New User';
    document.getElementById('user-form').reset();
}

// Edit a user
function editUser(id) {
    fetch(`../api/users.php?id=${id}`)
        .then(response => response.json())
        .then(user => {
            if (user) {
                openModal('user-form-modal');
                document.getElementById('user-form-title').textContent = 'Edit User';
                
                // Populate form fields
                document.getElementById('user-id').value = user.id;
                document.getElementById('user-name').value = user.name;
                document.getElementById('user-role').value = user.role_id;
                document.getElementById('user-email').value = user.email;
                // Password fields left blank for editing
            }
        })
        .catch(error => console.error('Error fetching user details:', error));
}

// Delete a user
function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch(`../api/users.php?id=${id}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User deleted successfully');
                loadUsers();
            } else {
                alert('Failed to delete user: ' + data.message);
            }
        })
        .catch(error => console.error('Error deleting user:', error));
    }
}

// View user details
function viewUser(id) {
    fetch(`../api/users.php?id=${id}`)
        .then(response => response.json())
        .then(user => {
            if (user) {
                // Implement user details view logic here
                alert(`User: ${user.name}, Role: ${user.role}`);
            }
        })
        .catch(error => console.error('Error fetching user details:', error));
}

// ===== REPORTS FUNCTIONS =====

// Load reports data
function loadReports() {
    // You can implement reports loading based on your needs
    // e.g., popular books, monthly loans, etc.
    console.log('Loading reports data');
}

// ===== UTILITY FUNCTIONS =====

// Modal functionality
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInputs = document.querySelectorAll('input[type="text"][id$="-search"]');
    
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const table = this.closest('.card').querySelector('table');
            if (!table) return;
            
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    });
});

// Form submission handlers
function setupFormListeners() {
    // Book form submission
    const bookForm = document.getElementById('book-form');
    if (bookForm) {
        bookForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../api/books.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Book saved successfully');
                    closeModal('book-form-modal');
                    loadBooks();
                } else {
                    alert('Failed to save book: ' + data.message);
                }
            })
            .catch(error => console.error('Error saving book:', error));
        });
    }
    
    // Student form submission
    const studentForm = document.getElementById('student-form');
    if (studentForm) {
        studentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../api/students.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Student saved successfully');
                    closeModal('student-form-modal');
                    loadStudents();
                } else {
                    alert('Failed to save student: ' + data.message);
                }
            })
            .catch(error => console.error('Error saving student:', error));
        });
    }
    
    // Borrow form submission
    const borrowForm = document.getElementById('borrow-form');
    if (borrowForm) {
        borrowForm.addEventListener('submit', function(e) {
            e.preventDefault();
            borrowBook();
        });
    }
    
    // User form submission
    const userForm = document.getElementById('user-form');
    if (userForm) {
        userForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../api/users.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User saved successfully');
                    closeModal('user-form-modal');
                    loadUsers();
                } else {
                    alert('Failed to save user: ' + data.message);
                }
            })
            .catch(error => console.error('Error saving user:', error));
        });
    }
}

// Load data on initial page load
document.addEventListener('DOMContentLoaded', function() {
    // Load data for the initially active section
    const activeSection = document.querySelector('.dashboard-section.active');
    if (activeSection) {
        const sectionId = activeSection.id;
        if (sectionId === 'books') {
            loadBooks();
        } else if (sectionId === 'students') {
            loadStudents();
        } else if (sectionId === 'loans') {
            loadActiveLoans();
        } else if (sectionId === 'users') {
            loadUsers();
        }
    }
});