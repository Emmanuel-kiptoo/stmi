<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

$action = $_GET['action'] ?? 'list';
$category = $_GET['category'] ?? 'all';
$file_type = $_GET['type'] ?? 'all';
$report_type = $_GET['report_type'] ?? 'all';

switch ($action) {
    case 'upload':
    case 'edit':
        requirePermission('editor');
        handleMediaForm($action);
        break;
    case 'delete':
        requirePermission('admin');
        handleMediaDelete();
        break;
    case 'view':
        handleMediaView();
        break;
    case 'download':
        handleMediaDownload();
        break;
    default:
        listMediaFiles();
}

function listMediaFiles() {
    global $pdo, $category, $file_type, $report_type;
    
    $search = $_GET['search'] ?? '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 24; // Items per page
    $offset = ($page - 1) * $per_page;
    
    // Build query
    $sql = "SELECT * FROM admin_media WHERE 1=1";
    $count_sql = "SELECT COUNT(*) as total FROM admin_media WHERE 1=1";
    $params = [];
    $count_params = [];
    
    if ($category !== 'all') {
        $sql .= " AND category = ?";
        $count_sql .= " AND category = ?";
        $params[] = $category;
        $count_params[] = $category;
    }
    
    if ($file_type !== 'all') {
        $sql .= " AND file_type = ?";
        $count_sql .= " AND file_type = ?";
        $params[] = $file_type;
        $count_params[] = $file_type;
    }
    
    // Add report type filter
    if ($category === 'reports' && $report_type !== 'all') {
        $sql .= " AND report_type = ?";
        $count_sql .= " AND report_type = ?";
        $params[] = $report_type;
        $count_params[] = $report_type;
    }
    
    if ($search) {
        $sql .= " AND (title LIKE ? OR description LIKE ? OR file_name LIKE ?)";
        $count_sql .= " AND (title LIKE ? OR description LIKE ? OR file_name LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $count_params[] = $search_term;
        $count_params[] = $search_term;
        $count_params[] = $search_term;
    }
    
    // Get total count
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($count_params);
    $total_result = $stmt->fetch();
    $total_items = $total_result['total'];
    $total_pages = ceil($total_items / $per_page);
    
    // Get paginated results
    $sql .= " ORDER BY uploaded_at DESC LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $media_items = $stmt->fetchAll();
    
    // Get statistics
    $stats_sql = "
        SELECT 
            COUNT(*) as total_files,
            SUM(file_size) as total_size,
            COUNT(CASE WHEN file_type = 'image' THEN 1 END) as image_count,
            COUNT(CASE WHEN file_type = 'video' THEN 1 END) as video_count,
            COUNT(CASE WHEN file_type = 'pdf' THEN 1 END) as pdf_count,
            COUNT(CASE WHEN file_type = 'document' THEN 1 END) as document_count,
            COUNT(CASE WHEN category = 'reports' THEN 1 END) as report_count
        FROM admin_media
    ";
    $stats_stmt = $pdo->query($stats_sql);
    $stats = $stats_stmt->fetch();
    
    include 'includes/header.php';
    ?>
    <div class="admin-content">
        <div class="dashboard-header">
            <h1><i class="fas fa-images"></i> Media Library</h1>
            <p>Manage all media files, images, documents, and resources</p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #667eea;">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['total_files']); ?></h3>
                    <p>Total Files</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #764ba2;">
                    <i class="fas fa-hdd"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo formatBytes($stats['total_size']); ?></h3>
                    <p>Total Size</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #57cc99;">
                    <i class="fas fa-image"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['image_count']); ?></h3>
                    <p>Images</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #ff9d0b;">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['report_count']); ?></h3>
                    <p>Reports</p>
                </div>
            </div>
        </div>
        
        <!-- Filters and Search -->
        <div class="media-filters">
            <div class="filters-left">
                <div class="filter-group">
                    <label>Category:</label>
                    <select id="categoryFilter" class="form-control">
                        <option value="all" <?php echo $category === 'all' ? 'selected' : ''; ?>>All Categories</option>
                        <option value="gallery" <?php echo $category === 'gallery' ? 'selected' : ''; ?>>Gallery</option>
                        <option value="articles" <?php echo $category === 'articles' ? 'selected' : ''; ?>>Articles</option>
                        <option value="newsletters" <?php echo $category === 'newsletters' ? 'selected' : ''; ?>>Newsletters</option>
                        <option value="resources" <?php echo $category === 'resources' ? 'selected' : ''; ?>>Resources</option>
                        <option value="reports" <?php echo $category === 'reports' ? 'selected' : ''; ?>>Reports</option>
                        <option value="events" <?php echo $category === 'events' ? 'selected' : ''; ?>>Events</option>
                        <option value="team" <?php echo $category === 'team' ? 'selected' : ''; ?>>Team</option>
                        <option value="general" <?php echo $category === 'general' ? 'selected' : ''; ?>>General</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>File Type:</label>
                    <select id="typeFilter" class="form-control">
                        <option value="all" <?php echo $file_type === 'all' ? 'selected' : ''; ?>>All Types</option>
                        <option value="image" <?php echo $file_type === 'image' ? 'selected' : ''; ?>>Images</option>
                        <option value="video" <?php echo $file_type === 'video' ? 'selected' : ''; ?>>Videos</option>
                        <option value="pdf" <?php echo $file_type === 'pdf' ? 'selected' : ''; ?>>PDFs</option>
                        <option value="document" <?php echo $file_type === 'document' ? 'selected' : ''; ?>>Documents</option>
                        <option value="audio" <?php echo $file_type === 'audio' ? 'selected' : ''; ?>>Audio</option>
                        <option value="archive" <?php echo $file_type === 'archive' ? 'selected' : ''; ?>>Archives</option>
                        <option value="other" <?php echo $file_type === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="filter-group" id="reportTypeFilter" style="<?php echo $category !== 'reports' ? 'display: none;' : ''; ?>">
                    <label>Report Type:</label>
                    <select id="reportTypeFilterSelect" class="form-control">
                        <option value="all" <?php echo $report_type === 'all' ? 'selected' : ''; ?>>All Report Types</option>
                        <option value="annual" <?php echo $report_type === 'annual' ? 'selected' : ''; ?>>Annual Reports</option>
                        <option value="financial" <?php echo $report_type === 'financial' ? 'selected' : ''; ?>>Financial Reports</option>
                        <option value="mel" <?php echo $report_type === 'mel' ? 'selected' : ''; ?>>M&E Reports</option>
                        <option value="general" <?php echo $report_type === 'general' ? 'selected' : ''; ?>>General Reports</option>
                    </select>
                </div>
            </div>
            
            <div class="filters-right">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search media..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button onclick="applyFilters()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                
                <a href="media.php?action=upload" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Upload Media
                </a>
            </div>
        </div>
        
        <!-- Media Grid -->
        <div class="media-grid-container">
            <?php if (empty($media_items)): ?>
                <div class="empty-state">
                    <i class="fas fa-images fa-3x"></i>
                    <h4>No media files found</h4>
                    <p>Upload your first media file to get started.</p>
                    <a href="media.php?action=upload" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload Media
                    </a>
                </div>
            <?php else: ?>
                <div class="media-grid">
                    <?php foreach ($media_items as $item): ?>
                        <div class="media-item category-<?php echo $item['category']; ?>" data-id="<?php echo $item['id']; ?>">
                            <div class="media-thumbnail">
                                <?php if ($item['file_type'] === 'image'): ?>
                                    <img src="../<?php echo htmlspecialchars($item['file_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['alt_text'] ?: $item['title']); ?>"
                                         loading="lazy">
                                <?php elseif ($item['file_type'] === 'video'): ?>
                                    <div class="file-icon video">
                                        <i class="fas fa-video"></i>
                                        <span>VIDEO</span>
                                    </div>
                                <?php elseif ($item['file_type'] === 'pdf'): ?>
                                    <div class="file-icon pdf">
                                        <i class="fas fa-file-pdf"></i>
                                        <span>PDF</span>
                                    </div>
                                <?php elseif ($item['file_type'] === 'document'): ?>
                                    <div class="file-icon document">
                                        <i class="fas fa-file-word"></i>
                                        <span>DOC</span>
                                    </div>
                                <?php elseif ($item['file_type'] === 'audio'): ?>
                                    <div class="file-icon audio">
                                        <i class="fas fa-file-audio"></i>
                                        <span>AUDIO</span>
                                    </div>
                                <?php elseif ($item['file_type'] === 'archive'): ?>
                                    <div class="file-icon archive">
                                        <i class="fas fa-file-archive"></i>
                                        <span>ZIP</span>
                                    </div>
                                <?php else: ?>
                                    <div class="file-icon other">
                                        <i class="fas fa-file"></i>
                                        <span>FILE</span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="media-overlay">
                                    <div class="overlay-actions">
                                        <a href="media.php?action=view&id=<?php echo $item['id']; ?>" 
                                           class="btn btn-sm btn-secondary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="media.php?action=edit&id=<?php echo $item['id']; ?>" 
                                           class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="../<?php echo htmlspecialchars($item['file_path']); ?>" 
                                           target="_blank" class="btn btn-sm btn-success" title="Open">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                        <?php if (hasPermission('admin')): ?>
                                            <a href="media.php?action=delete&id=<?php echo $item['id']; ?>" 
                                               class="btn btn-sm btn-danger" title="Delete"
                                               onclick="return confirm('Are you sure you want to delete this media file?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="media-category">
                                    <span class="category-badge category-<?php echo $item['category']; ?>">
                                        <?php echo ucfirst($item['category']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="media-info">
                                <h4 class="media-title" title="<?php echo htmlspecialchars($item['title']); ?>">
                                    <?php echo htmlspecialchars(truncateText($item['title'], 30)); ?>
                                    <?php if ($item['category'] === 'reports' && $item['report_type']): ?>
                                        <span class="report-badge <?php echo $item['report_type']; ?>">
                                            <?php echo ucfirst($item['report_type']); ?>
                                        </span>
                                    <?php endif; ?>
                                </h4>
                                
                                <?php if ($item['category'] === 'reports'): ?>
                                    <div class="report-meta">
                                        <?php if ($item['report_year']): ?>
                                            <span class="year-badge">
                                                <i class="fas fa-calendar"></i> <?php echo $item['report_year']; ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($item['report_pages']): ?>
                                            <span class="pages-badge">
                                                <i class="fas fa-file-alt"></i> <?php echo $item['report_pages']; ?> pages
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="media-meta">
                                    <span class="file-size">
                                        <i class="fas fa-weight"></i>
                                        <?php echo formatBytes($item['file_size']); ?>
                                    </span>
                                    <span class="file-type">
                                        <?php echo strtoupper(pathinfo($item['file_name'], PATHINFO_EXTENSION)); ?>
                                    </span>
                                </div>
                                
                                <div class="media-stats">
                                    <span class="stat-item">
                                        <i class="fas fa-eye"></i>
                                        <?php echo number_format($item['views']); ?>
                                    </span>
                                    <span class="stat-item">
                                        <i class="fas fa-download"></i>
                                        <?php echo number_format($item['downloads']); ?>
                                    </span>
                                </div>
                                
                                <div class="media-date">
                                    <i class="far fa-calendar"></i>
                                    <?php echo date('M d, Y', strtotime($item['uploaded_at'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <nav aria-label="Page navigation">
                        <ul class="pagination-list">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&category=<?php echo $category; ?>&type=<?php echo $file_type; ?>&search=<?php echo urlencode($search); ?>&report_type=<?php echo $report_type; ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&category=<?php echo $category; ?>&type=<?php echo $file_type; ?>&search=<?php echo urlencode($search); ?>&report_type=<?php echo $report_type; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&category=<?php echo $category; ?>&type=<?php echo $file_type; ?>&search=<?php echo urlencode($search); ?>&report_type=<?php echo $report_type; ?>">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    
                    <div class="pagination-info">
                        Showing <?php echo min(($page - 1) * $per_page + 1, $total_items); ?> - 
                        <?php echo min($page * $per_page, $total_items); ?> of 
                        <?php echo number_format($total_items); ?> items
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    function applyFilters() {
        const category = document.getElementById('categoryFilter').value;
        const type = document.getElementById('typeFilter').value;
        const search = document.getElementById('searchInput').value;
        const reportType = category === 'reports' ? document.getElementById('reportTypeFilterSelect').value : 'all';
        
        let url = 'media.php?';
        const params = [];
        
        if (category !== 'all') params.push('category=' + encodeURIComponent(category));
        if (type !== 'all') params.push('type=' + encodeURIComponent(type));
        if (search) params.push('search=' + encodeURIComponent(search));
        if (reportType !== 'all' && category === 'reports') params.push('report_type=' + encodeURIComponent(reportType));
        
        window.location.href = url + params.join('&');
    }
    
    // Auto-apply filters on change
    document.getElementById('categoryFilter').addEventListener('change', function() {
        const reportTypeFilter = document.getElementById('reportTypeFilter');
        reportTypeFilter.style.display = this.value === 'reports' ? 'block' : 'none';
        applyFilters();
    });
    
    document.getElementById('typeFilter').addEventListener('change', applyFilters);
    document.getElementById('reportTypeFilterSelect').addEventListener('change', applyFilters);
    
    // Search on Enter key
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyFilters();
        }
    });
    
    // Bulk selection (optional future feature)
    document.addEventListener('DOMContentLoaded', function() {
        const mediaItems = document.querySelectorAll('.media-item');
        
        mediaItems.forEach(item => {
            item.addEventListener('click', function(e) {
                // Don't trigger if clicking on links/buttons
                if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || 
                    e.target.closest('a') || e.target.closest('button')) {
                    return;
                }
                
                this.classList.toggle('selected');
            });
        });
    });
    </script>
    <?php
    include 'includes/footer.php';
}

