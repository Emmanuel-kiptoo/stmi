<?php
if (!$item): ?>
    <div class="alert alert-danger">
        Media item not found.
    </div>
    <a href="gallery.php" class="btn btn-secondary">Back to Gallery</a>
<?php else: 
    // Decode JSON data if exists
    $item['social_links'] = json_decode($item['social_links'] ?? '{}', true);
?>
<div class="form-container">
    <div class="form-header">
        <h2><i class="fas fa-edit"></i> Edit Media Item: <?php echo htmlspecialchars($item['title']); ?></h2>
        <a href="gallery.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Gallery
        </a>
    </div>
    
    <form id="galleryForm" action="handlers/save_gallery_item.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
        
        <div class="form-row">
            <div class="form-group">
                <label>Media Type *</label>
                <select name="media_type" id="mediaType" required onchange="toggleMediaFields()">
                    <option value="photo" <?php echo $item['media_type'] == 'photo' ? 'selected' : ''; ?>>Photo</option>
                    <option value="video" <?php echo $item['media_type'] == 'video' ? 'selected' : ''; ?>>Video</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Category *</label>
                <?php
                $categories = $pdo->query("SELECT * FROM gallery_categories ORDER BY type, display_order")->fetchAll();
                ?>
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                            <?php echo $item['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?> (<?php echo $cat['type']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label>Title *</label>
            <input type="text" name="title" required 
                   value="<?php echo htmlspecialchars($item['title']); ?>"
                   placeholder="Enter media title">
        </div>
        
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="3" 
                      placeholder="Enter description (optional)"><?php echo htmlspecialchars($item['description']); ?></textarea>
        </div>
        
        <!-- Current Media Preview -->
        <div class="current-media-preview">
            <h4>Current Media</h4>
            <?php if ($item['media_type'] === 'photo'): ?>
                <div class="photo-preview">
                    <img src="../<?php echo htmlspecialchars($item['file_path']); ?>" 
                         alt="<?php echo htmlspecialchars($item['title']); ?>" 
                         style="max-width: 300px; border-radius: 5px;">
                    <div class="preview-info">
                        <p><strong>Photo:</strong> <?php echo basename($item['file_path']); ?></p>
                        <label>
                            <input type="checkbox" name="remove_file" value="1">
                            Remove current file
                        </label>
                    </div>
                </div>
            <?php else: ?>
                <div class="video-preview">
                    <video controls style="max-width: 300px; border-radius: 5px;">
                        <source src="../<?php echo htmlspecialchars($item['file_path']); ?>" type="video/mp4">
                    </video>
                    <div class="preview-info">
                        <p><strong>Video:</strong> <?php echo basename($item['file_path']); ?></p>
                        <label>
                            <input type="checkbox" name="remove_file" value="1">
                            Remove current file
                        </label>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Photo Fields -->
        <div id="photoFields" style="<?php echo $item['media_type'] === 'photo' ? 'display: block;' : 'display: none;'; ?>">
            <div class="form-group">
                <label>Upload New Photo (Optional)</label>
                <div class="file-upload">
                    <input type="file" name="photo_file" id="photoFile" accept="image/*" onchange="previewNewImage(this)">
                    <label for="photoFile" class="upload-btn">
                        <i class="fas fa-cloud-upload-alt"></i> Choose New Photo
                    </label>
                    <div class="file-name" id="photoFileName">No file chosen</div>
                </div>
                <div class="image-preview" id="newImagePreview" style="display: none;">
                    <img id="previewNewImage" src="#" alt="New Preview">
                </div>
                <div class="form-help">Leave empty to keep current image. Max size: 10MB</div>
            </div>
        </div>
        
        <!-- Video Fields -->
        <div id="videoFields" style="<?php echo $item['media_type'] === 'video' ? 'display: block;' : 'display: none;'; ?>">
            <div class="form-group">
                <label>Upload New Video (Optional)</label>
                <div class="file-upload">
                    <input type="file" name="video_file" id="videoFile" accept="video/*" onchange="previewNewVideo(this)">
                    <label for="videoFile" class="upload-btn">
                        <i class="fas fa-cloud-upload-alt"></i> Choose New Video
                    </label>
                    <div class="file-name" id="videoFileName">No file chosen</div>
                </div>
                <div class="video-preview" id="newVideoPreview" style="display: none;">
                    <video id="previewNewVideo" controls style="max-width: 300px;"></video>
                </div>
                <div class="form-help">Leave empty to keep current video. Max size: 50MB</div>
            </div>
            
            <div class="form-group">
                <label>Thumbnail Image (Optional)</label>
                <?php if ($item['thumbnail_path']): ?>
                    <div class="current-thumbnail">
                        <img src="../<?php echo htmlspecialchars($item['thumbnail_path']); ?>" 
                             alt="Current Thumbnail" style="max-width: 150px; margin-bottom: 10px;">
                        <br>
                        <label>
                            <input type="checkbox" name="remove_thumbnail" value="1">
                            Remove current thumbnail
                        </label>
                    </div>
                <?php endif; ?>
                
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
                <input type="date" name="event_date" 
                       value="<?php echo $item['event_date'] ? date('Y-m-d', strtotime($item['event_date'])) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label>Display Order</label>
                <input type="number" name="display_order" value="<?php echo $item['display_order']; ?>" min="0">
                <small>Lower numbers appear first</small>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Views</label>
                <input type="number" value="<?php echo $item['views']; ?>" readonly>
                <small>Number of times viewed</small>
            </div>
            
            <div class="form-group">
                <label>Uploaded By</label>
                <?php
                $uploader_stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
                $uploader_stmt->execute([$item['uploaded_by']]);
                $uploader = $uploader_stmt->fetch();
                ?>
                <input type="text" value="<?php echo $uploader ? htmlspecialchars($uploader['full_name']) : 'Unknown'; ?>" readonly>
            </div>
        </div>
        
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="published" <?php echo $item['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                <option value="draft" <?php echo $item['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
            </select>
        </div>
        
        <div class="media-info-card">
            <h3>Media Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>File Path:</label>
                    <span><?php echo htmlspecialchars($item['file_path']); ?></span>
                </div>
                <div class="info-item">
                    <label>File Size:</label>
                    <span><?php echo $item['file_size'] ? formatBytes($item['file_size']) : 'Unknown'; ?></span>
                </div>
                <div class="info-item">
                    <label>Uploaded At:</label>
                    <span><?php echo date('F j, Y, g:i a', strtotime($item['uploaded_at'])); ?></span>
                </div>
                <div class="info-item">
                    <label>Last Viewed:</label>
                    <span>
                        <?php 
                        $last_view_stmt = $pdo->prepare("SELECT MAX(viewed_at) FROM media_views WHERE media_id = ?");
                        $last_view_stmt->execute([$item['id']]);
                        $last_view = $last_view_stmt->fetchColumn();
                        echo $last_view ? date('F j, Y, g:i a', strtotime($last_view)) : 'Never';
                        ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Media Item
            </button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='gallery.php'">
                Cancel
            </button>
            <button type="button" class="btn btn-info" onclick="previewMedia()">
                <i class="fas fa-eye"></i> Preview
            </button>
            <button type="button" class="btn btn-warning" onclick="resetViews()">
                <i class="fas fa-sync"></i> Reset Views
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
    
    .current-media-preview {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        border: 1px solid #eee;
    }
    
    .current-media-preview h4 {
        margin: 0 0 15px 0;
        color: #333;
    }
    
    .photo-preview, .video-preview {
        display: flex;
        gap: 20px;
        align-items: flex-start;
        flex-wrap: wrap;
    }
    
    .preview-info {
        flex: 1;
    }
    
    .preview-info p {
        margin: 0 0 10px 0;
        color: #666;
    }
    
    .preview-info label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: normal;
        color: #666;
        cursor: pointer;
    }
    
    .current-thumbnail {
        margin-bottom: 15px;
        padding: 10px;
        background: white;
        border-radius: 5px;
        border: 1px solid #ddd;
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
    
    .image-preview, .video-preview {
        margin-top: 15px;
    }
    
    .image-preview img, .video-preview video {
        max-width: 300px;
        border-radius: 5px;
        border: 1px solid #ddd;
    }
    
    .media-info-card {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin: 30px 0;
        border-left: 4px solid #0e0c5e;
    }
    
    .media-info-card h3 {
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
        word-break: break-all;
    }
    
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 40px;
        padding-top: 20px;
        border-top: 1px solid #eee;
        flex-wrap: wrap;
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
    
    .btn-info {
        background: #3498db;
        color: white;
        border: none;
    }
    
    .btn-info:hover {
        background: #2980b9;
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
    // Toggle media type fields
    function toggleMediaFields() {
        const mediaType = document.getElementById('mediaType').value;
        document.getElementById('photoFields').style.display = mediaType === 'photo' ? 'block' : 'none';
        document.getElementById('videoFields').style.display = mediaType === 'video' ? 'block' : 'none';
    }
    
    // Preview new image
    function previewNewImage(input) {
        if (input.files && input.files[0]) {
            document.getElementById('photoFileName').textContent = input.files[0].name;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('previewNewImage');
                preview.src = e.target.result;
                document.getElementById('newImagePreview').style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // Preview new video
    function previewNewVideo(input) {
        if (input.files && input.files[0]) {
            document.getElementById('videoFileName').textContent = input.files[0].name;
            
            const video = document.getElementById('previewNewVideo');
            video.src = URL.createObjectURL(input.files[0]);
            document.getElementById('newVideoPreview').style.display = 'block';
        }
    }
    
    // Preview media in new tab
    function previewMedia() {
        window.open('../<?php echo $item['file_path']; ?>', '_blank');
    }
    
    // Reset views count
    function resetViews() {
        if (confirm('Reset view count to 0?')) {
            fetch('handlers/reset_media_views.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    media_id: <?php echo $item['id']; ?>
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('View count reset successfully.');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }
    
    // Form validation
    document.getElementById('galleryForm').addEventListener('submit', function(e) {
        const mediaType = document.getElementById('mediaType').value;
        const removeFile = document.querySelector('input[name="remove_file"]:checked');
        
        // Check if trying to remove file without uploading new one
        if (removeFile && removeFile.value == '1') {
            if (mediaType === 'photo') {
                const photoFile = document.getElementById('photoFile').files[0];
                if (!photoFile) {
                    if (!confirm('You are removing the current file without uploading a new one. Continue?')) {
                        e.preventDefault();
                        return false;
                    }
                }
            } else if (mediaType === 'video') {
                const videoFile = document.getElementById('videoFile').files[0];
                if (!videoFile) {
                    if (!confirm('You are removing the current file without uploading a new one. Continue?')) {
                        e.preventDefault();
                        return false;
                    }
                }
            }
        }
        
        return true;
    });
</script>
<?php endif; ?>

<?php
// Helper function to format file sizes
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>