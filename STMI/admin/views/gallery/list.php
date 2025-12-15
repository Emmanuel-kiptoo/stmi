<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Management - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gallery-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .gallery-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .filter-controls {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 8px 15px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .filter-btn.active {
            background: #0e0c5e;
            color: white;
            border-color: #0e0c5e;
        }
        
        .filter-btn:hover {
            background: #f8f9fa;
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .gallery-item {
            background: white;
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .item-media {
            height: 180px;
            overflow: hidden;
            position: relative;
        }
        
        .item-media img, .item-media video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .item-media .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }
        
        .item-info {
            padding: 15px;
        }
        
        .item-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }
        
        .item-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 10px;
        }
        
        .item-category {
            background: #f0f0f0;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
        }
        
        .item-actions {
            display: flex;
            gap: 5px;
            margin-top: 10px;
        }
        
        .btn-small {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-edit { background: #3498db; color: white; }
        .btn-delete { background: #e74c3c; color: white; }
        .btn-view { background: #57cc99; color: white; }
        
        .upload-dropzone {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.3s;
            margin-bottom: 20px;
        }
        
        .upload-dropzone:hover {
            border-color: #0e0c5e;
        }
        
        .upload-dropzone i {
            font-size: 3rem;
            color: #666;
            margin-bottom: 15px;
        }
        
        .upload-dropzone h3 {
            margin-bottom: 10px;
            color: #333;
        }
        
        .upload-dropzone p {
            color: #666;
            margin-bottom: 15px;
        }
        
        .batch-upload {
            margin-top: 30px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #ddd;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1>Media Gallery</h1>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="window.location.href='gallery.php?action=add'">
                    <i class="fas fa-plus"></i> Add Media
                </button>
            </div>
        </div>
        
        <!-- Filter Controls -->
        <div class="filter-controls">
            <button class="filter-btn <?php echo ($_GET['type'] ?? 'all') === 'all' ? 'active' : ''; ?>" 
                    onclick="window.location.href='gallery.php'">
                All Media
            </button>
            <button class="filter-btn <?php echo ($_GET['type'] ?? '') === 'photos' ? 'active' : ''; ?>" 
                    onclick="window.location.href='gallery.php?type=photos'">
                <i class="fas fa-image"></i> Photos
            </button>
            <button class="filter-btn <?php echo ($_GET['type'] ?? '') === 'videos' ? 'active' : ''; ?>" 
                    onclick="window.location.href='gallery.php?type=videos'">
                <i class="fas fa-video"></i> Videos
            </button>
            
            <select class="filter-btn" onchange="window.location.href='gallery.php?category='+this.value">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" 
                        <?php echo ($_GET['category'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?> (<?php echo $cat['type']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Upload Dropzone -->
        <div class="upload-dropzone" id="uploadDropzone">
            <i class="fas fa-cloud-upload-alt"></i>
            <h3>Drag & Drop Files Here</h3>
            <p>Upload images or videos (Max 10MB per file)</p>
            <button class="btn btn-primary" onclick="document.getElementById('batchUpload').click()">
                <i class="fas fa-folder-open"></i> Select Files
            </button>
            <input type="file" id="batchUpload" multiple style="display: none;" 
                   accept="image/*,video/*" onchange="handleBatchUpload(this.files)">
        </div>
        
        <!-- Gallery Grid -->
        <div class="gallery-container">
            <?php if (empty($items)): ?>
                <div class="empty-state">
                    <i class="fas fa-images"></i>
                    <h3>No Media Items Found</h3>
                    <p>Upload your first photo or video to get started.</p>
                </div>
            <?php else: ?>
                <div class="gallery-grid">
                    <?php foreach ($items as $item): ?>
                        <div class="gallery-item">
                            <div class="item-media">
                                <?php if ($item['media_type'] === 'photo'): ?>
                                    <img src="../<?php echo htmlspecialchars($item['file_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['title']); ?>">
                                <?php else: ?>
                                    <video>
                                        <source src="../<?php echo htmlspecialchars($item['file_path']); ?>">
                                    </video>
                                    <div class="video-overlay">
                                        <i class="fas fa-play"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="item-info">
                                <h4 class="item-title">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </h4>
                                
                                <div class="item-meta">
                                    <span><?php echo date('M d, Y', strtotime($item['uploaded_at'])); ?></span>
                                    <span class="item-category">
                                        <?php echo htmlspecialchars($item['category_name']); ?>
                                    </span>
                                </div>
                                
                                <?php if (!empty($item['description'])): ?>
                                    <p style="font-size: 0.9rem; color: #666; margin-bottom: 10px;">
                                        <?php echo substr(htmlspecialchars($item['description']), 0, 60); ?>...
                                    </p>
                                <?php endif; ?>
                                
                                <div class="item-actions">
                                    <button class="btn-small btn-view" 
                                            onclick="window.open('../<?php echo $item['file_path']; ?>', '_blank')">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn-small btn-edit" 
                                            onclick="window.location.href='gallery.php?action=edit&id=<?php echo $item['id']; ?>'">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn-small btn-delete" 
                                            onclick="deleteItem(<?php echo $item['id']; ?>)">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <script>
        // Drag and drop functionality
        const dropzone = document.getElementById('uploadDropzone');
        
        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.style.borderColor = '#0e0c5e';
            dropzone.style.background = '#f8f9fa';
        });
        
        dropzone.addEventListener('dragleave', () => {
            dropzone.style.borderColor = '#ddd';
            dropzone.style.background = 'white';
        });
        
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.style.borderColor = '#ddd';
            dropzone.style.background = 'white';
            
            const files = e.dataTransfer.files;
            handleBatchUpload(files);
        });
        
        function handleBatchUpload(files) {
            if (files.length === 0) return;
            
            // Create form data
            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }
            
            // Show loading
            dropzone.innerHTML = '<i class="fas fa-spinner fa-spin"></i><h3>Uploading...</h3>';
            
            // Upload files
            fetch('handlers/batch_upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                    location.reload();
                }
            })
            .catch(error => {
                alert('Upload failed: ' + error);
                location.reload();
            });
        }
        
        function deleteItem(id) {
            if (confirm('Are you sure you want to delete this item?')) {
                window.location.href = 'gallery.php?action=delete&id=' + id;
            }
        }
        
        // Lazy load images
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('img[data-src]');
            
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        observer.unobserve(img);
                    }
                });
            });
            
            images.forEach(img => imageObserver.observe(img));
        });
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>