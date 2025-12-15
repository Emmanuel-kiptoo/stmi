<div class="form-container">
    <div class="form-header">
        <h2><i class="fas fa-user-plus"></i> Add New User</h2>
        <a href="users.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>
    
    <form id="userForm" action="handlers/save_user.php" method="POST">
        <input type="hidden" name="action" value="add">
        
        <div class="form-row">
            <div class="form-group">
                <label>Username *</label>
                <input type="text" name="username" required 
                       placeholder="Enter username (for login)" 
                       pattern="[a-zA-Z0-9_]{3,50}"
                       title="3-50 characters, letters, numbers, and underscores only">
                <div class="form-help">Username cannot be changed later</div>
            </div>
            
            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" required 
                       placeholder="Enter email address">
            </div>
        </div>
        
        <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="full_name" required 
                   placeholder="Enter full name">
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Password *</label>
                <div class="password-field">
                    <input type="password" name="password" id="password" required 
                           placeholder="Enter password" minlength="6">
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="form-help">Minimum 6 characters</div>
            </div>
            
            <div class="form-group">
                <label>Confirm Password *</label>
                <div class="password-field">
                    <input type="password" name="confirm_password" id="confirm_password" required 
                           placeholder="Confirm password">
                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Role *</label>
                <select name="role" required>
                    <option value="">Select Role</option>
                    <option value="admin">Administrator</option>
                    <option value="editor" selected>Editor</option>
                    <option value="viewer">Viewer</option>
                </select>
                <div class="form-help">
                    <strong>Admin:</strong> Full access to everything<br>
                    <strong>Editor:</strong> Can manage content but not users<br>
                    <strong>Viewer:</strong> Read-only access
                </div>
            </div>
            
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    <option value="active" selected>Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label>Phone Number (Optional)</label>
            <input type="tel" name="phone" placeholder="Enter phone number">
        </div>
        
        <div class="form-group">
            <label>Notes (Optional)</label>
            <textarea name="notes" rows="3" placeholder="Any additional notes about this user..."></textarea>
        </div>
        
        <div class="permissions-section">
            <h3>Permissions</h3>
            <div class="permissions-grid">
                <div class="permission-category">
                    <h4>Content Management</h4>
                    <label class="permission-checkbox">
                        <input type="checkbox" name="permissions[]" value="manage_events" checked>
                        <span>Manage Events</span>
                    </label>
                    <label class="permission-checkbox">
                        <input type="checkbox" name="permissions[]" value="manage_team" checked>
                        <span>Manage Team</span>
                    </label>
                    <label class="permission-checkbox">
                        <input type="checkbox" name="permissions[]" value="manage_gallery" checked>
                        <span>Manage Gallery</span>
                    </label>
                </div>
                
                <div class="permission-category">
                    <h4>Communication</h4>
                    <label class="permission-checkbox">
                        <input type="checkbox" name="permissions[]" value="view_messages" checked>
                        <span>View Messages</span>
                    </label>
                    <label class="permission-checkbox">
                        <input type="checkbox" name="permissions[]" value="reply_messages" checked>
                        <span>Reply to Messages</span>
                    </label>
                </div>
                
                <div class="permission-category">
                    <h4>Donations</h4>
                    <label class="permission-checkbox">
                        <input type="checkbox" name="permissions[]" value="view_donations" checked>
                        <span>View Donations</span>
                    </label>
                    <label class="permission-checkbox">
                        <input type="checkbox" name="permissions[]" value="manage_donations" checked>
                        <span>Manage Donations</span>
                    </label>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Create User
            </button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='users.php'">
                Cancel
            </button>
        </div>
    </form>
</div>

<style>
    .form-container {
        background: white;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .form-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }
    
    .form-header h2 {
        color: #333;
        margin: 0;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 1rem;
        transition: border-color 0.3s;
    }
    
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: #0e0c5e;
        outline: none;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .form-help {
        font-size: 0.85rem;
        color: #666;
        margin-top: 5px;
        line-height: 1.4;
    }
    
    .password-field {
        position: relative;
    }
    
    .password-field input {
        padding-right: 40px;
    }
    
    .toggle-password {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #666;
        cursor: pointer;
    }
    
    .permissions-section {
        margin: 40px 0;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .permissions-section h3 {
        margin-bottom: 20px;
        color: #333;
    }
    
    .permissions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }
    
    .permission-category {
        background: white;
        padding: 15px;
        border-radius: 5px;
        border: 1px solid #eee;
    }
    
    .permission-category h4 {
        margin: 0 0 15px 0;
        color: #0e0c5e;
        font-size: 1rem;
    }
    
    .permission-checkbox {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        cursor: pointer;
    }
    
    .permission-checkbox input[type="checkbox"] {
        width: auto;
    }
    
    .permission-checkbox span {
        color: #555;
    }
    
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 40px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
    
    .btn {
        padding: 12px 25px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 1rem;
    }
    
    .btn-primary {
        background: #0e0c5e;
        color: white;
    }
    
    .btn-primary:hover {
        background: #0a0848;
    }
    
    .btn-secondary {
        background: #f8f9fa;
        color: #333;
        border: 1px solid #ddd;
    }
    
    .btn-secondary:hover {
        background: #e9ecef;
    }
    
    .password-strength {
        margin-top: 5px;
        height: 5px;
        border-radius: 3px;
        background: #eee;
        overflow: hidden;
    }
    
    .strength-meter {
        height: 100%;
        transition: width 0.3s, background-color 0.3s;
    }
</style>

<script>
    // Toggle password visibility
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const button = field.nextElementSibling;
        const icon = button.querySelector('i');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    
    // Password strength indicator
    const passwordField = document.getElementById('password');
    if (passwordField) {
        const strengthMeter = document.createElement('div');
        strengthMeter.className = 'password-strength';
        strengthMeter.innerHTML = '<div class="strength-meter"></div>';
        passwordField.parentNode.insertBefore(strengthMeter, passwordField.nextSibling);
        
        passwordField.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            const meter = strengthMeter.querySelector('.strength-meter');
            
            meter.style.width = strength.score * 25 + '%';
            
            if (strength.score <= 1) {
                meter.style.backgroundColor = '#e74c3c';
            } else if (strength.score == 2) {
                meter.style.backgroundColor = '#f39c12';
            } else if (strength.score == 3) {
                meter.style.backgroundColor = '#3498db';
            } else {
                meter.style.backgroundColor = '#57cc99';
            }
        });
    }
    
    function calculatePasswordStrength(password) {
        let score = 0;
        const messages = [];
        
        if (password.length >= 8) score++;
        else messages.push('At least 8 characters');
        
        if (/[A-Z]/.test(password)) score++;
        else messages.push('Uppercase letter');
        
        if (/[a-z]/.test(password)) score++;
        else messages.push('Lowercase letter');
        
        if (/[0-9]/.test(password)) score++;
        else messages.push('Number');
        
        if (/[^A-Za-z0-9]/.test(password)) score++;
        else messages.push('Special character');
        
        return { score: score, messages: messages };
    }
    
    // Form validation
    document.getElementById('userForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long!');
            return false;
        }
        
        // Additional validation can be added here
        
        return true;
    });
</script>