function handleMediaForm($action) {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    $media = null;
    
    if ($id && $action === 'edit') {
        $stmt = $pdo->prepare("SELECT * FROM admin_media WHERE id = ?");
        $stmt->execute([$id]);
        $media = $stmt->fetch();
        
        if (!$media) {
            $_SESSION['error'] = 'Media file not found.';
            header('Location: media.php');
            exit();
        }
    }
    
    $error = '';
    $success_message = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $category = $_POST['category'];
        $sub_category = trim($_POST['sub_category']);
        $alt_text = trim($_POST['alt_text']);
        $caption = trim($_POST['caption']);
        $status = $_POST['status'];
        $report_type = $_POST['report_type'] ?? null;
        $report_year = $_POST['report_year'] ?? null;
        $report_audit_date = $_POST['report_audit_date'] ?: null;
        $report_pages = $_POST['report_pages'] ?? null;
        $report_summary = trim($_POST['report_summary'] ?? '');
        
        $errors = [];
        if (empty($title)) $errors[] = 'Title is required';
        
        // Handle file upload for new media
        if ($action === 'add' && (!isset($_FILES['media_file']) || $_FILES['media_file']['error'] === 4)) {
            $errors[] = 'Please select a file to upload';
        }
        
        if (empty($errors)) {
            try {
                $filePath = $media['file_path'] ?? null;
                $fileName = $media['file_name'] ?? null;
                $fileSize = $media['file_size'] ?? 0;
                $fileType = $media['file_type'] ?? null;
                $mimeType = $media['mime_type'] ?? null;
                
                // Handle file upload for new item or replacement
                if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === 0) {
                    $uploadResult = uploadMediaFile($_FILES['media_file']);
                    if ($uploadResult['success']) {
                        // Delete old file if exists (for edit/replacement)
                        if ($media && $media['file_path'] && $uploadResult['path'] !== $media['file_path']) {
                            unlink('../' . $media['file_path']);
                        }
                        
                        $filePath = $uploadResult['path'];
                        $fileName = $uploadResult['name'];
                        $fileSize = $uploadResult['size'];
                        $fileType = $uploadResult['type'];
                        $mimeType = $uploadResult['mime_type'];
                    } else {
                        throw new Exception($uploadResult['error']);
                    }
                }
                
                if ($action === 'add') {
                    $stmt = $pdo->prepare("
                        INSERT INTO admin_media 
                        (title, description, file_path, file_name, file_size, file_type, mime_type, 
                         category, sub_category, alt_text, caption, status, uploaded_by,
                         report_type, report_year, report_audit_date, report_pages, report_summary)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $title, $description, $filePath, $fileName, $fileSize, $fileType, $mimeType,
                        $category, $sub_category, $alt_text, $caption, $status, $_SESSION['admin_id'],
                        $report_type, $report_year, $report_audit_date, $report_pages, $report_summary
                    ]);
                    
                    $id = $pdo->lastInsertId();
                    logActivity('create', 'admin_media', $id, null, [
                        'title' => $title,
                        'category' => $category,
                        'file_type' => $fileType
                    ]);
                    
                    $success_message = 'Media file uploaded successfully.';
                } else {
                    $oldValues = [
                        'title' => $media['title'],
                        'category' => $media['category'],
                        'status' => $media['status']
                    ];
                    
                    $stmt = $pdo->prepare("
                        UPDATE admin_media SET
                        title = ?, description = ?, file_path = ?, file_name = ?, file_size = ?, 
                        file_type = ?, mime_type = ?, category = ?, sub_category = ?, 
                        alt_text = ?, caption = ?, status = ?,
                        report_type = ?, report_year = ?, report_audit_date = ?, 
                        report_pages = ?, report_summary = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $title, $description, $filePath, $fileName, $fileSize, $fileType, $mimeType,
                        $category, $sub_category, $alt_text, $caption, $status,
                        $report_type, $report_year, $report_audit_date, $report_pages, $report_summary, $id
                    ]);
                    
                    logActivity('update', 'admin_media', $id, $oldValues, [
                        'title' => $title,
                        'category' => $category,
                        'status' => $status
                    ]);
                    
                    $success_message = 'Media file updated successfully.';
                }
                
                // Redirect to media list with success message
                $_SESSION['message'] = $success_message;
                header('Location: media.php');
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
                <i class="fas fa-<?php echo $action === 'add' ? 'upload' : 'edit'; ?>"></i>
                <?php echo $action === 'add' ? 'Upload Media' : 'Edit Media'; ?>
            </h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" 
                               value="<?php echo htmlspecialchars($media['title'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category" id="categorySelect" class="form-control" onchange="toggleReportFields()">
                            <option value="general" <?php echo ($media['category'] ?? '') === 'general' ? 'selected' : ''; ?>>General</option>
                            <option value="gallery" <?php echo ($media['category'] ?? '') === 'gallery' ? 'selected' : ''; ?>>Gallery</option>
                            <option value="articles" <?php echo ($media['category'] ?? '') === 'articles' ? 'selected' : ''; ?>>Articles</option>
                            <option value="newsletters" <?php echo ($media['category'] ?? '') === 'newsletters' ? 'selected' : ''; ?>>Newsletters</option>
                            <option value="resources" <?php echo ($media['category'] ?? '') === 'resources' ? 'selected' : ''; ?>>Resources</option>
                            <option value="reports" <?php echo ($media['category'] ?? '') === 'reports' ? 'selected' : ''; ?>>Reports</option>
                            <option value="events" <?php echo ($media['category'] ?? '') === 'events' ? 'selected' : ''; ?>>Events</option>
                            <option value="team" <?php echo ($media['category'] ?? '') === 'team' ? 'selected' : ''; ?>>Team</option>
                        </select>
                    </div>
                </div>
                
                <!-- Report-specific fields -->
                <div id="reportFields" style="display: <?php echo ($media['category'] ?? '') === 'reports' ? 'block' : 'none'; ?>;">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Report Type</label>
                            <select name="report_type" class="form-control">
                                <option value="annual" <?php echo ($media['report_type'] ?? '') === 'annual' ? 'selected' : ''; ?>>Annual Report</option>
                                <option value="financial" <?php echo ($media['report_type'] ?? '') === 'financial' ? 'selected' : ''; ?>>Financial Report</option>
                                <option value="mel" <?php echo ($media['report_type'] ?? '') === 'mel' ? 'selected' : ''; ?>>Monitoring & Evaluation Report</option>
                                <option value="general" <?php echo ($media['report_type'] ?? '') === 'general' ? 'selected' : ''; ?>>General Report</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Report Year</label>
                            <select name="report_year" class="form-control">
                                <option value="">Select Year</option>
                                <?php for($y = date('Y'); $y >= 2010; $y--): ?>
                                    <option value="<?php echo $y; ?>" <?php echo ($media['report_year'] ?? '') == $y ? 'selected' : ''; ?>>
                                        <?php echo $y; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Number of Pages</label>
                            <input type="number" name="report_pages" class="form-control" 
                                   value="<?php echo htmlspecialchars($media['report_pages'] ?? ''); ?>"
                                   min="1" max="1000" placeholder="e.g., 45">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Audit Date (for financial reports)</label>
                            <input type="date" name="report_audit_date" class="form-control" 
                                   value="<?php echo htmlspecialchars($media['report_audit_date'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Executive Summary</label>
                        <textarea name="report_summary" class="form-control" rows="3" 
                                  placeholder="Brief summary of the report's key findings..."><?php echo htmlspecialchars($media['report_summary'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Sub-category (optional)</label>
                        <input type="text" name="sub_category" class="form-control" 
                               value="<?php echo htmlspecialchars($media['sub_category'] ?? ''); ?>"
                               placeholder="e.g., sports, arts, training">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="published" <?php echo ($media['status'] ?? 'published') === 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="draft" <?php echo ($media['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="archived" <?php echo ($media['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" 
                              placeholder="Brief description of the media file..."><?php echo htmlspecialchars($media['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Alt Text (for images)</label>
                        <input type="text" name="alt_text" class="form-control" 
                               value="<?php echo htmlspecialchars($media['alt_text'] ?? ''); ?>"
                               placeholder="Descriptive text for screen readers">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Caption</label>
                        <input type="text" name="caption" class="form-control" 
                               value="<?php echo htmlspecialchars($media['caption'] ?? ''); ?>"
                               placeholder="Short caption for display">
                    </div>
                </div>
                
                <!-- File Upload Section -->
                <div class="form-group">
                    <label class="form-label">
                        <?php echo $action === 'add' ? 'Media File *' : 'Replace File (optional)'; ?>
                    </label>
                    
                    <?php if ($action === 'edit' && $media): ?>
                        <div class="current-file">
                            <div class="file-info">
                                <div class="file-icon-preview">
                                    <?php if ($media['file_type'] === 'image'): ?>
                                        <i class="fas fa-image"></i>
                                    <?php elseif ($media['file_type'] === 'video'): ?>
                                        <i class="fas fa-video"></i>
                                    <?php elseif ($media['file_type'] === 'pdf'): ?>
                                        <i class="fas fa-file-pdf"></i>
                                    <?php elseif ($media['file_type'] === 'document'): ?>
                                        <i class="fas fa-file-word"></i>
                                    <?php else: ?>
                                        <i class="fas fa-file"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="file-details">
                                    <h5><?php echo htmlspecialchars($media['file_name']); ?></h5>
                                    <p>
                                        <span class="file-size"><?php echo formatBytes($media['file_size']); ?></span>
                                        <span class="file-type"><?php echo strtoupper($media['file_type']); ?></span>
                                        <span class="file-date">Uploaded: <?php echo date('M d, Y', strtotime($media['uploaded_at'])); ?></span>
                                    </p>
                                </div>
                            </div>
                            <div class="file-actions">
                                <a href="../<?php echo htmlspecialchars($media['file_path']); ?>" 
                                   target="_blank" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-external-link-alt"></i> View
                                </a>
                                <a href="media.php?action=download&id=<?php echo $media['id']; ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        </div>
                        
                        <div class="file-preview">
                            <?php if ($media['file_type'] === 'image'): ?>
                                <img src="../<?php echo htmlspecialchars($media['file_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($media['alt_text'] ?: $media['title']); ?>"
                                     style="max-width: 300px; max-height: 200px; border-radius: 8px; margin-top: 10px;">
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="file-upload-container" id="uploadContainer">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="upload-text">
                            <h4>Choose a file</h4>
                            <p>Drag & drop or click to browse</p>
                            <p class="text-muted" style="font-size: 12px; margin-top: 5px;">
                                Max file size: 50MB
                            </p>
                        </div>
                        <label class="upload-btn">
                            <input type="file" name="media_file" id="mediaFileUpload" 
                                   accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar" 
                                   <?php echo $action === 'add' ? 'required' : ''; ?>>
                            <span><i class="fas fa-folder-open"></i> Browse Files</span>
                        </label>
                    </div>
                    
                    <div class="file-preview" id="filePreview" style="display: none;">
                        <h5>Selected File:</h5>
                        <div id="previewContent"></div>
                        <button type="button" class="btn btn-sm btn-danger mt-2" 
                                onclick="removeFile()">
                            <i class="fas fa-times"></i> Remove File
                        </button>
                    </div>
                    
                    <div class="file-instructions">
                        <h5><i class="fas fa-info-circle"></i> Supported Files:</h5>
                        <ul>
                            <li><strong>Images:</strong> JPG, PNG, GIF, WebP, SVG (Max: 10MB)</li>
                            <li><strong>Videos:</strong> MP4, MOV, AVI (Max: 50MB)</li>
                            <li><strong>Documents:</strong> PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT</li>
                            <li><strong>Archives:</strong> ZIP, RAR</li>
                            <li><strong>Audio:</strong> MP3, WAV</li>
                        </ul>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 
                        <?php echo $action === 'add' ? 'Upload Media' : 'Save Changes'; ?>
                    </button>
                    <a href="media.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    // Toggle report-specific fields
    function toggleReportFields() {
        const category = document.getElementById('categorySelect').value;
        const reportFields = document.getElementById('reportFields');
        
        if (category === 'reports') {
            reportFields.style.display = 'block';
        } else {
            reportFields.style.display = 'none';
        }
    }
    
    // File upload preview functionality
    const uploadContainer = document.getElementById('uploadContainer');
    const mediaFileUpload = document.getElementById('mediaFileUpload');
    const filePreview = document.getElementById('filePreview');
    const previewContent = document.getElementById('previewContent');

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
        mediaFileUpload.files = files;
        handleFiles(files);
    }

    // Click to upload
    uploadContainer.addEventListener('click', () => {
        mediaFileUpload.click();
    });

    // Handle file selection
    mediaFileUpload.addEventListener('change', function(e) {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        if (files.length === 0) return;
        
        const file = files[0];
        
        // Validate file size (50MB max)
        const maxSize = 50 * 1024 * 1024; // 50MB
        if (file.size > maxSize) {
            alert('File size should be less than 50MB.');
            mediaFileUpload.value = '';
            return;
        }
        
        // Show preview
        previewContent.innerHTML = '';
        
        // Create preview based on file type
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewContent.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <img src="${e.target.result}" alt="Preview" 
                             style="max-width: 100px; max-height: 100px; border-radius: 8px;">
                        <div>
                            <strong>${file.name}</strong><br>
                            <small>${formatBytes(file.size)} • ${file.type}</small>
                        </div>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        } else {
            // For non-image files
            const fileIcon = getFileIcon(file.type);
            previewContent.innerHTML = `
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="width: 80px; height: 80px; background: #f8f9fa; border-radius: 8px; 
                         display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <i class="${fileIcon}" style="font-size: 32px; color: #6c757d;"></i>
                        <small style="font-size: 10px; margin-top: 5px;">${getFileExtension(file.name).toUpperCase()}</small>
                    </div>
                    <div>
                        <strong>${file.name}</strong><br>
                        <small>${formatBytes(file.size)} • ${file.type}</small>
                    </div>
                </div>
            `;
        }
        
        filePreview.style.display = 'block';
        uploadContainer.style.display = 'none';
    }

    // Helper functions for file preview
    function getFileIcon(mimeType) {
        if (mimeType.startsWith('video/')) return 'fas fa-video';
        if (mimeType === 'application/pdf') return 'fas fa-file-pdf';
        if (mimeType.includes('document') || mimeType.includes('word')) return 'fas fa-file-word';
        if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'fas fa-file-excel';
        if (mimeType.includes('powerpoint') || mimeType.includes('presentation')) return 'fas fa-file-powerpoint';
        if (mimeType.includes('audio/')) return 'fas fa-file-audio';
        if (mimeType.includes('zip') || mimeType.includes('rar')) return 'fas fa-file-archive';
        if (mimeType.includes('text/')) return 'fas fa-file-alt';
        return 'fas fa-file';
    }

    function getFileExtension(filename) {
        return filename.split('.').pop();
    }

    // Format bytes helper (for JavaScript)
    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    // Remove file function
    function removeFile() {
        filePreview.style.display = 'none';
        uploadContainer.style.display = 'block';
        mediaFileUpload.value = '';
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleReportFields();
    });
    </script>
    
    <style>
    .file-info {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    
    .file-icon-preview {
        width: 60px;
        height: 60px;
        background: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #0e0c5e;
    }
    
    .file-details h5 {
        margin: 0 0 5px 0;
    }
    
    .file-details p {
        margin: 0;
        color: #6c757d;
        font-size: 13px;
    }
    
    .file-size, .file-type, .file-date {
        display: inline-block;
        margin-right: 15px;
    }
    
    .file-actions {
        margin-left: auto;
        display: flex;
        gap: 10px;
    }
    
    /* Report Type Badges */
    .report-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        margin-left: 5px;
    }
    
    .report-badge.annual {
        background-color: #d4edda;
        color: #155724;
    }
    
    .report-badge.financial {
        background-color: #cce5ff;
        color: #004085;
    }
    
    .report-badge.mel {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .report-badge.general {
        background-color: #fff3cd;
        color: #856404;
    }
    
    /* Style for report items in grid */
    .media-item.category-reports .media-title {
        color: #2c3e50;
        font-weight: 600;
    }
    
    .report-meta {
        display: flex;
        gap: 8px;
        margin: 5px 0;
        flex-wrap: wrap;
    }
    
    .year-badge, .pages-badge {
        display: inline-block;
        padding: 2px 6px;
        background: #f8f9fa;
        border-radius: 12px;
        font-size: 11px;
        color: #6c757d;
    }
    
    .year-badge i, .pages-badge i {
        margin-right: 3px;
        font-size: 10px;
    }
    </style>
    
    <?php
    include 'includes/footer.php';
}

function uploadMediaFile($file) {
    $uploadDir = '../uploads/media/';
    
    // Create directories if they don't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Create yearly and monthly subdirectories
    $year = date('Y');
    $month = date('m');
    $yearDir = $uploadDir . $year . '/';
    $monthDir = $yearDir . $month . '/';
    
    if (!file_exists($yearDir)) mkdir($yearDir, 0755, true);
    if (!file_exists($monthDir)) mkdir($monthDir, 0755, true);
    
    // Define allowed file types and max sizes
    $allowedTypes = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
        'video' => ['mp4', 'mov', 'avi', 'wmv', 'flv'],
        'pdf' => ['pdf'],
        'document' => ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'],
        'audio' => ['mp3', 'wav', 'ogg'],
        'archive' => ['zip', 'rar', '7z']
    ];
    
    $maxSizes = [
        'image' => 10 * 1024 * 1024, // 10MB
        'video' => 50 * 1024 * 1024, // 50MB
        'pdf' => 20 * 1024 * 1024, // 20MB
        'document' => 10 * 1024 * 1024, // 10MB
        'audio' => 20 * 1024 * 1024, // 20MB
        'archive' => 50 * 1024 * 1024, // 50MB
        'other' => 10 * 1024 * 1024 // 10MB
    ];
    
    // Get file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Determine file type
    $fileType = 'other';
    $mimeType = $file['type'];
    
    foreach ($allowedTypes as $type => $extensions) {
        if (in_array($extension, $extensions)) {
            $fileType = $type;
            break;
        }
    }
    
    // Check if file type is allowed
    if ($fileType === 'other') {
        // Check by MIME type for safety
        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
            'video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv',
            'application/pdf',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'audio/mpeg', 'audio/wav', 'audio/ogg',
            'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed'
        ];
        
        if (!in_array($mimeType, $allowedMimes)) {
            return ['success' => false, 'error' => 'File type not allowed.'];
        }
    }
    
    // Check file size
    if ($file['size'] > $maxSizes[$fileType]) {
        return ['success' => false, 'error' => 'File too large. Maximum size: ' . formatBytes($maxSizes[$fileType])];
    }
    
    // Generate unique filename
    $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
    $safeName = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $originalName);
    $fileName = $safeName . '_' . uniqid() . '.' . $extension;
    $filePath = $monthDir . $fileName;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // For images, get dimensions if GD is available
        $dimensions = null;
        if ($fileType === 'image' && function_exists('getimagesize')) {
            $dimensions = @getimagesize($filePath);
        }
        
        return [
            'success' => true,
            'path' => 'uploads/media/' . $year . '/' . $month . '/' . $fileName,
            'name' => $file['name'],
            'size' => $file['size'],
            'type' => $fileType,
            'mime_type' => $mimeType,
            'dimensions' => $dimensions
        ];
    }
    
    return ['success' => false, 'error' => 'Failed to upload file.'];
}

