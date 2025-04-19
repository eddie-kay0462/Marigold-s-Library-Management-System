// Student Management System
(function() {
  // Global variables
  let studentTableBody = null;
  let studentForm = null;

  // Helper function to show success messages
  window.showSuccess = function(message) {
      console.log('Success:', message);
      alert('Success: ' + message);
  };

  // Helper function to show error messages
  window.showError = function(message) {
      console.error('Error:', message);
      alert('Error: ' + message);
  };

  // Function to format date
  window.formatDate = function(dateString) {
      if (!dateString) return 'N/A';
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(dateString).toLocaleDateString(undefined, options);
  };

  // Function to display students in the table
  window.displayStudents = function(students) {
      if (!studentTableBody) {
          studentTableBody = document.querySelector('#students table tbody');
          if (!studentTableBody) {
              console.error('Student table body not found');
              return;
          }
      }
      
      studentTableBody.innerHTML = '';
      
      if (!students || students.length === 0) {
          const row = document.createElement('tr');
          row.innerHTML = '<td colspan="4" class="text-center">No students found</td>';
          studentTableBody.appendChild(row);
          return;
      }

      students.forEach(student => {
          const row = document.createElement('tr');
          row.innerHTML = `
              <td>${student.student_number || 'N/A'}</td>
              <td>${student.first_name || ''} ${student.last_name || ''}</td>
              <td>${student.email || 'N/A'}</td>
              <td>
                  <button type="button" class="btn btn-secondary" onclick="window.editStudent(${student.student_id})">
                      <i class="fas fa-edit"></i>
                  </button>
                  <button type="button" class="btn btn-danger" onclick="window.deleteStudent(${student.student_id})">
                      <i class="fas fa-trash-alt"></i>
                  </button>
                  <button type="button" class="btn btn-info" onclick="window.viewStudent(${student.student_id})">
                      <i class="fas fa-eye"></i>
                  </button>
              </td>
          `;
          studentTableBody.appendChild(row);
      });
  };

  // Function to load students
  window.loadStudents = function() {
      if (!studentTableBody) {
          studentTableBody = document.querySelector('#students table tbody');
          if (!studentTableBody) {
              console.error('Student table body not found');
              return;
          }
      }

      fetch('../pages/students/student_handler.php?action=list')
          .then(response => {
              if (!response.ok) {
                  throw new Error('Network response was not ok');
              }
              return response.json();
          })
          .then(data => {
              if (data.status === 'success') {
                  window.displayStudents(data.data);
              } else {
                  window.showError(data.message || 'Failed to load students');
              }
          })
          .catch(error => {
              window.showError('Error loading students: ' + error.message);
              console.error('Error:', error);
          });
  };

  // Function to reset the form
  window.resetForm = function() {
      if (studentForm) {
          studentForm.reset();
          document.getElementById('student-id').value = '';
      }
  };

  // Function to handle form submission
  window.handleFormSubmit = function(e) {
      e.preventDefault();
      const formData = new FormData(studentForm);
      const studentId = document.getElementById('student-id').value;
      
      formData.append('action', studentId ? 'edit' : 'add');

      fetch('../pages/students/student_handler.php', {
          method: 'POST',
          body: formData
      })
      .then(response => {
          if (!response.ok) {
              throw new Error('Network response was not ok');
          }
          return response.json();
      })
      .then(data => {
          if (data.status === 'success') {
              window.showSuccess(data.message);
              document.getElementById('student-form-modal').style.display = 'none';
              window.loadStudents();
          } else {
              window.showError(data.message || 'Operation failed');
          }
      })
      .catch(error => {
          window.showError('An error occurred: ' + error.message);
          console.error('Error:', error);
      });
  };

  // Function to edit student
  window.editStudent = function(studentId) {
      if (!studentId) {
          console.error('No student ID provided');
          return;
      }

      fetch(`../pages/students/student_handler.php?action=get&student_id=${studentId}`)
          .then(response => {
              if (!response.ok) {
                  throw new Error('Network response was not ok');
              }
              return response.json();
          })
          .then(data => {
              if (data.status === 'success') {
                  const student = data.data;
                  document.getElementById('student-id').value = student.student_id;
                  document.getElementById('student-number').value = student.student_number || '';
                  document.getElementById('first-name').value = student.first_name || '';
                  document.getElementById('last-name').value = student.last_name || '';
                  document.getElementById('email').value = student.email || '';
                  document.getElementById('date-of-birth').value = student.date_of_birth || '';
                  document.getElementById('registration-date').value = student.registration_date || '';
                  
                  document.getElementById('student-form-title').textContent = 'Edit Student';
                  document.getElementById('student-form-modal').style.display = 'block';
              } else {
                  window.showError(data.message || 'Failed to load student data');
              }
          })
          .catch(error => {
              window.showError('Error loading student details: ' + error.message);
              console.error('Error:', error);
          });
  };

  // Function to delete student
  window.deleteStudent = function(studentId) {
      if (!studentId) {
          console.error('No student ID provided');
          return;
      }

      if (confirm('Are you sure you want to delete this student?')) {
          const formData = new FormData();
          formData.append('action', 'delete');
          formData.append('student_id', studentId);

          fetch('../pages/students/student_handler.php', {
              method: 'POST',
              body: formData
          })
          .then(response => {
              if (!response.ok) {
                  throw new Error('Network response was not ok');
              }
              return response.json();
          })
          .then(data => {
              if (data.status === 'success') {
                  window.showSuccess(data.message);
                  window.loadStudents();
              } else {
                  window.showError(data.message || 'Failed to delete student');
              }
          })
          .catch(error => {
              window.showError('Error deleting student: ' + error.message);
              console.error('Error:', error);
          });
      }
  };

  // Function to view student details
  window.viewStudent = function(studentId) {
      if (!studentId) {
          console.error('No student ID provided');
          return;
      }

      fetch(`../pages/students/student_handler.php?action=get&student_id=${studentId}`)
          .then(response => {
              if (!response.ok) {
                  throw new Error('Network response was not ok');
              }
              return response.json();
          })
          .then(data => {
              if (data.status === 'success') {
                  const student = data.data;
                  
                  // Create or update modal
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
                          <span class="close-modal" onclick="window.closeViewModal()" style="position: absolute; right: 20px; top: 10px; font-size: 24px; cursor: pointer; color: #666;">&times;</span>
                          <div class="student-details">
                              <h2 style="color: #2C3E50; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                                  <i class="fas fa-user-graduate" style="color: #FF8303;"></i> 
                                  Student Details
                              </h2>
                              <div class="detail-row" style="display: flex; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                                  <label style="font-weight: 600; width: 150px; color: #2C3E50;">Student Number:</label>
                                  <span style="color: #2C3E50;">${student.student_number || 'N/A'}</span>
                              </div>
                              <div class="detail-row" style="display: flex; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                                  <label style="font-weight: 600; width: 150px; color: #2C3E50;">Name:</label>
                                  <span style="color: #2C3E50;">${student.first_name || ''} ${student.last_name || ''}</span>
                              </div>
                              <div class="detail-row" style="display: flex; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                                  <label style="font-weight: 600; width: 150px; color: #2C3E50;">Email:</label>
                                  <span style="color: #2C3E50;">${student.email || 'N/A'}</span>
                              </div>
                              <div class="detail-row" style="display: flex; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                                  <label style="font-weight: 600; width: 150px; color: #2C3E50;">Date of Birth:</label>
                                  <span style="color: #2C3E50;">${window.formatDate(student.date_of_birth)}</span>
                              </div>
                              <div class="detail-row" style="display: flex; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                                  <label style="font-weight: 600; width: 150px; color: #2C3E50;">Registration Date:</label>
                                  <span style="color: #2C3E50;">${window.formatDate(student.registration_date)}</span>
                              </div>
                          </div>
                      </div>
                  `;

                  // Show modal
                  viewModal.style.display = 'block';
              } else {
                  window.showError(data.message || 'Failed to load student details');
              }
          })
          .catch(error => {
              window.showError('Error loading student details: ' + error.message);
              console.error('Error:', error);
          });
  };

  // Function to close view modal
  window.closeViewModal = function() {
      const viewModal = document.getElementById('view-student-modal');
      if (viewModal) {
          viewModal.style.display = 'none';
      }
  };

  // Initialize the page
  function init() {
      // Get DOM elements
      const studentModal = document.getElementById('student-form-modal');
      studentForm = document.getElementById('student-form');
      const addStudentBtn = document.getElementById('add-student-btn');
      const closeModalBtn = studentModal?.querySelector('.close-modal');
      studentTableBody = document.querySelector('#students table tbody');

      // Check if required elements exist
      if (!studentModal || !studentForm || !addStudentBtn || !studentTableBody) {
          console.error('Required DOM elements not found');
          return;
      }

      // Load students when the page loads
      window.loadStudents();

      // Add student button click handler
      addStudentBtn.addEventListener('click', function() {
          window.resetForm();
          document.getElementById('student-form-title').textContent = 'Register New Student';
          studentModal.style.display = 'block';
      });

      // Close modal button click handler
      if (closeModalBtn) {
          closeModalBtn.addEventListener('click', function() {
              studentModal.style.display = 'none';
          });
      }

      // Close modal when clicking outside
      window.addEventListener('click', function(event) {
          if (event.target === studentModal) {
              studentModal.style.display = 'none';
          }
      });

      // Form submission handler
      studentForm.addEventListener('submit', window.handleFormSubmit);
  }

  // Initialize when DOM is loaded
  if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', init);
  } else {
      init();
  }
})();