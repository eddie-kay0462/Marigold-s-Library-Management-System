document.addEventListener('DOMContentLoaded', function() {
    // Load settings
    loadSettings();
    
    // Set up form listeners
    setupSettingsForms();
});

// Load settings data
function loadSettings() {
    fetch('../api/settings.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Set library information form values
                const libraryInfoForm = document.querySelector('#library-info-form');
                if (libraryInfoForm) {
                    libraryInfoForm.querySelector('#library-name').value = data.settings.library_name || '';
                    libraryInfoForm.querySelector('#library-address').value = data.settings.library_address || '';
                    libraryInfoForm.querySelector('#library-phone').value = data.settings.library_phone || '';
                    libraryInfoForm.querySelector('#library-email').value = data.settings.library_email || '';
                }
                
                // Set loan settings form values
                const loanSettingsForm = document.querySelector('#loan-settings-form');
                if (loanSettingsForm) {
                    loanSettingsForm.querySelector('#loan-duration').value = data.settings.loan_duration || '14';
                    loanSettingsForm.querySelector('#max-books').value = data.settings.max_books || '5';
                    loanSettingsForm.querySelector('#late-fee').value = data.settings.late_fee || '0.50';
                }
            }
        })
        .catch(error => console.error('Error loading settings:', error));
}

// Set up settings forms event listeners
function setupSettingsForms() {
    // Library information form
    const libraryInfoForm = document.querySelector('#library-info-form');
    if (libraryInfoForm) {
        libraryInfoForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'update_library_info');
            
            saveSettings(formData);
        });
    }
    
    // Loan settings form
    const loanSettingsForm = document.querySelector('#loan-settings-form');
    if (loanSettingsForm) {
        loanSettingsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'update_loan_settings');
            
            saveSettings(formData);
        });
    }
}

// Save settings
function saveSettings(formData) {
    fetch('../api/settings.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Settings saved successfully');
        } else {
            alert('Failed to save settings: ' + data.message);
        }
    })
    .catch(error => console.error('Error saving settings:', error));
}