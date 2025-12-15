<!-- admin/views/gallery/add.php -->
<?php
// Get categories
$categories = $pdo->query("SELECT * FROM gallery_categories ORDER BY type, display_order")->fetchAll();
?>

<div class="form-container">
    <div class="form-header">
        <h2><i class="fas fa-plus-circle"></i> Add New Media Item</h2>
        <a href="gallery.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Gallery
        </a>
    </div>
    
    <form id="galleryForm" action="handlers/save_gallery_item.php" method="POST" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-group">
                <label>Media Type *</label>
                <select name="media_type" id="mediaType" required onchange="toggleMediaFields()">
                    <option value="">Select Type</option>
                    <option value="photo">Photo</option>
                    <option value="video">Video</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Category *</label>
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?> (<?php echo $cat['type']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label>Title *</label>
            <input type="text" name="title" required placeholder="Enter media title">
        </div>
        
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3" placeholder="Enter description (optional)"></textarea>
        </div>
        
        <!-- Photo Fields -->
        <div id="photoFields">
            <div class="form-group">
                <label>Upload Photo *</label>
                <div class="file-upload">
                    <input type="file" name="photo_file" id="photoFile" accept="image/*" onchange="previewImage(this)">
                    <label for="photoFile" class="upload-btn">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Photo
                    </label>
                    <div class="file-name" id="photoFileName">No file chosen</div>
                </div>
                <div class="image-preview" id="imagePreview" style="display: none;">
                    <img id="previewImage" src="#" alt="Preview">
                </div>
            </div>
        </div>
        
        <!-- Video Fields -->
        <div id="videoFields" style="display: none;">
            <div class="form-group">
                <label>Video File *</label>
                <div class="file-upload">
                    <input type="file" name="video_file" id="videoFile" accept="video/*" onchange="previewVideo(this)">
                    <label for="videoFile" class="upload-btn">
                        <i class="fas fa-cloud-upload-alt"></i> Choose Video
                    </label>
                    <div class="file-name" id="videoFileName">No file chosen</div>
                </div>
                <div class="video-preview" id="videoPreview" style="display: none;">
                    <video id="previewVideo" controls style="max-width: 300px;"></video>
                </div>
            </div>
            
            <div class="form-group">
                <label>Thumbnail Image (Optional)</label>
                <div class="file-upload">
                    <input type="file" name="thumbnail_file" id="thumbnailFile" accept="image/*">
                    <label for="thumbnailFile" class="upload-btn">
                        <i class="fas fa-image"></i> Choose Thumbnail
                    </label>
                    <div class="file-name" id="thumbnailFileName">No file chosen</div>
                </div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Event Date (Optional)</label>
                <input type="date" name="event_date">
            </div>
            
            <div class="form-group">
                <label>Display Order</label>
                <input type="number" name="display_order" value="0" min="0">
                <small>Lower numbers appear first</small>
            </div>
        </div>
        
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="published" selected>Published</option>
                <option value="draft">Draft</option>
            </select>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Media Item
            </button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='gallery.php'">
                Cancel
            </button>
        </div>
    </form>
</div>

<script>
function toggleMediaFields() {
    const mediaType = document.getElementById('mediaType').value;
    document.getElementById('photoFields').style.display = mediaType === 'photo' ? 'block' : 'none';
    document.getElementById('videoFields').style.display = mediaType === 'video' ? 'block' : 'none';
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        document.getElementById('photoFileName').textContent = input.files[0].name;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('previewImage');
            preview.src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function previewVideo(input) {
    if (input.files && input.files[0]) {
        document.getElementById('videoFileName').textContent = input.files[0].name;
        
        const video = document.getElementById('previewVideo');
        video.src = URL.createObjectURL(input.files[0]);
        document.getElementById('videoPreview').style.display = 'block';
    }
}
</script>