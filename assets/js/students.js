document.addEventListener('DOMContentLoaded', function() {
    const addStudentBtn = document.querySelector('.add-student-btn');
    const modal = document.getElementById('addStudentModal');
    const closeModal = document.querySelector('.close-modal');
    const cancelBtn = document.querySelector('.cancel-btn');
    const studentForm = document.querySelector('.student-form');

    // Load students data
    loadStudents();

    // Open modal
    if (addStudentBtn) {
        addStudentBtn.addEventListener('click', () => {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }

    // Close modal functions
    const closeModalFunction = () => {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    };

    if (closeModal) {
        closeModal.addEventListener('click', closeModalFunction);
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeModalFunction);
    }

    // Close modal when clicking outside
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModalFunction();
            }
        });
    }

    // Form submission
    if (studentForm) {
        studentForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const formData = new FormData(studentForm);
            
            fetch('../api/students.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Student added successfully');
                    closeModalFunction();
                    loadStudents(); // Reload the students list
                } else {
                    alert('Failed to add student: ' + data.message);
                }
            })
            .catch(error => console.error('Error adding student:', error));
        });
    }
});

// Load all students
function loadStudents() {
    fetch('../api/students.php')
        .then(response => response.json())
        .then(data => {
            const studentsTable = document.querySelector('#students table tbody');
            if (!studentsTable) return;
            
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

// Edit student function
function editStudent(id) {
    fetch(`../api/students.php?id=${id}`)
        .then(response => response.json())
        .then(student => {
            if (student) {
                const modal = document.getElementById('addStudentModal');
                if (!modal) return;
                
                // Update form title
                const modalTitle = modal.querySelector('h2');
                if (modalTitle) modalTitle.textContent = 'Edit Student';
                
                // Fill form with student data
                const form = modal.querySelector('form');
                form.querySelector('[name="student_id"]').value = student.id;
                form.querySelector('[name="first_name"]').value = student.first_name;
                form.querySelector('[name="last_name"]').value = student.last_name;
                form.querySelector('[name="email"]').value = student.email;
                
                // Set form action to update
                form.querySelector('[name="action"]').value = 'update';
                
                // Show modal
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        })
        .catch(error => console.error('Error fetching student details:', error));
}

// Delete student function
function deleteStudent(id) {
    if (confirm('Are you sure you want to delete this student?')) {
        fetch(`../api/students.php?id=${id}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Student deleted successfully');
                loadStudents(); // Reload the students list
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
                // You can implement a modal to display student details
                alert(`Student: ${student.first_name} ${student.last_name}\nEmail: ${student.email}`);
            }
        })
        .catch(error => console.error('Error fetching student details:', error));
}