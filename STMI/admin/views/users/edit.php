<?php if (!$user): ?>
    <div class="alert alert-danger">
        User not found.
    </div>
    <a href="users.php" class="btn btn-secondary">Back to Users</a>
<?php else: ?>
<div class="form-container">
    <div class="form-header">
        <h2><i class="fas fa-edit"></i> Edit User: <?php echo htmlspecialchars($user['username']); ?></h2>
        <a href="users.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>
    
    <form id="userForm" action="handlers/save_user.php" method="POST">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
        
        <div class="form-row">
            <div class="form-group">
                <label>Username</label>
                <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                <div class="form-help">Username cannot be changed</div>
            </div>
            
            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" required 
                       value="<?php echo htmlspecialchars($user['email']); ?>"
                       placeholder="Enter email address">
            </div>
        </div>
        
        <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="full_name" required 
                   value="<?php echo htmlspecialchars($user['full_name']); ?>"
                   placeholder="Enter full name">
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Change Password (Optional)</label>
                <div class="password-field">
                    <input type="password" name="password" id="password" 
                           placeholder="Leave blank to keep current password" minlength="6">
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="form-help">Minimum 6 characters</div>
            </div>
            
            <div class="form-group">
                <label>Confirm Password</label>
                <div class="password-field">
                    <input type="password" name="confirm_password" id="confirm_password" 
                           placeholder="Confirm new password">
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
                    <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Administrator</option>
                    <option value="editor" <?php echo $user['role'] == 'editor' ? 'selected' : ''; ?>>Editor</option>
                    <option value="viewer" <?php echo $user['role'] == 'viewer' ? 'selected' : ''; ?>>Viewer</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    <option value="active" <?php echo $user['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $user['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label>Phone Number (Optional)</label>
            <input type="tel" name="phone" 
                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                   placeholder="Enter phone number">
        </div>
        
        <div class="form-group">
            <label>Notes (Optional)</label>
            <textarea name="notes" rows="3" placeholder="Any additional notes about this user..."><?php echo htmlspecialchars($user['notes'] ?? ''); ?></textarea>
        </div>
        
        <div class="user-info-card">
            <h3>User Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>Created:</label>
                    <span><?php echo date('F j, Y, g:i a', strtotime($user['created_at'])); ?></span>
                </div>
                <div class="info-item">
                    <label>Last Login:</label>
                    <span><?php echo $user['last_login'] ? date('F j, Y, g:i a', strtotime($user['last_login'])) : 'Never logged in'; ?></span>
                </div>
                <div class="info-item">
                    <label>Last Updated:</label>
                    <span><?php echo date('F j, Y, g:i a', strtotime($user['updated_at'])); ?></span>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update User
            </button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='users.php'">
                Cancel
            </button>
            <button type="button" class="btn btn-warning" onclick="sendResetLink(<?php echo $user['id']; ?>)">
                <i class="fas fa-envelope"></i> Send Reset Link
            </button>
        </div>
    </form>
</div>

<style>
    .user-info-card {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin: 30px 0;
        border-left: 4px solid #0e0c5e;
    }
    
    .user-info-card h3 {
        margin: 0 0 15px 0;
        color: #333;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
    }
    
    .info-item label {
        font-weight: 600;
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 5px;
    }
    
    .info-item span {
        color: #333;
        font-size: 0.95rem;
    }
    
    .btn-warning {
        background: #f39c12;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-warning:hover {
        background: #e67e22;
    }
</style>

<script>
    // Toggle password visibility (same as add form)
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
    
    // Form validation
    document.getElementById('userForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== '' && password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
        
        if (password !== '' && password.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long!');
            return false;
        }
        
        return true;
    });
    
    // Send password reset link
    function sendResetLink(userId) {
        if (confirm('Send password reset link to this user?')) {
            fetch('handlers/send_reset_link.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ user_id: userId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Reset link sent successfully!');
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }
</script>
<?php endif; ?>