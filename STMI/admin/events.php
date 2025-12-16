<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

$action = $_GET['action'] ?? 'list';
$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

switch ($action) {
    case 'add':
    case 'edit':
        requirePermission('editor');
        handleEventForm($action);
        break;
    case 'delete':
        requirePermission('admin');
        handleEventDelete();
        break;
    case 'publish':
        requirePermission('editor');
        handleEventPublish();
        break;
    default:
        listEvents();
}

function listEvents() {
    global $pdo, $message, $error;
    
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';
    $status = $_GET['status'] ?? '';
    
    $sql = "SELECT * FROM admin_events WHERE 1=1";
    $params = [];
    
    if ($search) {
        $sql .= " AND (title LIKE ? OR description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($category) {
        $sql .= " AND category = ?";
        $params[] = $category;
    }
    
    if ($status) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY event_date DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll();
    
    include 'includes/header.php';
    ?>
    <div class="admin-content">
        <div class="table-header">
            <h3><i class="fas fa-calendar-alt"></i> Manage Events</h3>
            <div class="table-actions">
                <div class="table-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search events...">
                </div>
                <a href="events.php?action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Event
                </a>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>Banner</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($events)): ?>
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-calendar-times fa-3x"></i>
                                    <h4>No events found</h4>
                                    <p>Get started by adding your first event.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td>
                                    <?php if ($event['banner_image']): ?>
                                        <div class="event-banner-thumbnail">
                                            <img src="../<?php echo htmlspecialchars($event['banner_image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($event['title']); ?>"
                                                 onclick="viewBanner('<?php echo htmlspecialchars($event['banner_image']); ?>')">
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No banner</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($event['title']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars(substr($event['description'], 0, 50)); ?>...</small>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $event['category']; ?>">
                                        <?php echo ucfirst($event['category']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $event['status']; ?>">
                                        <?php echo ucfirst($event['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="events.php?action=edit&id=<?php echo $event['id']; ?>" 
                                           class="btn btn-sm btn-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($event['status'] === 'draft'): ?>
                                            <a href="events.php?action=publish&id=<?php echo $event['id']; ?>" 
                                               class="btn btn-sm btn-success" title="Publish">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (hasPermission('admin')): ?>
                                            <a href="events.php?action=delete&id=<?php echo $event['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this event?')"
                                               title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Banner Preview Modal -->
    <div class="modal fade" id="bannerModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Event Banner Preview</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="bannerPreview" src="" alt="Banner Preview" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function viewBanner(bannerPath) {
        document.getElementById('bannerPreview').src = '../' + bannerPath;
        $('#bannerModal').modal('show');
    }
    
    // Search functionality
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const search = this.value;
            if (search) {
                window.location.href = 'events.php?search=' + encodeURIComponent(search);
            } else {
                window.location.href = 'events.php';
            }
        }
    });
    </script>
    
    <style>
    .event-banner-thumbnail {
        width: 60px;
        height: 60px;
        border-radius: 4px;
        overflow: hidden;
        cursor: pointer;
        border: 1px solid #dee2e6;
    }
    
    .event-banner-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }
    
    .event-banner-thumbnail:hover img {
        transform: scale(1.1);
    }
    </style>
    <?php
    include 'includes/footer.php';
}

