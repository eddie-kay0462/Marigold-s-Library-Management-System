// Check if styles are already added
if (!document.getElementById('loans-styles')) {
    const style = document.createElement('style');
    style.id = 'loans-styles';
    style.textContent = `
        .search-container {
            position: relative;
        }

        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }

        .search-result-item {
            padding: 10px 15px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-item:hover {
            background-color: #f8f9fa;
        }

        .result-main {
            display: flex;
            flex-direction: column;
        }

        .result-title {
            font-weight: 500;
            color: #2C3E50;
        }

        .result-subtitle {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .result-meta {
            font-size: 0.85rem;
        }

        .available-copies {
            background: #e8f5e9;
            color: #388e3c;
            padding: 2px 8px;
            border-radius: 12px;
        }

        .no-results {
            padding: 10px 15px;
            color: #6c757d;
            text-align: center;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .status-active {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .status-warning {
            background-color: #fff3e0;
            color: #f57c00;
        }
        
        .status-overdue {
            background-color: #ffebee;
            color: #d32f2f;
        }
    `;
    document.head.appendChild(style);
}

document.addEventListener('DOMContentLoaded', function() {
    const studentSearch = document.getElementById('borrow-student');
    const bookSearch = document.getElementById('borrow-book');
    const studentResults = document.getElementById('student-search-results');
    const bookResults = document.getElementById('book-search-results');
    const borrowForm = document.getElementById('borrow-form');
    const dueDateInput = document.getElementById('due-date');

    let selectedStudent = null;
    let selectedBook = null;

    // Set minimum date for due date input to today
    if (dueDateInput) {
        const today = new Date();
        const minDate = today.toISOString().split('T')[0];
        dueDateInput.min = minDate;
        
        // Set default due date to 14 days from today
        const defaultDueDate = new Date();
        defaultDueDate.setDate(defaultDueDate.getDate() + 14);
        dueDateInput.value = defaultDueDate.toISOString().split('T')[0];
    }

    // Student search
    if (studentSearch) {
        let studentDebounceTimer;
        studentSearch.addEventListener('input', (e) => {
            clearTimeout(studentDebounceTimer);
            const searchTerm = e.target.value.trim();
            
            if (searchTerm.length < 2) {
                studentResults.style.display = 'none';
                return;
            }

            studentDebounceTimer = setTimeout(() => {
                fetch(`students/search_students.php?query=${encodeURIComponent(searchTerm)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            displayStudentResults(data.students);
                        } else {
                            showError(data.message || 'Error searching for students');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showError('Failed to search for students');
                    });
            }, 300);
        });
    }

    // Book search
    if (bookSearch) {
        let bookDebounceTimer;
        bookSearch.addEventListener('input', (e) => {
            clearTimeout(bookDebounceTimer);
            const searchTerm = e.target.value.trim();
            
            if (searchTerm.length < 2) {
                bookResults.style.display = 'none';
                return;
            }

            bookDebounceTimer = setTimeout(() => {
                fetch(`books/search_books.php?query=${encodeURIComponent(searchTerm)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            displayBookResults(data.books);
                        } else {
                            showError(data.message || 'Error searching for books');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showError('Failed to search for books');
                    });
            }, 300);
        });
    }

    // Close search results when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.search-container')) {
            if (studentResults) studentResults.style.display = 'none';
            if (bookResults) bookResults.style.display = 'none';
        }
    });

    // Function to update book availability display
    function updateBookAvailability(bookId, change) {
        const bookItem = bookResults.querySelector(`[data-book-id="${bookId}"]`);
        if (bookItem) {
            const availableCopiesSpan = bookItem.querySelector('.available-copies');
            if (availableCopiesSpan) {
                const [available, total] = availableCopiesSpan.textContent
                    .match(/Available: (\d+) \/ Total: (\d+)/)
                    .slice(1)
                    .map(Number);
                const newAvailable = available + change;
                availableCopiesSpan.textContent = `Available: ${newAvailable} / Total: ${total}`;
                
                // Hide book if no copies available
                if (newAvailable <= 0) {
                    bookItem.remove();
                }
            }
        }
    }

    // Handle borrow form submission
    if (borrowForm) {
        borrowForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            if (!selectedStudent || !selectedBook || !dueDateInput.value) {
                showError('Please select both a student and a book, and set a due date');
                return;
            }

            const formData = new FormData();
            formData.append('student_id', selectedStudent.id);
            formData.append('book_id', selectedBook.id);
            formData.append('due_date', dueDateInput.value);

            fetch('loans/loan_handler.php?action=borrow', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Book borrowed successfully');
                    // Reset form
                    borrowForm.reset();
                    selectedStudent = null;
                    selectedBook = null;
                    // Set default due date
                    const defaultDueDate = new Date();
                    defaultDueDate.setDate(defaultDueDate.getDate() + 14);
                    dueDateInput.value = defaultDueDate.toISOString().split('T')[0];
                    // Refresh active loans table
                    loadActiveLoans();
                } else {
                    showError(data.message || 'Failed to borrow book');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Failed to borrow book');
            });
        });
    }

    // Load active loans when the page loads
    loadActiveLoans();
});

