<!-- Add User Button -->
<button type="button" class="btn btn-primary rounded-pill shadow-sm" id="add-user-btn">
    <i class="fas fa-user-plus me-2"></i> Add User
</button>

<!-- User Form Modal -->
<div class="modal fade" id="user-form-modal" tabindex="-1" role="dialog" aria-labelledby="user-form-title" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-gradient-primary text-white border-0">
                <h5 class="modal-title" id="user-form-title">
                    <i class="fas fa-user-plus me-2"></i>Add New User
                </h5>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="user-form" class="needs-validation" novalidate>
                    <input type="hidden" id="user-id">
                    
                    <div class="mb-3">
                        <div class="form-floating">
                            <input type="text" class="form-control custom-input" id="username" placeholder="Username" required>
                            <label for="username">Username <span class="text-danger">*</span></label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-floating password-group">
                            <input type="password" class="form-control custom-input" id="password" placeholder="Password" required>
                            <button class="btn btn-outline-secondary password-toggle" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                            <label for="password">Password <span class="text-danger">*</span></label>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div class="form-floating">
                                <input type="text" class="form-control custom-input" id="firstname" placeholder="First Name" required>
                                <label for="firstname">First Name <span class="text-danger">*</span></label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-floating">
                                <input type="text" class="form-control custom-input" id="lastname" placeholder="Last Name" required>
                                <label for="lastname">Last Name <span class="text-danger">*</span></label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-floating">
                            <input type="email" class="form-control custom-input" id="email" placeholder="Email" required>
                            <label for="email">Email <span class="text-danger">*</span></label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-floating">
                            <select class="form-select custom-input" id="role_id" required>
                                <option value="">Select Role</option>
                                <option value="1">Administrator</option>
                                <option value="2">Librarian</option>
                                <option value="3">Staff</option>
                            </select>
                            <label for="role_id">Role <span class="text-danger">*</span></label>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div class="form-floating">
                                <input type="tel" class="form-control custom-input" id="phone" placeholder="Phone Number">
                                <label for="phone">Phone Number</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-floating">
                                <select class="form-select custom-input" id="status">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                <label for="status">Status</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-floating">
                            <textarea class="form-control custom-input" id="notes" placeholder="Notes" style="min-height: 80px"></textarea>
                            <label for="notes">Notes</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="save-user">
                    <i class="fas fa-save me-2"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities Section -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Recent Activities</h5>
    </div>
    <div class="card-body">
        <div id="recent-activities">
            <!-- Activities will be loaded here -->
        </div>
    </div>
</div>

<!-- Users Table -->
<div id="users">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Role</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Users will be loaded here -->
        </tbody>
    </table>
</div>

<!-- Include JavaScript files -->
<script src="assets/js/dashboard.js"></script>

<!-- Add this to your head section or before closing body tag -->
<style>
.modal-content {
    border-radius: 15px;
    background: #fff;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #2196F3, #1976D2);
}

.modal-dialog {
    max-width: 400px;
    margin: 1.75rem auto;
}

.modal-header {
    padding: 1.25rem 1.5rem;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1.25rem 1.5rem;
    background: #f8f9fa;
    border-bottom-left-radius: 15px;
    border-bottom-right-radius: 15px;
}

/* Form styling */
.form-floating {
    position: relative;
    margin-bottom: 0;
}

.custom-input {
    height: 48px !important;
    padding: 0.75rem 1rem !important;
    font-size: 0.9rem;
    border-radius: 10px !important;
    border: 1px solid #dee2e6;
    background-color: #fff;
    transition: all 0.2s ease-in-out;
}

.custom-input:focus {
    border-color: #2196F3;
    box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.15);
    background-color: #fff;
}

.form-floating > label {
    padding: 0.75rem 1rem;
    color: #6c757d;
    font-size: 0.9rem;
    transform-origin: 0 0;
}

.form-floating > .custom-input:focus ~ label,
.form-floating > .custom-input:not(:placeholder-shown) ~ label {
    transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
    background-color: #fff;
    padding: 0 0.5rem;
}

/* Password input group */
.password-group {
    position: relative;
}

.password-group .custom-input {
    padding-right: 3rem !important;
}

.password-toggle {
    position: absolute;
    right: 0;
    top: 0;
    height: 48px;
    width: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    color: #6c757d;
    z-index: 3;
}

.password-toggle:hover {
    color: #2196F3;
}

/* Textarea styling */
textarea.custom-input {
    min-height: 80px !important;
    height: auto !important;
}

/* Button styling */
.btn {
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.2s;
}

.btn-primary {
    background: linear-gradient(135deg, #2196F3, #1976D2);
    border: none;
}

.btn-light {
    background: #fff;
    border: 1px solid #dee2e6;
}

.btn:hover {
    transform: translateY(-1px);
}

/* Row spacing */
.row {
    margin-left: -0.75rem;
    margin-right: -0.75rem;
}

.col-6 {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
}

/* Select styling */
.form-select {
    background-position: right 1rem center;
}

/* Required field indicator */
.text-danger {
    font-size: 0.8rem;
    font-weight: bold;
}
</style> 