function handleEventForm($action) {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    $event = null;
    $error = '';
    
    if ($id && $action === 'edit') {
        $stmt = $pdo->prepare("SELECT * FROM admin_events WHERE id = ?");
        $stmt->execute([$id]);
        $event = $stmt->fetch();
        
        if (!$event) {
            $_SESSION['error'] = 'Event not found.';
            header('Location: events.php');
            exit();
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $event_date = $_POST['event_date'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $category = $_POST['category'] ?? '';
        $registration_link = trim($_POST['registration_link'] ?? '');
        
        // Handle banner upload
        $banner_image = $event['banner_image'] ?? ''; // Keep existing banner if no new upload
        
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === 0) {
            $uploadResult = uploadEventBanner($_FILES['banner_image'], $id ?: 'temp');
            
            if ($uploadResult['success']) {
                // Delete old banner if exists and we're editing
                if ($id && $event['banner_image']) {
                    @unlink('../' . $event['banner_image']);
                }
                
                $banner_image = $uploadResult['path'];
            } else {
                $error = $uploadResult['error'];
            }
        }
        
        // Validation
        $errors = [];
        if (empty($title)) $errors[] = 'Title is required';
        if (empty($description)) $errors[] = 'Description is required';
        if (empty($event_date)) $errors[] = 'Event date is required';
        if (empty($location)) $errors[] = 'Location is required';
        
        if (empty($errors) && empty($error)) {
            try {
                $pdo->beginTransaction();
                
                if ($action === 'add') {
                    $stmt = $pdo->prepare("
                        INSERT INTO admin_events 
                        (title, description, event_date, start_time, end_time, location, 
                         category, registration_link, banner_image, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $title, $description, $event_date, $start_time, $end_time, 
                        $location, $category, $registration_link, $banner_image, $_SESSION['admin_id']
                    ]);
                    $id = $pdo->lastInsertId();
                    
                    logActivity('create', 'admin_events', $id, null, [
                        'title' => $title,
                        'category' => $category,
                        'status' => 'draft'
                    ]);
                    
                    $_SESSION['message'] = 'Event created successfully.';
                } else {
                    $oldValues = [
                        'title' => $event['title'],
                        'description' => $event['description'],
                        'category' => $event['category'],
                        'status' => $event['status']
                    ];
                    
                    $stmt = $pdo->prepare("
                        UPDATE admin_events SET
                        title = ?, description = ?, event_date = ?, start_time = ?, end_time = ?,
                        location = ?, category = ?, registration_link = ?, banner_image = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $title, $description, $event_date, $start_time, $end_time,
                        $location, $category, $registration_link, $banner_image, $id
                    ]);
                    
                    logActivity('update', 'admin_events', $id, $oldValues, [
                        'title' => $title,
                        'description' => $description,
                        'category' => $category
                    ]);
                    
                    $_SESSION['message'] = 'Event updated successfully.';
                }
                
                $pdo->commit();
                header('Location: events.php');
                exit();
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Database error: ' . $e->getMessage();
            }
        } else {
            if (!empty($errors)) {
                $error = implode('<br>', $errors);
            }
        }
    }
    
    include 'includes/header.php';
    ?>
    <div class="admin-content">
        <div class="form-card">
            <h2>
                <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i>
                <?php echo $action === 'add' ? 'Add New Event' : 'Edit Event'; ?>
            </h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Event Title *</label>
                        <input type="text" name="title" class="form-control" 
                               value="<?php echo htmlspecialchars($event['title'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <select name="category" class="form-control" required>
                            <option value="upcoming" <?php echo ($event['category'] ?? '') === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                            <option value="ongoing" <?php echo ($event['category'] ?? '') === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                            <option value="past" <?php echo ($event['category'] ?? '') === 'past' ? 'selected' : ''; ?>>Past</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description *</label>
                    <textarea name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($event['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Event Date *</label>
                        <input type="date" name="event_date" class="form-control" 
                               value="<?php echo $event['event_date'] ?? date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Start Time *</label>
                        <input type="time" name="start_time" class="form-control" 
                               value="<?php echo $event['start_time'] ?? '09:00'; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">End Time *</label>
                        <input type="time" name="end_time" class="form-control" 
                               value="<?php echo $event['end_time'] ?? '17:00'; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Location *</label>
                    <input type="text" name="location" class="form-control" 
                           value="<?php echo htmlspecialchars($event['location'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Registration Link</label>
                    <input type="url" name="registration_link" class="form-control" 
                           value="<?php echo htmlspecialchars($event['registration_link'] ?? ''); ?>"
                           placeholder="https://example.com/register">
                </div>
                
                <!-- Banner Image Upload -->
                <div class="form-group">
                    <label class="form-label">Event Banner Image (Optional)</label>
                    
                    <?php if ($event && $event['banner_image']): ?>
                        <div class="current-banner mb-3">
                            <p><strong>Current Banner:</strong></p>
                            <div class="banner-preview">
                                <img src="../<?php echo htmlspecialchars($event['banner_image']); ?>" 
                                     alt="Current Banner" class="img-thumbnail" style="max-height: 150px;">
                                <div class="mt-2">
                                    <label class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" name="remove_banner" value="1">
                                        <span class="form-check-label">Remove current banner</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="banner-upload-area">
                        <div class="upload-container">
                            <input type="file" name="banner_image" id="bannerUpload" 
                                   accept="image/*" class="form-control-file">
                            <label for="bannerUpload" class="upload-label">
                                <i class="fas fa-cloud-upload-alt fa-2x"></i>
                                <span>Choose banner image</span>
                                <small>Recommended size: 1200x400 pixels • Max size: 2MB • JPG, PNG, GIF</small>
                            </label>
                            <div id="bannerPreview" class="upload-preview"></div>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Event
                    </button>
                    <a href="events.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    // Banner preview
    document.getElementById('bannerUpload').addEventListener('change', function(e) {
        const file = this.files[0];
        const preview = document.getElementById('bannerPreview');
        
        if (file) {
            // Validate file size (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                alert('File too large. Maximum size is 2MB.');
                this.value = '';
                preview.innerHTML = '';
                return;
            }
            
            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Invalid file type. Only JPG, PNG, GIF, and WebP images are allowed.');
                this.value = '';
                preview.innerHTML = '';
                return;
            }
            
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.innerHTML = `
                    <div class="preview-container">
                        <p><strong>Preview:</strong></p>
                        <img src="${e.target.result}" alt="Preview" class="img-thumbnail" style="max-height: 150px;">
                        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeBannerPreview()">
                            <i class="fas fa-times"></i> Remove
                        </button>
                    </div>
                `;
            };
            
            reader.readAsDataURL(file);
        }
    });
    
    function removeBannerPreview() {
        document.getElementById('bannerUpload').value = '';
        document.getElementById('bannerPreview').innerHTML = '';
    }
    
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const title = document.querySelector('input[name="title"]').value.trim();
        const description = document.querySelector('textarea[name="description"]').value.trim();
        const eventDate = document.querySelector('input[name="event_date"]').value;
        const location = document.querySelector('input[name="location"]').value.trim();
        
        if (!title || !description || !eventDate || !location) {
            e.preventDefault();
            alert('Please fill in all required fields (marked with *).');
            return false;
        }
        
        // Validate date is not in the past for upcoming events
        const category = document.querySelector('select[name="category"]').value;
        const selectedDate = new Date(eventDate);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (category === 'upcoming' && selectedDate < today) {
            if (!confirm('You selected "Upcoming" but the event date is in the past. Continue anyway?')) {
                e.preventDefault();
                return false;
            }
        }
        
        return true;
    });
    </script>
    
    <style>
    .banner-upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 20px;
        background: #f8f9fa;
        transition: border-color 0.3s;
    }
    
    .banner-upload-area:hover {
        border-color: #0e0c5e;
    }
    
    .upload-container {
        position: relative;
    }
    
    .upload-container input[type="file"] {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        opacity: 0;
        cursor: pointer;
    }
    
    .upload-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        color: #6c757d;
        cursor: pointer;
        text-align: center;
    }
    
    .upload-label i {
        color: #0e0c5e;
    }
    
    .upload-label span {
        font-weight: 500;
    }
    
    .upload-label small {
        font-size: 12px;
        color: #adb5bd;
    }
    
    .upload-preview {
        margin-top: 15px;
    }
    
    .preview-container {
        text-align: center;
    }
    
    .current-banner {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    
    .banner-preview {
        text-align: center;
    }
    </style>
    <?php
    include 'includes/footer.php';
}