function loadActiveLoans() {
    const loansTableBody = document.getElementById('active-loans-table');
    if (!loansTableBody) return;

    fetch('loans/loan_handler.php?action=list_active')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.loans && data.loans.length > 0) {
                    loansTableBody.innerHTML = data.loans.map(loan => {
                        // Determine loan status and badge class
                        const today = new Date();
                        const dueDate = new Date(loan.due_date);
                        let statusClass = 'status-active';
                        let statusText = 'Active';

                        if (today > dueDate) {
                            statusClass = 'status-overdue';
                            statusText = 'Overdue';
                        } else if (today.toDateString() === dueDate.toDateString()) {
                            statusClass = 'status-warning';
                            statusText = 'Due Today';
                        }

                        return `
                            <tr data-loan-id="${loan.loan_id}" data-book-id="${loan.book_id}">
                                <td>${loan.student_name}</td>
                                <td>${loan.book_title}</td>
                                <td>${new Date(loan.loan_date).toLocaleDateString()}</td>
                                <td>${new Date(loan.due_date).toLocaleDateString()}</td>
                                <td>
                                    <span class="status-badge ${statusClass}">
                                        ${statusText}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-success btn-sm" onclick="returnBook(${loan.loan_id})">
                                        <i class="fas fa-check"></i> Return
                                    </button>
                                </td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    loansTableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center">
                                <div style="padding: 20px;">
                                    <i class="fas fa-info-circle" style="color: #666; margin-right: 10px;"></i>
                                    No active loans found
                                </div>
                            </td>
                        </tr>
                    `;
                }
            } else {
                showError(data.message || 'Failed to load active loans');
                loansTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center">
                            <div style="padding: 20px; color: #d32f2f;">
                                <i class="fas fa-exclamation-circle" style="margin-right: 10px;"></i>
                                Error loading loans
                            </div>
                        </td>
                    </tr>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to load active loans. Please check your connection and try again.');
            if (loansTableBody) {
                loansTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center">
                            <div style="padding: 20px; color: #d32f2f;">
                                <i class="fas fa-exclamation-circle" style="margin-right: 10px;"></i>
                                Error loading loans
                            </div>
                        </td>
                    </tr>
                `;
            }
        });
}

// Function to return a book
function returnBook(loanId) {
    if (!confirm('Are you sure you want to return this book?')) {
        return;
    }

    const loanRow = document.querySelector(`tr[data-loan-id="${loanId}"]`);
    if (!loanRow) {
        showError('Could not find loan record');
        return;
    }

    const bookId = loanRow.dataset.bookId;
    const formData = new FormData();
    formData.append('action', 'return');
    formData.append('loan_id', loanId);

    fetch('../pages/loans/loan_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showSuccess(data.message);
            // Update UI immediately
            loanRow.remove();
            updateBookAvailability(bookId, 1);
            
            // Check if table is empty
            const loansTableBody = document.getElementById('active-loans-table');
            if (loansTableBody.children.length === 0) {
                loansTableBody.innerHTML = '<tr><td colspan="6" class="text-center">No active loans</td></tr>';
            }
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error returning book');
    });
}

// Add this function to show success messages
function showSuccess(message) {
    // You can customize this based on your UI
    alert(message);
}

// Add this function to show error messages
function showError(message) {
    // You can customize this based on your UI
    alert(message);
}

function displayStudentResults(students) {
    if (!studentResults) return;
    
    if (students.length === 0) {
        studentResults.innerHTML = '<div class="search-result-item">No students found</div>';
        studentResults.style.display = 'block';
        return;
    }

    studentResults.innerHTML = students.map(student => `
        <div class="search-result-item" onclick="selectStudent('${student.student_id}', '${student.first_name} ${student.last_name}')">
            <div class="result-main">${student.first_name} ${student.last_name}</div>
            <div class="result-sub">ID: ${student.student_number}</div>
        </div>
    `).join('');
    
    studentResults.style.display = 'block';
}

function displayBookResults(books) {
    if (!bookResults) return;
    
    if (books.length === 0) {
        bookResults.innerHTML = '<div class="search-result-item">No books found</div>';
        bookResults.style.display = 'block';
        return;
    }

    bookResults.innerHTML = books.map(book => `
        <div class="search-result-item" onclick="selectBook('${book.book_id}', '${book.title}')">
            <div class="result-main">${book.title}</div>
            <div class="result-sub">
                <span>By: ${book.author}</span>
                <span class="availability-badge ${book.available_copies > 0 ? 'available' : 'unavailable'}">
                    ${book.available_copies} available
                </span>
            </div>
        </div>
    `).join('');
    
    bookResults.style.display = 'block';
}

function selectStudent(studentId, studentName) {
    selectedStudent = { id: studentId, name: studentName };
    studentResults.style.display = 'none';
}

function selectBook(bookId, bookTitle) {
    selectedBook = { id: bookId, title: bookTitle };
    bookResults.style.display = 'none';
}

// Add these styles to the page
const style = document.createElement('style');
style.textContent = `
    .search-container {
        position: relative;
        margin-bottom: 20px;
    }

    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
    }

    .search-result-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
        transition: background-color 0.2s ease;
    }

    .search-result-item:last-child {
        border-bottom: none;
    }

    .search-result-item:hover {
        background-color: #f5f5f5;
    }

    .result-main {
        font-weight: 500;
        color: #2c3e50;
    }

    .result-sub {
        font-size: 0.85em;
        color: #666;
        margin-top: 4px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .availability-badge {
        padding: 2px 6px;
        border-radius: 12px;
        font-size: 0.8em;
        font-weight: 500;
    }

    .availability-badge.available {
        background-color: #e8f5e9;
        color: #388e3c;
    }

    .availability-badge.unavailable {
        background-color: #ffebee;
        color: #d32f2f;
    }
`;
document.head.appendChild(style); 