function handleMediaDelete() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    
    try {
        // Get media details
        $stmt = $pdo->prepare("SELECT * FROM admin_media WHERE id = ?");
        $stmt->execute([$id]);
        $media = $stmt->fetch();
        
        if (!$media) {
            $_SESSION['error'] = 'Media file not found.';
            header('Location: media.php');
            exit();
        }
        
        // Delete file from server
        if ($media['file_path']) {
            $filePath = '../' . $media['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        // Log activity before deletion
        logActivity('delete', 'admin_media', $id, [
            'title' => $media['title'],
            'file_name' => $media['file_name'],
            'file_type' => $media['file_type']
        ], null);
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM admin_media WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['message'] = 'Media file deleted successfully.';
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error deleting media file: ' . $e->getMessage();
    }
    
    header('Location: media.php');
    exit();
}

function handleMediaView() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    
    $stmt = $pdo->prepare("SELECT * FROM admin_media WHERE id = ?");
    $stmt->execute([$id]);
    $media = $stmt->fetch();
    
    if (!$media) {
        $_SESSION['error'] = 'Media file not found.';
        header('Location: media.php');
        exit();
    }
    
    // Update view count
    $updateStmt = $pdo->prepare("UPDATE admin_media SET views = views + 1, last_accessed = NOW() WHERE id = ?");
    $updateStmt->execute([$id]);
    
    include 'includes/header.php';
    ?>
    <div class="admin-content">
        <div class="media-detail-view">
            <div class="detail-header">
                <a href="media.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Media Library
                </a>
                <div class="header-actions">
                    <a href="media.php?action=edit&id=<?php echo $media['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="../<?php echo htmlspecialchars($media['file_path']); ?>" 
                       target="_blank" class="btn btn-success">
                        <i class="fas fa-external-link-alt"></i> Open
                    </a>
                    <a href="media.php?action=download&id=<?php echo $media['id']; ?>" class="btn btn-info">
                        <i class="fas fa-download"></i> Download
                    </a>
                    <?php if (hasPermission('admin')): ?>
                        <a href="media.php?action=delete&id=<?php echo $media['id']; ?>" 
                           class="btn btn-danger"
                           onclick="return confirm('Are you sure you want to delete this media file?')">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="detail-content">
                <div class="media-preview">
                    <?php if ($media['file_type'] === 'image'): ?>
                        <div class="image-preview-large">
                            <img src="../<?php echo htmlspecialchars($media['file_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($media['alt_text'] ?: $media['title']); ?>"
                                 style="max-width: 100%; max-height: 500px; border-radius: 8px;">
                        </div>
                    <?php elseif ($media['file_type'] === 'video'): ?>
                        <div class="video-preview">
                            <div class="video-placeholder">
                                <i class="fas fa-video fa-5x"></i>
                                <h4>Video File</h4>
                                <p><?php echo htmlspecialchars($media['file_name']); ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="file-preview-large">
                            <div class="file-icon-large">
                                <?php if ($media['file_type'] === 'pdf'): ?>
                                    <i class="fas fa-file-pdf fa-5x"></i>
                                <?php elseif ($media['file_type'] === 'document'): ?>
                                    <i class="fas fa-file-word fa-5x"></i>
                                <?php elseif ($media['file_type'] === 'audio'): ?>
                                    <i class="fas fa-file-audio fa-5x"></i>
                                <?php elseif ($media['file_type'] === 'archive'): ?>
                                    <i class="fas fa-file-archive fa-5x"></i>
                                <?php else: ?>
                                    <i class="fas fa-file fa-5x"></i>
                                <?php endif; ?>
                            </div>
                            <h4><?php echo strtoupper($media['file_type']); ?> File</h4>
                            <p><?php echo htmlspecialchars($media['file_name']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="media-details">
                    <div class="detail-section">
                        <h3><?php echo htmlspecialchars($media['title']); ?></h3>
                        <?php if ($media['caption']): ?>
                            <p class="caption"><?php echo htmlspecialchars($media['caption']); ?></p>
                        <?php endif; ?>
                        <?php if ($media['description']): ?>
                            <p class="description"><?php echo nl2br(htmlspecialchars($media['description'])); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($media['category'] === 'reports'): ?>
                        <div class="detail-section">
                            <h4>Report Details</h4>
                            <div class="report-details-grid">
                                <?php if ($media['report_type']): ?>
                                    <div class="detail-item">
                                        <label>Report Type:</label>
                                        <span class="report-badge <?php echo $media['report_type']; ?>">
                                            <?php echo ucfirst($media['report_type']); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($media['report_year']): ?>
                                    <div class="detail-item">
                                        <label>Year:</label>
                                        <span><?php echo $media['report_year']; ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($media['report_pages']): ?>
                                    <div class="detail-item">
                                        <label>Pages:</label>
                                        <span><?php echo $media['report_pages']; ?> pages</span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($media['report_audit_date']): ?>
                                    <div class="detail-item">
                                        <label>Audit Date:</label>
                                        <span><?php echo date('F j, Y', strtotime($media['report_audit_date'])); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($media['report_summary']): ?>
                                    <div class="detail-item full-width">
                                        <label>Executive Summary:</label>
                                        <p><?php echo nl2br(htmlspecialchars($media['report_summary'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>File Name:</label>
                            <span><?php echo htmlspecialchars($media['file_name']); ?></span>
                        </div>
                        
                        <div class="detail-item">
                            <label>File Type:</label>
                            <span class="file-type-badge file-type-<?php echo $media['file_type']; ?>">
                                <?php echo strtoupper($media['file_type']); ?>
                            </span>
                        </div>
                        
                        <div class="detail-item">
                            <label>File Size:</label>
                            <span><?php echo formatBytes($media['file_size']); ?></span>
                        </div>
                        
                        <div class="detail-item">
                            <label>Category:</label>
                            <span class="category-badge category-<?php echo $media['category']; ?>">
                                <?php echo ucfirst($media['category']); ?>
                            </span>
                        </div>
                        
                        <div class="detail-item">
                            <label>Sub-category:</label>
                            <span><?php echo $media['sub_category'] ? htmlspecialchars($media['sub_category']) : '—'; ?></span>
                        </div>
                        
                        <div class="detail-item">
                            <label>Status:</label>
                            <span class="status-badge status-<?php echo $media['status']; ?>">
                                <?php echo ucfirst($media['status']); ?>
                            </span>
                        </div>
                        
                        <div class="detail-item">
                            <label>Uploaded By:</label>
                            <span><?php echo getUploaderName($media['uploaded_by']); ?></span>
                        </div>
                        
                        <div class="detail-item">
                            <label>Upload Date:</label>
                            <span><?php echo date('F j, Y g:i A', strtotime($media['uploaded_at'])); ?></span>
                        </div>
                        
                        <div class="detail-item">
                            <label>Last Accessed:</label>
                            <span><?php echo $media['last_accessed'] ? date('F j, Y g:i A', strtotime($media['last_accessed'])) : 'Never'; ?></span>
                        </div>
                    </div>
                    
                    <div class="detail-stats">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo number_format($media['views']); ?></div>
                            <div class="stat-label">Views</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number"><?php echo number_format($media['downloads']); ?></div>
                            <div class="stat-label">Downloads</div>
                        </div>
                    </div>
                    
                    <?php if ($media['alt_text']): ?>
                    <div class="detail-section">
                        <h4>Alt Text</h4>
                        <p><?php echo htmlspecialchars($media['alt_text']); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="detail-section">
                        <h4>File Path</h4>
                        <div class="file-path">
                            <code><?php echo htmlspecialchars($media['file_path']); ?></code>
                            <button class="btn btn-sm btn-secondary" onclick="copyToClipboard('<?php echo htmlspecialchars($media['file_path']); ?>')">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('File path copied to clipboard!');
        });
    }
    </script>
    
    <style>
    .media-detail-view {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .detail-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #dee2e6;
    }
    
    .header-actions {
        display: flex;
        gap: 10px;
    }
    
    .detail-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }
    
    .media-preview {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 300px;
    }
    
    .video-preview, .file-preview-large {
        text-align: center;
        color: #6c757d;
    }
    
    .video-preview i, .file-preview-large i {
        margin-bottom: 15px;
        color: #0e0c5e;
    }
    
    .media-details {
        padding: 10px 0;
    }
    
    .detail-section {
        margin-bottom: 25px;
    }
    
    .detail-section h3 {
        margin: 0 0 10px 0;
        color: #333;
    }
    
    .detail-section h4 {
        margin: 0 0 10px 0;
        color: #666;
        font-size: 16px;
    }
    
    .caption {
        font-style: italic;
        color: #6c757d;
        margin-bottom: 15px;
    }
    
    .description {
        line-height: 1.6;
        color: #555;
    }
    
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
        margin-bottom: 25px;
    }
    
    .report-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 10px;
    }
    
    .report-details-grid .full-width {
        grid-column: 1 / -1;
    }
    
    .detail-item {
        display: flex;
        flex-direction: column;
    }
    
    .detail-item label {
        font-weight: 600;
        color: #666;
        font-size: 13px;
        margin-bottom: 5px;
    }
    
    .detail-item span {
        color: #333;
    }
    
    .file-type-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .file-type-image { background: #d4edda; color: #155724; }
    .file-type-video { background: #cce5ff; color: #004085; }
    .file-type-pdf { background: #f8d7da; color: #721c24; }
    .file-type-document { background: #fff3cd; color: #856404; }
    .file-type-audio { background: #d1ecf1; color: #0c5460; }
    .file-type-archive { background: #e2e3e5; color: #383d41; }
    .file-type-other { background: #f8f9fa; color: #6c757d; }
    
    .detail-stats {
        display: flex;
        gap: 30px;
        margin-bottom: 25px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .stat-item {
        text-align: center;
    }
    
    .stat-number {
        font-size: 28px;
        font-weight: 700;
        color: #0e0c5e;
    }
    
    .stat-label {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .file-path {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .file-path code {
        flex: 1;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 4px;
        font-family: monospace;
        font-size: 13px;
        word-break: break-all;
    }
    </style>
    <?php
    include 'includes/footer.php';
}

function handleMediaDownload() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    
    $stmt = $pdo->prepare("SELECT * FROM admin_media WHERE id = ?");
    $stmt->execute([$id]);
    $media = $stmt->fetch();
    
    if (!$media) {
        $_SESSION['error'] = 'Media file not found.';
        header('Location: media.php');
        exit();
    }
    
    $filePath = '../' . $media['file_path'];
    
    if (!file_exists($filePath)) {
        $_SESSION['error'] = 'File not found on server.';
        header('Location: media.php');
        exit();
    }
    
    // Update download count
    $updateStmt = $pdo->prepare("UPDATE admin_media SET downloads = downloads + 1, last_accessed = NOW() WHERE id = ?");
    $updateStmt->execute([$id]);
    
    // Set headers for file download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($media['file_name']) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    
    // Clear output buffer
    ob_clean();
    flush();
    
    // Read file
    readfile($filePath);
    exit();
}

function getUploaderName($userId) {
    global $pdo;
    
    if (!$userId) return 'System';
    
    $stmt = $pdo->prepare("SELECT full_name FROM admin_users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    return $user ? $user['full_name'] : 'Unknown';
}

// Helper functions
function formatBytes($bytes, $decimals = 2) {
    if ($bytes == 0) return '0 Bytes';
    
    $k = 1024;
    $dm = $decimals < 0 ? 0 : $decimals;
    $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    
    $i = floor(log($bytes) / log($k));
    
    return number_format($bytes / pow($k, $i), $dm) . ' ' . $sizes[$i];
}

function truncateText($text, $length = 50) {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . '...';
}
?>