function uploadEventBanner($file, $event_id) {
    $uploadDir = '../uploads/events/';
    
    // Create directories if they don't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Create event-specific directory
    $eventDir = $uploadDir . ($event_id ?: 'temp') . '/';
    if (!file_exists($eventDir)) {
        mkdir($eventDir, 0755, true);
    }
    
    // Check file size (2MB max)
    $maxSize = 2 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File too large. Maximum size: 2MB'];
    }
    
    // Check file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'error' => 'Only JPG, PNG, GIF, and WebP images are allowed'];
    }
    
    // Generate unique filename
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileName = 'banner_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $filePath = $eventDir . $fileName;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Resize image if needed (optional - optimize for web)
        if (function_exists('imagecreatefromjpeg')) {
            resizeImage($filePath, 1200, 400); // Resize to recommended banner size
        }
        
        return [
            'success' => true,
            'path' => 'uploads/events/' . ($event_id ?: 'temp') . '/' . $fileName,
            'name' => $file['name'],
            'size' => $file['size']
        ];
    }
    
    return ['success' => false, 'error' => 'Failed to upload file'];
}

function resizeImage($filePath, $maxWidth, $maxHeight) {
    // Get image info
    list($width, $height, $type) = getimagesize($filePath);
    
    // Calculate new dimensions
    $ratio = $width / $height;
    if ($maxWidth / $maxHeight > $ratio) {
        $newWidth = $maxHeight * $ratio;
        $newHeight = $maxHeight;
    } else {
        $newWidth = $maxWidth;
        $newHeight = $maxWidth / $ratio;
    }
    
    // Create image resource based on type
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($filePath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($filePath);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($filePath);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($filePath);
            break;
        default:
            return false;
    }
    
    // Create new image
    $destination = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency for PNG and GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagecolortransparent($destination, imagecolorallocatealpha($destination, 0, 0, 0, 127));
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
    }
    
    // Resize image
    imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Save image
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($destination, $filePath, 85); // 85% quality for web
            break;
        case IMAGETYPE_PNG:
            imagepng($destination, $filePath, 8); // Compression level 8
            break;
        case IMAGETYPE_GIF:
            imagegif($destination, $filePath);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($destination, $filePath, 85); // 85% quality for web
            break;
    }
    
    // Free memory
    imagedestroy($source);
    imagedestroy($destination);
    
    return true;
}

