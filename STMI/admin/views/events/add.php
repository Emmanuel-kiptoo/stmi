<div class="form-container">
    <div class="form-header">
        <h2><i class="fas fa-plus-circle"></i> Add New Event</h2>
        <a href="events.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Events
        </a>
    </div>
    
    <form id="eventForm" action="handlers/save_event.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        
        <div class="form-group">
            <label>Event Title *</label>
            <input type="text" name="title" required 
                   placeholder="Enter event title" 
                   maxlength="200">
        </div>
        
        <div class="form-group">
            <label>Event Description *</label>
            <textarea id="description" name="description" rows="5" required 
                      placeholder="Enter detailed event description..."></textarea>
            <div class="form-help">You can use HTML formatting</div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Event Date *</label>
                <input type="date" name="event_date" required 
                       min="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="form-group">
                <label>Start Time</label>
                <input type="time" name="start_time">
            </div>
            
            <div class="form-group">
                <label>End Time</label>
                <input type="time" name="end_time">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Location *</label>
                <input type="text" name="location" required 
                       placeholder="Enter event location">
            </div>
            
            <div class="form-group">
                <label>Category *</label>
                <select name="category" required>
                    <option value="upcoming" selected>Upcoming</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="past">Past</option>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    <option value="published" selected>Published</option>
                    <option value="draft">Draft</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Registration Link</label>
                <input type="url" name="registration_link" 
                       placeholder="https://example.com/register">
            </div>
        </div>
        
        <div class="form-group">
            <label>Featured Image</label>
            <div class="file-upload">
                <input type="file" name="featured_image" id="featuredImage" 
                       accept="image/*" onchange="previewImage(this)">
                <label for="featuredImage" class="upload-btn">
                    <i class="fas fa-cloud-upload-alt"></i> Choose Image
                </label>
                <div class="file-name" id="fileName">No file chosen</div>
            </div>
            <div class="image-preview" id="imagePreview" style="display: none;">
                <img id="previewImg" src="#" alt="Preview">
            </div>
            <div class="form-help">Recommended size: 800x450px. Max size: 2MB</div>
        </div>
        
        <div class="form-group">
            <label>Additional Information (Optional)</label>
            <textarea name="additional_info" rows="3" 
                      placeholder="Any additional information about the event..."></textarea>
        </div>
        
        <div class="form-group">
            <label>Tags (Optional)</label>
            <input type="text" name="tags" 
                   placeholder="sports, workshop, fundraiser (comma separated)">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Create Event
            </button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='events.php'">
                Cancel
            </button>
            <button type="button" class="btn btn-warning" onclick="saveDraft()">
                <i class="fas fa-save"></i> Save as Draft
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
        max-width: 300px;
    }
    
    .image-preview img {
        width: 100%;
        height: auto;
        border-radius: 5px;
        border: 1px solid #ddd;
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
    
    .btn-warning {
        background: #f39c12;
        color: white;
        border: none;
    }
    
    .btn-warning:hover {
        background: #e67e22;
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
    
    // Save as draft
    function saveDraft() {
        document.querySelector('select[name="status"]').value = 'draft';
        document.getElementById('eventForm').submit();
    }
    
    // Form validation
    document.getElementById('eventForm').addEventListener('submit', function(e) {
        const eventDate = document.querySelector('input[name="event_date"]').value;
        const today = new Date().toISOString().split('T')[0];
        
        // Check if event date is in the past for upcoming events
        const category = document.querySelector('select[name="category"]').value;
        if (category === 'upcoming' && eventDate < today) {
            e.preventDefault();
            if (!confirm('Event date is in the past. Do you want to continue?')) {
                return false;
            }
        }
        
        // Validate end time is after start time if both provided
        const startTime = document.querySelector('input[name="start_time"]').value;
        const endTime = document.querySelector('input[name="end_time"]').value;
        
        if (startTime && endTime && startTime >= endTime) {
            e.preventDefault();
            alert('End time must be after start time.');
            return false;
        }
        
        return true;
    });
    
    // Rich text editor (simplified)
    document.addEventListener('DOMContentLoaded', function() {
        const textarea = document.getElementById('description');
        if (textarea) {
            // Add toolbar for basic formatting
            const toolbar = document.createElement('div');
            toolbar.className = 'editor-toolbar';
            toolbar.innerHTML = `
                <button type="button" onclick="formatText('bold')"><i class="fas fa-bold"></i></button>
                <button type="button" onclick="formatText('italic')"><i class="fas fa-italic"></i></button>
                <button type="button" onclick="formatText('underline')"><i class="fas fa-underline"></i></button>
                <button type="button" onclick="insertBullet()"><i class="fas fa-list-ul"></i></button>
                <button type="button" onclick="insertNumber()"><i class="fas fa-list-ol"></i></button>
                <button type="button" onclick="insertLink()"><i class="fas fa-link"></i></button>
            `;
            textarea.parentNode.insertBefore(toolbar, textarea);
        }
    });
    
    function formatText(command) {
        const textarea = document.getElementById('description');
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
    
    function insertBullet() {
        const textarea = document.getElementById('description');
        const cursorPos = textarea.selectionStart;
        textarea.value = textarea.value.substring(0, cursorPos) + '\n• ' + textarea.value.substring(cursorPos);
    }
    
    function insertNumber() {
        const textarea = document.getElementById('description');
        const cursorPos = textarea.selectionStart;
        textarea.value = textarea.value.substring(0, cursorPos) + '\n1. ' + textarea.value.substring(cursorPos);
    }
    
    function insertLink() {
        const url = prompt('Enter URL:');
        if (url) {
            const text = prompt('Enter link text:', url);
            const textarea = document.getElementById('description');
            const cursorPos = textarea.selectionStart;
            textarea.value = textarea.value.substring(0, cursorPos) + 
                `<a href="${url}" target="_blank">${text}</a>` + 
                textarea.value.substring(cursorPos);
        }
    }
</script>