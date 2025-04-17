document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements
    const studentModal = document.getElementById('student-form-modal');
    const studentForm = document.getElementById('student-form');
    const addStudentBtn = document.getElementById('add-student-btn');
    const closeModalBtn = studentModal.querySelector('.close-modal');
    const studentTableBody = document.querySelector('#students table tbody');

    // Load students when the page loads
    loadStudents();

    // Add student button click handler
    addStudentBtn.addEventListener('click', function() {
        resetForm();
        document.getElementById('student-form-title').textContent = 'Register New Student';
        studentModal.style.display = 'block';
    });

    // Close modal button click handler
    closeModalBtn.addEventListener('click', function() {
        studentModal.style.display = 'none';
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === studentModal) {
            studentModal.style.display = 'none';
        }
    });

    // Form submission handler
    studentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(studentForm);
        const studentId = document.getElementById('student-id').value;
        
        // Set the action based on whether we're adding or editing
        formData.append('action', studentId ? 'edit' : 'add');

        // Send the request
        fetch('../pages/students/student_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showSuccess(data.message);
                studentModal.style.display = 'none';
                loadStudents();
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            showError('An error occurred while processing your request.');
            console.error('Error:', error);
        });
    });

    // Function to load students
    function loadStudents() {
        fetch('../pages/students/student_handler.php?action=list')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    displayStudents(data.data);
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                showError('Error loading students');
                console.error('Error:', error);
            });
    }

    // Function to display students in the table
    function displayStudents(students) {
        studentTableBody.innerHTML = '';
        students.forEach(student => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${student.student_number}</td>
                <td>${student.first_name} ${student.last_name}</td>
                <td>${student.email}</td>
                <td>
                    <button type="button" class="btn btn-secondary" onclick="editStudent(${student.student_id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-danger" onclick="deleteStudent(${student.student_id})">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                    <button type="button" class="btn btn-info" onclick="viewStudent(${student.student_id})">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            `;
            studentTableBody.appendChild(row);
        });
    }

    // Function to reset the form
    function resetForm() {
        studentForm.reset();
        document.getElementById('student-id').value = '';
    }
});

// Function to edit student
function editStudent(studentId) {
    fetch(`../pages/students/student_handler.php?action=get&student_id=${studentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const student = data.data;
                document.getElementById('student-id').value = student.student_id;
                document.getElementById('student-number').value = student.student_number;
                document.getElementById('first-name').value = student.first_name;
                document.getElementById('last-name').value = student.last_name;
                document.getElementById('email').value = student.email;
                document.getElementById('date-of-birth').value = student.date_of_birth;
                document.getElementById('registration-date').value = student.registration_date;
                
                document.getElementById('student-form-title').textContent = 'Edit Student';
                document.getElementById('student-form-modal').style.display = 'block';
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            showError('Error loading student details');
            console.error('Error:', error);
        });
}

// Function to delete student
function deleteStudent(studentId) {
    if (confirm('Are you sure you want to delete this student?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('student_id', studentId);

        fetch('../pages/students/student_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showSuccess(data.message);
                loadStudents();
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            showError('Error deleting student');
            console.error('Error:', error);
        });
    }
}

// Function to view student details
function viewStudent(studentId) {
    fetch(`../pages/students/student_handler.php?action=get&student_id=${studentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const student = data.data;
                
                // Create modal if it doesn't exist
                let viewModal = document.getElementById('view-student-modal');
                if (!viewModal) {
                    viewModal = document.createElement('div');
                    viewModal.id = 'view-student-modal';
                    viewModal.className = 'modal';
                    document.body.appendChild(viewModal);
                }

                // Update modal content
                viewModal.innerHTML = `
                    <div class="modal-content" style="background: white; padding: 20px; border-radius: 8px; width: 500px; max-width: 90%; position: relative; margin: 10% auto;">
                        <span class="close-modal" onclick="closeViewModal()" style="position: absolute; right: 20px; top: 10px; font-size: 24px; cursor: pointer; color: #666;">&times;</span>
                        <div class="student-details">
                            <h2 style="color: #2C3E50; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-user-graduate" style="color: #FF8303;"></i> 
                                Student Details
                            </h2>
                            <div class="detail-row" style="display: flex; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                                <label style="font-weight: 600; width: 150px; color: #2C3E50;">Student Number:</label>
                                <span style="color: #2C3E50;">${student.student_number}</span>
                            </div>
                            <div class="detail-row" style="display: flex; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                                <label style="font-weight: 600; width: 150px; color: #2C3E50;">Name:</label>
                                <span style="color: #2C3E50;">${student.first_name} ${student.last_name}</span>
                            </div>
                            <div class="detail-row" style="display: flex; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                                <label style="font-weight: 600; width: 150px; color: #2C3E50;">Email:</label>
                                <span style="color: #2C3E50;">${student.email}</span>
                            </div>
                            <div class="detail-row" style="display: flex; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                                <label style="font-weight: 600; width: 150px; color: #2C3E50;">Date of Birth:</label>
                                <span style="color: #2C3E50;">${formatDate(student.date_of_birth)}</span>
                            </div>
                            <div class="detail-row" style="display: flex; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                                <label style="font-weight: 600; width: 150px; color: #2C3E50;">Registration Date:</label>
                                <span style="color: #2C3E50;">${formatDate(student.registration_date)}</span>
                            </div>
                        </div>
                    </div>
                `;

                // Add modal styles if not already present
                if (!document.getElementById('modal-styles')) {
                    const style = document.createElement('style');
                    style.id = 'modal-styles';
                    style.textContent = `
                        .modal {
                            display: none;
                            position: fixed;
                            z-index: 1000;
                            left: 0;
                            top: 0;
                            width: 100%;
                            height: 100%;
                            background-color: rgba(0, 0, 0, 0.5);
                            animation: fadeIn 0.3s ease;
                        }

                        @keyframes fadeIn {
                            from { opacity: 0; }
                            to { opacity: 1; }
                        }

                        .modal-content {
                            animation: slideIn 0.3s ease;
                        }

                        @keyframes slideIn {
                            from {
                                transform: translateY(-20px);
                                opacity: 0;
                            }
                            to {
                                transform: translateY(0);
                                opacity: 1;
                            }
                        }

                        .detail-row:hover {
                            background: #fff !important;
                            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                        }

                        .close-modal:hover {
                            color: #ff0000 !important;
                        }
                    `;
                    document.head.appendChild(style);
                }

                // Show modal
                viewModal.style.display = 'block';
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            showError('Error loading student details');
            console.error('Error:', error);
        });
}

// Function to close view modal
function closeViewModal() {
    const viewModal = document.getElementById('view-student-modal');
    if (viewModal) {
        viewModal.style.display = 'none';
    }
}

// Function to format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

// Add event listener to close modal when clicking outside
window.addEventListener('click', function(event) {
    const viewModal = document.getElementById('view-student-modal');
    if (event.target === viewModal) {
        closeViewModal();
    }
}); 