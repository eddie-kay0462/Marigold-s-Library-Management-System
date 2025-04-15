// Load students when page loads
function loadStudents() {
    fetch('../api/students.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const studentsTable = document.querySelector('#students table tbody');
                if (!studentsTable) return;
                
                studentsTable.innerHTML = '';
                
                if (data.data.length > 0) {
                    data.data.forEach(student => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${student.student_number}</td>
                            <td>${student.first_name} ${student.last_name}</td>
                            <td>${student.borrowed_books || 0}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary" onclick="editStudent('${student.student_id}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteStudent('${student.student_id}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-info" onclick="viewStudent('${student.student_id}')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        `;
                        studentsTable.appendChild(row);
                    });
                } else {
                    studentsTable.innerHTML = '<tr><td colspan="4" class="text-center">No students found</td></tr>';
                }
            }
        })
        .catch(error => console.error('Error loading students:', error));
}

// Add student
function addStudent() {
    Swal.fire({
        title: 'Add New Student',
        html: `
            <form id="add-student-form" class="text-start">
                <div class="form-group mb-3">
                    <label>Student Number</label>
                    <input type="text" id="add-student-number" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label>First Name</label>
                    <input type="text" id="add-first-name" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label>Last Name</label>
                    <input type="text" id="add-last-name" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label>Email</label>
                    <input type="email" id="add-email" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label>Date of Birth</label>
                    <input type="date" id="add-date-of-birth" class="form-control" required>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Add Student',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const formData = new FormData();
            formData.append('student_number', document.getElementById('add-student-number').value);
            formData.append('first_name', document.getElementById('add-first-name').value);
            formData.append('last_name', document.getElementById('add-last-name').value);
            formData.append('email', document.getElementById('add-email').value);
            formData.append('date_of_birth', document.getElementById('add-date-of-birth').value);
            
            return fetch('../api/students.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to add student');
                }
                return data;
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Student added successfully',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                loadStudents();
                updateDashboardStats();
            });
        }
    }).catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.message || 'Failed to add student'
        });
    });
}

// Edit student
function editStudent(id) {
    fetch(`../api/students.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const student = data.data;
                
                Swal.fire({
                    title: 'Edit Student',
                    html: `
                        <form id="edit-student-form" class="text-start">
                            <input type="hidden" id="edit-student-id" value="${student.student_id}">
                            <div class="form-group mb-3">
                                <label>Student Number (Format: M-XXX)</label>
                                <input type="text" id="edit-student-number" class="form-control" value="${student.student_number}" pattern="M-[0-9]{3}" placeholder="M-001" required>
                                <small class="text-muted">Example: M-001, M-002, etc.</small>
                            </div>
                            <div class="form-group mb-3">
                                <label>First Name</label>
                                <input type="text" id="edit-first-name" class="form-control" value="${student.first_name}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label>Last Name</label>
                                <input type="text" id="edit-last-name" class="form-control" value="${student.last_name}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label>Email</label>
                                <input type="email" id="edit-email" class="form-control" value="${student.email}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label>Date of Birth</label>
                                <input type="date" id="edit-date-of-birth" class="form-control" value="${student.date_of_birth}" required>
                            </div>
                        </form>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Save Changes',
                    showLoaderOnConfirm: true,
                    didOpen: () => {
                        // Add input validation for student number
                        const studentNumberInput = document.getElementById('edit-student-number');
                        studentNumberInput.addEventListener('input', function() {
                            const isValid = this.value.match(/^M-\d{3}$/);
                            this.setCustomValidity(isValid ? '' : 'Student number must be in format M-XXX (e.g., M-001)');
                        });
                    },
                    preConfirm: () => {
                        const studentNumber = document.getElementById('edit-student-number').value;
                        if (!studentNumber.match(/^M-\d{3}$/)) {
                            Swal.showValidationMessage('Student number must be in format M-XXX (e.g., M-001)');
                            return false;
                        }

                        const formData = new URLSearchParams();
                        formData.append('student_number', studentNumber);
                        formData.append('first_name', document.getElementById('edit-first-name').value);
                        formData.append('last_name', document.getElementById('edit-last-name').value);
                        formData.append('email', document.getElementById('edit-email').value);
                        formData.append('date_of_birth', document.getElementById('edit-date-of-birth').value);
                        
                        return fetch(`../api/students.php?id=${id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: formData.toString()
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                throw new Error(data.message || 'Failed to update student');
                            }
                            return data;
                        });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Student updated successfully',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => loadStudents());
                    }
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message || 'Failed to load student data'
            });
        });
}

// View student details
function viewStudent(id) {
    fetch(`../api/students.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const student = data.data;
                
                Swal.fire({
                    title: 'Student Details',
                    html: `
                        <div class="text-start">
                            <p><strong>Student ID:</strong> ${student.student_id}</p>
                            <p><strong>Student Number:</strong> ${student.student_number}</p>
                            <p><strong>Name:</strong> ${student.first_name} ${student.last_name}</p>
                            <p><strong>Email:</strong> ${student.email}</p>
                            <p><strong>Date of Birth:</strong> ${student.date_of_birth}</p>
                            <p><strong>Registration Date:</strong> ${student.registration_date}</p>
                            <p><strong>Borrowed Books:</strong> ${student.borrowed_books || 0}</p>
                            <p><strong>Status:</strong> ${student.is_active ? 'Active' : 'Inactive'}</p>
                        </div>
                    `,
                    showCloseButton: true,
                    showConfirmButton: false
                });
            } else {
                throw new Error(data.message || 'Failed to load student data');
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message || 'Failed to load student data'
            });
        });
}

// Delete student
function deleteStudent(id) {
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
            fetch(`../api/students.php?id=${id}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Student has been deleted.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        loadStudents();
                        updateDashboardStats();
                    });
                } else {
                    throw new Error(data.message || 'Failed to delete student');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error.message || 'Failed to delete student'
                });
            });
        }
    });
}

// Add event listener for the Add Student button
document.addEventListener('DOMContentLoaded', function() {
    const addStudentBtn = document.getElementById('add-student-btn');
    if (addStudentBtn) {
        addStudentBtn.addEventListener('click', addStudent);
    }
    
    // Load students when page loads
    loadStudents();
}); 