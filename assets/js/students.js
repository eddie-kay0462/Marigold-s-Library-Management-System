document.addEventListener('DOMContentLoaded', function() {
    const addStudentBtn = document.querySelector('.add-student-btn');
    const modal = document.getElementById('addStudentModal');
    const closeModal = document.querySelector('.close-modal');
    const cancelBtn = document.querySelector('.cancel-btn');
    const studentForm = document.querySelector('.student-form');

    // Open modal
    addStudentBtn.addEventListener('click', () => {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    });

    // Close modal functions
    const closeModalFunction = () => {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    };

    closeModal.addEventListener('click', closeModalFunction);
    cancelBtn.addEventListener('click', closeModalFunction);

    // Close modal when clicking outside
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModalFunction();
        }
    });

    // Form submission
    studentForm.addEventListener('submit', (e) => {
        e.preventDefault();
        // Add your form submission logic here
        closeModalFunction();
    });
}); 