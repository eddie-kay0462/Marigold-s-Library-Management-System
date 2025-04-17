document.addEventListener('DOMContentLoaded', function() {
    // Function to remove messages after timeout
    function removeMessage(messageElement) {
        if (messageElement) {
            messageElement.style.animation = 'slideUp 0.5s ease-out';
            messageElement.addEventListener('animationend', function() {
                messageElement.remove();
            });
        }
    }

    // Add slideUp animation to styles
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideUp {
            from {
                transform: translateY(0);
                opacity: 1;
            }
            to {
                transform: translateY(-100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

    // Handle success messages
    const successMessage = document.querySelector('.success-message-container');
    if (successMessage) {
        setTimeout(() => {
            removeMessage(successMessage);
        }, 3000);
    }

    // Handle error messages
    const errorMessage = document.querySelector('.error-message-container');
    if (errorMessage) {
        setTimeout(() => {
            removeMessage(errorMessage);
        }, 5000);
    }
}); 