function handleEventDelete() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    
    try {
        // Get event details for logging and banner deletion
        $stmt = $pdo->prepare("SELECT * FROM admin_events WHERE id = ?");
        $stmt->execute([$id]);
        $event = $stmt->fetch();
        
        if (!$event) {
            $_SESSION['error'] = 'Event not found.';
            header('Location: events.php');
            exit();
        }
        
        // Delete banner file if exists
        if ($event['banner_image']) {
            @unlink('../' . $event['banner_image']);
            
            // Try to remove the event directory if empty
            $bannerPath = dirname('../' . $event['banner_image']);
            if (is_dir($bannerPath) && count(scandir($bannerPath)) == 2) { // Directory only contains . and ..
                @rmdir($bannerPath);
            }
        }
        
        // Delete event from database
        $stmt = $pdo->prepare("DELETE FROM admin_events WHERE id = ?");
        $stmt->execute([$id]);
        
        logActivity('delete', 'admin_events', $id, [
            'title' => $event['title'],
            'category' => $event['category']
        ], null);
        
        $_SESSION['message'] = 'Event deleted successfully.';
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error deleting event: ' . $e->getMessage();
    }
    
    header('Location: events.php');
    exit();
}

function handleEventPublish() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    
    try {
        $stmt = $pdo->prepare("UPDATE admin_events SET status = 'published' WHERE id = ?");
        $stmt->execute([$id]);
        
        logActivity('publish', 'admin_events', $id, ['status' => 'draft'], ['status' => 'published']);
        
        $_SESSION['message'] = 'Event published successfully.';
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error publishing event: ' . $e->getMessage();
    }
    
    header('Location: events.php');
    exit();
}
?>