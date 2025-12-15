<?php if (!$event): ?>
    <div class="alert alert-danger">
        Event not found.
    </div>
    <a href="events.php" class="btn btn-secondary">Back to Events</a>
<?php else: ?>
<div class="form-container">
    <div class="form-header">
        <h2><i class="fas fa-edit"></i> Edit Event: <?php echo htmlspecialchars($event['title']); ?></h2>
        <a href="events.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Events
        </a>
    </div>
    
    <form id="eventForm" action="handlers/save_event.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
        
        <div class="form-group">
            <label>Event Title *</label>
            <input type="text" name="title" required 
                   value="<?php echo htmlspecialchars($event['title']); ?>"
                   placeholder="Enter event title" 
                   maxlength="200">
        </div>
        
        <div class="form-group">
            <label>Event Description *</label>
            <textarea id="description" name="description" rows="5" required 
                      placeholder="Enter detailed event description..."><?php echo htmlspecialchars($event['description']); ?></textarea>
            <div class="form-help">You can use HTML formatting</div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Event Date *</label>
                <input type="date" name="event_date" required 
                       value="<?php echo $event['event_date']; ?>">
            </div>
            
            <div class="form-group">
                <label>Start Time</label>
                <input type="time" name="start_time" 
                       value="<?php echo $event['start_time']; ?>">
            </div>
            
            <div class="form-group">
                <label>End Time</label>
                <input type="time" name="end_time" 
                       value="<?php echo $event['end_time']; ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Location *</label>
                <input type="text" name="location" required 
                       value="<?php echo htmlspecialchars($event['location']); ?>"
                       placeholder="Enter event location">
            </div>
            
            <div class="form-group">
                <label>Category *</label>
                <select name="category" required>
                    <option value="upcoming" <?php echo $event['category'] == 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                    <option value="ongoing" <?php echo $event['category'] == 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                    <option value="past" <?php echo $event['category'] == 'past' ? 'selected' : ''; ?>>Past</option>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    <option value="published" <?php echo $event['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                    <option value="draft" <?php echo $event['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="cancelled" <?php echo $event['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Registration Link</label>
                <input type="url" name="registration_link" 
                       value="<?php echo htmlspecialchars($event['registration_link']); ?>"
                       placeholder="https://example.com/register">
            </div>
        </div>
        
        <div class="form-group">
            <label>Featured Image</label>
            <?php if ($event['featured_image']): ?>
                <div class="current-image">
                    <img src="../<?php echo htmlspecialchars($event['featured_image']); ?>" 
                         alt="Current Image" style="max-width: 200px; margin-bottom: 10px;">
                    <br>
                    <label>
                        <input type="checkbox" name="remove_image" value="1">
                        Remove current image
                    </label>
                </div>
            <?php endif; ?>
            
            <div class="file-upload">
                <input type="file" name="featured_image" id="featuredImage" 
                       accept="image/*" onchange="previewImage(this)">
                <label for="featuredImage" class="upload-btn">
                    <i class="fas fa-cloud-upload-alt"></i> 
                    <?php echo $event['featured_image'] ? 'Change Image' : 'Choose Image'; ?>
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
                      placeholder="Any additional information about the event..."><?php echo htmlspecialchars($event['additional_info'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label>Tags (Optional)</label>
            <input type="text" name="tags" 
                   value="<?php echo htmlspecialchars($event['tags'] ?? ''); ?>"
                   placeholder="sports, workshop, fundraiser (comma separated)">
        </div>
        
        <div class="event-info-card">
            <h3>Event Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>Created:</label>
                    <span><?php echo date('F j, Y, g:i a', strtotime($event['created_at'])); ?></span>
                </div>
                <div class="info-item">
                    <label>Last Updated:</label>
                    <span><?php echo date('F j, Y, g:i a', strtotime($event['updated_at'])); ?></span>
                </div>
                <div class="info-item">
                    <label>Created By:</label>
                    <span>
                        <?php 
                        $stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
                        $stmt->execute([$event['created_by']]);
                        $creator = $stmt->fetch();
                        echo $creator ? htmlspecialchars($creator['full_name']) : 'Unknown';
                        ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Event
            </button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='events.php'">
                Cancel
            </button>
            <button type="button" class="btn btn-info" onclick="duplicateEvent()">
                <i class="fas fa-copy"></i> Duplicate Event
            </button>
        </div>
    </form>
</div>

<style>
    .current-image {
        margin-bottom: 15px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 5px;
        border: 1px solid #ddd;
    }
    
    .event-info-card {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin: 30px 0;
        border-left: 4px solid #0e0c5e;
    }
    
    .event-info-card h3 {
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
    
    .btn-info {
        background: #3498db;
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
    
    .btn-info:hover {
        background: #2980b9;
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
    
    // Duplicate event
    function duplicateEvent() {
        if (confirm('Create a duplicate of this event?')) {
            window.location.href = 'events.php?action=duplicate&id=<?php echo $event['id']; ?>';
        }
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
</script>
<?php endif; ?>