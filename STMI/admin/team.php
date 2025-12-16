<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'add':
    case 'edit':
        requirePermission('editor');
        handleTeamForm($action);
        break;
    case 'delete':
        requirePermission('admin');
        handleTeamDelete();
        break;
    default:
        listTeamMembers();
}

function listTeamMembers() {
    global $pdo;
    
    $stmt = $pdo->query("
        SELECT * FROM admin_team 
        ORDER BY display_order, department, name
    ");
    $members = $stmt->fetchAll();
    
    include 'includes/header.php';
    ?>
    <div class="admin-content">
        <div class="table-header">
            <h3><i class="fas fa-users"></i> Team Members</h3>
            <a href="team.php?action=add" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Add Member
            </a>
        </div>
        
        <div class="team-grid">
            <?php if (empty($members)): ?>
                <div class="empty-state">
                    <i class="fas fa-users fa-3x"></i>
                    <h4>No team members yet</h4>
                    <p>Add your first team member to get started.</p>
                </div>
            <?php else: ?>
                <?php foreach ($members as $member): ?>
                    <div class="team-card">
                        <div class="team-photo-container">
                            <div class="team-photo">
                                <?php if ($member['photo']): ?>
                                    <img src="../<?php echo htmlspecialchars($member['photo']); ?>" 
                                         alt="<?php echo htmlspecialchars($member['name']); ?>"
                                         style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;"
                                         onerror="this.onerror=null; this.src='data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=\"http://www.w3.org/2000/svg\" width=\"200\" height=\"200\" viewBox=\"0 0 200 200\"%3E%3Crect width=\"200\" height=\"200\" fill=\"%23f8f9fa\"/%3E%3Ctext x=\"50%25\" y=\"50%25\" font-family=\"Arial, sans-serif\" font-size=\"24\" fill=\"%236c757d\" text-anchor=\"middle\" dy=\".3em\"%3E%3Ctspan x=\"50%25\" dy=\"-0.6em\"%3E<?php echo urlencode(substr($member['name'], 0, 1)); ?>%3C/tspan%3E%3Ctspan x=\"50%25\" dy=\"1.2em\"%3E<?php echo urlencode(substr(explode(' ', $member['name'])[1] ?? '', 0, 1)); ?>%3C/tspan%3E%3C/text%3E%3C/svg%3E'">
                                <?php else: ?>
                                    <div class="photo-placeholder">
                                        <?php 
                                        $initials = '';
                                        $nameParts = explode(' ', $member['name']);
                                        if (count($nameParts) >= 2) {
                                            $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
                                        } else {
                                            $initials = strtoupper(substr($member['name'], 0, 2));
                                        }
                                        echo $initials;
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="member-status">
                                <span class="status-badge status-<?php echo $member['status']; ?>">
                                    <?php echo ucfirst($member['status']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="team-info">
                            <h4 class="member-name"><?php echo htmlspecialchars($member['name']); ?></h4>
                            <p class="member-position"><?php echo htmlspecialchars($member['position']); ?></p>
                            <p class="member-department">
                                <span class="department-badge department-<?php echo $member['department']; ?>">
                                    <i class="fas fa-users"></i>
                                    <?php echo ucfirst(str_replace('_', ' ', $member['department'])); ?>
                                </span>
                            </p>
                            
                            <?php if ($member['email']): ?>
                            <p class="member-email">
                                <i class="fas fa-envelope"></i>
                                <?php echo htmlspecialchars($member['email']); ?>
                            </p>
                            <?php endif; ?>
                            
                            <?php 
                            if ($member['social_links']) {
                                $socialLinks = json_decode($member['social_links'], true);
                                if ($socialLinks && (isset($socialLinks['linkedin']) || isset($socialLinks['twitter']) || isset($socialLinks['facebook']))):
                            ?>
                            <div class="member-social">
                                <?php if (!empty($socialLinks['linkedin'])): ?>
                                <a href="<?php echo htmlspecialchars($socialLinks['linkedin']); ?>" target="_blank" title="LinkedIn">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($socialLinks['twitter'])): ?>
                                <a href="<?php echo htmlspecialchars($socialLinks['twitter']); ?>" target="_blank" title="Twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($socialLinks['facebook'])): ?>
                                <a href="<?php echo htmlspecialchars($socialLinks['facebook']); ?>" target="_blank" title="Facebook">
                                    <i class="fab fa-facebook"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                            <?php endif; } ?>
                            
                            <div class="team-actions">
                                <a href="team.php?action=edit&id=<?php echo $member['id']; ?>" 
                                   class="btn btn-sm btn-secondary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <?php if (hasPermission('admin')): ?>
                                    <a href="team.php?action=delete&id=<?php echo $member['id']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this team member?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php
    include 'includes/footer.php';
}

function handleTeamForm($action) {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    $member = null;
    $socialLinks = ['linkedin' => '', 'twitter' => '', 'facebook' => ''];
    
    if ($id && $action === 'edit') {
        $stmt = $pdo->prepare("SELECT * FROM admin_team WHERE id = ?");
        $stmt->execute([$id]);
        $member = $stmt->fetch();
        
        if (!$member) {
            $_SESSION['error'] = 'Team member not found.';
            header('Location: team.php');
            exit();
        }
        
        if ($member['social_links']) {
            $decodedLinks = json_decode($member['social_links'], true);
            if ($decodedLinks) {
                $socialLinks = array_merge($socialLinks, $decodedLinks);
            }
        }
    }
    
    $error = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']);
        $position = trim($_POST['position']);
        $department = $_POST['department'];
        $bio = trim($_POST['bio']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $display_order = intval($_POST['display_order']);
        
        // Social links
        $socialLinks = [
            'linkedin' => trim($_POST['linkedin'] ?? ''),
            'twitter' => trim($_POST['twitter'] ?? ''),
            'facebook' => trim($_POST['facebook'] ?? '')
        ];
        
        $errors = [];
        if (empty($name)) $errors[] = 'Name is required';
        if (empty($position)) $errors[] = 'Position is required';
        
        if (empty($errors)) {
            try {
                $photoPath = $member['photo'] ?? null;
                
                // Handle photo upload
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
                    $uploadResult = uploadTeamPhoto($_FILES['photo']);
                    if ($uploadResult['success']) {
                        $photoPath = $uploadResult['path'];
                        
                        // Delete old photo if exists
                        if ($member && $member['photo'] && $photoPath !== $member['photo']) {
                            unlink('../' . $member['photo']);
                        }
                    } else {
                        throw new Exception($uploadResult['error']);
                    }
                }
                
                if ($action === 'add') {
                    $stmt = $pdo->prepare("
                        INSERT INTO admin_team 
                        (name, position, department, bio, photo, email, phone, social_links, display_order, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
                    ");
                    $stmt->execute([
                        $name, $position, $department, $bio, $photoPath,
                        $email, $phone, json_encode($socialLinks), $display_order
                    ]);
                    
                    $id = $pdo->lastInsertId();
                    logActivity('create', 'admin_team', $id, null, [
                        'name' => $name,
                        'position' => $position,
                        'department' => $department
                    ]);
                    
                    $_SESSION['message'] = 'Team member added successfully.';
                } else {
                    $oldValues = [
                        'name' => $member['name'],
                        'position' => $member['position'],
                        'department' => $member['department'],
                        'status' => $member['status']
                    ];
                    
                    $stmt = $pdo->prepare("
                        UPDATE admin_team SET
                        name = ?, position = ?, department = ?, bio = ?, photo = ?,
                        email = ?, phone = ?, social_links = ?, display_order = ?,
                        updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $name, $position, $department, $bio, $photoPath,
                        $email, $phone, json_encode($socialLinks), $display_order, $id
                    ]);
                    
                    logActivity('update', 'admin_team', $id, $oldValues, [
                        'name' => $name,
                        'position' => $position,
                        'department' => $department
                    ]);
                    
                    $_SESSION['message'] = 'Team member updated successfully.';
                }
                
                header('Location: team.php');
                exit();
                
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        } else {
            $error = implode('<br>', $errors);
        }
    }
    
    include 'includes/header.php';
    ?>
    <div class="admin-content">
        <div class="form-card">
            <h2>
                <i class="fas fa-<?php echo $action === 'add' ? 'user-plus' : 'user-edit'; ?>"></i>
                <?php echo $action === 'add' ? 'Add Team Member' : 'Edit Team Member'; ?>
            </h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" 
                               value="<?php echo htmlspecialchars($member['name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Position *</label>
                        <input type="text" name="position" class="form-control" 
                               value="<?php echo htmlspecialchars($member['position'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Department</label>
                        <select name="department" class="form-control">
                            <option value="leadership" <?php echo ($member['department'] ?? '') === 'leadership' ? 'selected' : ''; ?>>Leadership</option>
                            <option value="programs" <?php echo ($member['department'] ?? '') === 'programs' ? 'selected' : ''; ?>>Programs</option>
                            <option value="sports" <?php echo ($member['department'] ?? '') === 'sports' ? 'selected' : ''; ?>>Sports</option>
                            <option value="arts" <?php echo ($member['department'] ?? '') === 'arts' ? 'selected' : ''; ?>>Arts</option>
                            <option value="mentorship" <?php echo ($member['department'] ?? '') === 'mentorship' ? 'selected' : ''; ?>>Mentorship</option>
                            <option value="support" <?php echo ($member['department'] ?? '') === 'support' ? 'selected' : ''; ?>>Support</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Display Order</label>
                        <input type="number" name="display_order" class="form-control" 
                               value="<?php echo $member['display_order'] ?? 0; ?>" min="0">
                        <small class="text-muted">Lower numbers appear first</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Biography</label>
                    <textarea name="bio" class="form-control" rows="4" placeholder="Tell us about this team member..."><?php echo htmlspecialchars($member['bio'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($member['email'] ?? ''); ?>"
                               placeholder="team.member@example.com">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control" 
                               value="<?php echo htmlspecialchars($member['phone'] ?? ''); ?>"
                               placeholder="+254 700 000000">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Profile Photo</label>
                    
                    <?php if (isset($member) && $member['photo']): ?>
                        <div class="current-photo">
                            <img src="../<?php echo htmlspecialchars($member['photo']); ?>" 
                                 alt="Current photo" 
                                 id="currentPhotoPreview"
                                 style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 3px solid #dee2e6;">
                            <p class="text-muted" style="margin-top: 10px; font-size: 12px;">
                                Current photo (click to view full size)
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="image-upload-container" id="uploadContainer">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="upload-text">
                            <h4>Upload Profile Photo</h4>
                            <p>Drag & drop or click to browse</p>
                            <p class="text-muted" style="font-size: 12px; margin-top: 5px;">
                                Recommended: Square image, 400x400px minimum
                            </p>
                        </div>
                        <label class="upload-btn">
                            <input type="file" name="photo" id="photoUpload" 
                                   accept="image/*" onchange="previewImage(event)">
                            <span><i class="fas fa-folder-open"></i> Browse Files</span>
                        </label>
                    </div>
                    
                    <div class="image-preview" id="imagePreview" style="display: none;">
                        <h5>Preview:</h5>
                        <img id="previewImage" alt="Image preview" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                        <button type="button" class="btn btn-sm btn-danger mt-2" 
                                onclick="removeImage()">
                            <i class="fas fa-times"></i> Remove Image
                        </button>
                    </div>
                    
                    <div class="image-instructions">
                        <h5><i class="fas fa-info-circle"></i> Image Requirements:</h5>
                        <ul>
                            <li>Square format works best (1:1 ratio)</li>
                            <li>Minimum size: 400x400 pixels</li>
                            <li>Max file size: 5MB</li>
                            <li>Supported formats: JPG, PNG, GIF, WebP</li>
                            <li>Clear, professional headshot recommended</li>
                            <li>Image will be displayed as a circle</li>
                        </ul>
                    </div>
                </div>
                
                <h4>Social Links</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">LinkedIn</label>
                        <input type="url" name="linkedin" class="form-control" 
                               value="<?php echo htmlspecialchars($socialLinks['linkedin']); ?>"
                               placeholder="https://linkedin.com/in/username">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Twitter</label>
                        <input type="url" name="twitter" class="form-control" 
                               value="<?php echo htmlspecialchars($socialLinks['twitter']); ?>"
                               placeholder="https://twitter.com/username">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Facebook</label>
                        <input type="url" name="facebook" class="form-control" 
                               value="<?php echo htmlspecialchars($socialLinks['facebook']); ?>"
                               placeholder="https://facebook.com/username">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Member
                    </button>
                    <a href="team.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    // Image upload preview functionality
    const uploadContainer = document.getElementById('uploadContainer');
    const photoUpload = document.getElementById('photoUpload');
    const imagePreview = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');
    const currentPhotoPreview = document.getElementById('currentPhotoPreview');

    // Drag and drop functionality
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadContainer.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadContainer.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadContainer.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        uploadContainer.classList.add('drag-over');
    }

    function unhighlight(e) {
        uploadContainer.classList.remove('drag-over');
    }

    uploadContainer.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        photoUpload.files = files;
        handleFiles(files);
    }

    // Click to upload
    uploadContainer.addEventListener('click', () => {
        photoUpload.click();
    });

    // Handle file selection
    photoUpload.addEventListener('change', function(e) {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        if (files.length === 0) return;
        
        const file = files[0];
        
        // Validate file type
        const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            alert('Please upload a valid image file (JPG, PNG, GIF, or WebP).');
            return;
        }
        
        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            alert('Image size should be less than 5MB.');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            imagePreview.style.display = 'block';
            uploadContainer.style.display = 'none';
            
            // Hide current photo preview if exists
            if (currentPhotoPreview) {
                currentPhotoPreview.parentElement.style.display = 'none';
            }
        }
        reader.readAsDataURL(file);
    }

    // Remove image function
    function removeImage() {
        imagePreview.style.display = 'none';
        uploadContainer.style.display = 'block';
        photoUpload.value = '';
        
        // Show current photo preview again
        if (currentPhotoPreview) {
            currentPhotoPreview.parentElement.style.display = 'block';
        }
    }

    // View full size image
    if (currentPhotoPreview) {
        currentPhotoPreview.addEventListener('click', function() {
            window.open(this.src, '_blank');
        });
    }
    
    // Preview image function (for form onchange)
    function previewImage(event) {
        handleFiles(event.target.files);
    }
    </script>
    
    <style>
    .mt-2 { margin-top: 10px; }
    </style>
    
    <?php
    include 'includes/footer.php';
}

function uploadTeamPhoto($file) {
    $uploadDir = '../uploads/team/';
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Allowed image types
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'error' => 'Only JPG, PNG, GIF, and WebP images are allowed.'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'Image too large. Maximum size: 5MB'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '_' . time() . '.' . $extension;
    $filePath = $uploadDir . $fileName;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return [
            'success' => true,
            'path' => 'uploads/team/' . $fileName
        ];
    }
    
    return ['success' => false, 'error' => 'Failed to upload image.'];
}

function handleTeamDelete() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    
    try {
        // Get member details to delete photo
        $stmt = $pdo->prepare("SELECT * FROM admin_team WHERE id = ?");
        $stmt->execute([$id]);
        $member = $stmt->fetch();
        
        if (!$member) {
            $_SESSION['error'] = 'Team member not found.';
            header('Location: team.php');
            exit();
        }
        
        // Delete photo file if exists
        if ($member && $member['photo']) {
            $photoPath = '../' . $member['photo'];
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }
        
        // Log activity before deletion
        logActivity('delete', 'admin_team', $id, [
            'name' => $member['name'],
            'position' => $member['position'],
            'department' => $member['department']
        ], null);
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM admin_team WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['message'] = 'Team member deleted successfully.';
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error deleting team member: ' . $e->getMessage();
    }
    
    header('Location: team.php');
    exit();
}
?>