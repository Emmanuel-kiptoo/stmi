<div class="form-container">
    <div class="form-header">
        <h2><i class="fas fa-user-plus"></i> Add New Team Member</h2>
        <a href="team.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Team
        </a>
    </div>
    
    <form id="teamForm" action="handlers/save_team_member.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        
        <div class="form-row">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" required 
                       placeholder="Enter full name">
            </div>
            
            <div class="form-group">
                <label>Position/Role *</label>
                <input type="text" name="position" required 
                       placeholder="e.g., Executive Director, Sports Coordinator">
            </div>
        </div>
        
        <div class="form-group">
            <label>Biography *</label>
            <textarea id="bio" name="bio" rows="5" required 
                      placeholder="Tell us about this team member..."></textarea>
            <div class="form-help">Share their background, experience, and role in the organization</div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" 
                       placeholder="Enter email address">
            </div>
            
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" 
                       placeholder="Enter phone number">
            </div>
        </div>
        
        <div class="form-group">
            <label>Profile Photo</label>
            <div class="file-upload">
                <input type="file" name="photo" id="photo" 
                       accept="image/*" onchange="previewImage(this)">
                <label for="photo" class="upload-btn">
                    <i class="fas fa-cloud-upload-alt"></i> Choose Photo
                </label>
                <div class="file-name" id="fileName">No file chosen</div>
            </div>
            <div class="image-preview" id="imagePreview" style="display: none;">
                <img id="previewImg" src="#" alt="Preview">
            </div>
            <div class="form-help">Recommended size: 400x400px (square). Max size: 2MB</div>
        </div>
        
        <div class="social-media-section">
            <h3>Social Media Links (Optional)</h3>
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fab fa-linkedin"></i> LinkedIn Profile</label>
                    <input type="url" name="linkedin" 
                           placeholder="https://linkedin.com/in/username">
                </div>
                
                <div class="form-group">
                    <label><i class="fab fa-twitter"></i> Twitter Profile</label>
                    <input type="url" name="twitter" 
                           placeholder="https://twitter.com/username">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fab fa-facebook"></i> Facebook Profile</label>
                    <input type="url" name="facebook" 
                           placeholder="https://facebook.com/username">
                </div>
                
                <div class="form-group">
                    <label><i class="fab fa-instagram"></i> Instagram Profile</label>
                    <input type="url" name="instagram" 
                           placeholder="https://instagram.com/username">
                </div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Display Order</label>
                <input type="number" name="display_order" value="0" min="0">
                <small>Lower numbers appear first in the team list</small>
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
            <label>Team Category</label>
            <select name="team_category">
                <option value="">Select Category</option>
                <option value="leadership">Leadership Team</option>
                <option value="programs">Programs Team</option>
                <option value="sports">Sports Team</option>
                <option value="arts">Arts Team</option>
                <option value="mentorship">Mentorship Team</option>
                <option value="support">Support Team</option>
                <option value="volunteer">Volunteers</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Personal Statement (Optional)</label>
            <textarea name="personal_statement" rows="3" 
                      placeholder="A quote or personal mission statement from this team member..."></textarea>
        </div>
        
        <div class="form-group">
            <label>Areas of Expertise (Optional)</label>
            <input type="text" name="expertise" 
                   placeholder="e.g., Child Psychology, Sports Coaching, Art Therapy (comma separated)">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Add Team Member
            </button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='team.php'">
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
    }
    
    .file-upload {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .file-upload input[type="file"] {
        display: none;
    }
    
    .upload-btn {
        background: #f8f9fa;
        color: #333;
        padding: 10px 20px;
        border: 1px solid #ddd;
        border-radius: 5px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }
    
    .upload-btn:hover {
        background: #e9ecef;
    }
    
    .file-name {
        color: #666;
        font-size: 0.9rem;
    }
    
    .image-preview {
        margin-top: 15px;
        max-width: 200px;
    }
    
    .image-preview img {
        width: 100%;
        height: auto;
        border-radius: 5px;
        border: 1px solid #ddd;
    }
    
    .social-media-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin: 30px 0;
    }
    
    .social-media-section h3 {
        margin: 0 0 20px 0;
        color: #333;
    }
    
    .social-media-section label i {
        margin-right: 8px;
        color: #0e0c5e;
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
    
    .avatar-preview {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #0e0c5e;
        margin-bottom: 15px;
    }
    
    .avatar-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>

<script>
    // Image preview
    function previewImage(input) {
        if (input.files && input.files[0]) {
            document.getElementById('fileName').textContent = input.files[0].name;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('previewImg');
                preview.src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // Form validation
    document.getElementById('teamForm').addEventListener('submit', function(e) {
        const name = document.querySelector('input[name="name"]').value;
        const position = document.querySelector('input[name="position"]').value;
        const bio = document.querySelector('textarea[name="bio"]').value;
        
        if (!name || !position || !bio) {
            e.preventDefault();
            alert('Please fill in all required fields (Name, Position, Biography).');
            return false;
        }
        
        // Validate photo if uploaded
        const photoFile = document.getElementById('photo').files[0];
        if (photoFile && photoFile.size > 2 * 1024 * 1024) {
            e.preventDefault();
            alert('Photo file size must be less than 2MB.');
            return false;
        }
        
        // Validate email format if provided
        const email = document.querySelector('input[name="email"]').value;
        if (email && !validateEmail(email)) {
            e.preventDefault();
            alert('Please enter a valid email address.');
            return false;
        }
        
        return true;
    });
    
    function validateEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }
    
    // Rich text editor for biography
    document.addEventListener('DOMContentLoaded', function() {
        const textarea = document.getElementById('bio');
        if (textarea) {
            // Add toolbar for basic formatting
            const toolbar = document.createElement('div');
            toolbar.className = 'editor-toolbar';
            toolbar.innerHTML = `
                <button type="button" onclick="formatText('bold')"><i class="fas fa-bold"></i></button>
                <button type="button" onclick="formatText('italic')"><i class="fas fa-italic"></i></button>
                <button type="button" onclick="formatText('underline')"><i class="fas fa-underline"></i></button>
                <button type="button" onclick="insertParagraph()"><i class="fas fa-paragraph"></i></button>
                <button type="button" onclick="insertBullet()"><i class="fas fa-list-ul"></i></button>
            `;
            textarea.parentNode.insertBefore(toolbar, textarea);
            
            // Add some CSS for the toolbar
            toolbar.style.marginBottom = '10px';
            toolbar.style.display = 'flex';
            toolbar.style.gap = '5px';
            toolbar.style.flexWrap = 'wrap';
            
            const buttons = toolbar.querySelectorAll('button');
            buttons.forEach(button => {
                button.style.padding = '8px 12px';
                button.style.border = '1px solid #ddd';
                button.style.background = '#f8f9fa';
                button.style.borderRadius = '4px';
                button.style.cursor = 'pointer';
                button.style.color = '#333';
            });
        }
    });
    
    function formatText(command) {
        const textarea = document.getElementById('bio');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = textarea.value.substring(start, end);
        
        let formattedText = '';
        switch (command) {
            case 'bold': formattedText = `<strong>${selectedText}</strong>`; break;
            case 'italic': formattedText = `<em>${selectedText}</em>`; break;
            case 'underline': formattedText = `<u>${selectedText}</u>`; break;
        }
        
        textarea.value = textarea.value.substring(0, start) + formattedText + textarea.value.substring(end);
    }
    
    function insertParagraph() {
        const textarea = document.getElementById('bio');
        const cursorPos = textarea.selectionStart;
        textarea.value = textarea.value.substring(0, cursorPos) + '\n\n' + textarea.value.substring(cursorPos);
    }
    
    function insertBullet() {
        const textarea = document.getElementById('bio');
        const cursorPos = textarea.selectionStart;
        textarea.value = textarea.value.substring(0, cursorPos) + '\n• ' + textarea.value.substring(cursorPos);
    }
</script>