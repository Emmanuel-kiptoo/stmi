<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

$action = $_GET['action'] ?? 'list';
$status = $_GET['status'] ?? 'all';

switch ($action) {
    case 'view':
        requirePermission('editor');
        handleMessageView();
        break;
    case 'reply':
        requirePermission('editor');
        handleMessageReply();
        break;
    case 'mark-read':
        requirePermission('editor');
        handleMarkRead();
        break;
    case 'mark-unread':
        requirePermission('editor');
        handleMarkUnread();
        break;
    case 'delete':
        requirePermission('admin');
        handleMessageDelete();
        break;
    case 'export':
        requirePermission('admin');
        handleMessagesExport();
        break;
    default:
        listMessages();
}

function listMessages() {
    global $pdo, $status;
    
    $search = $_GET['search'] ?? '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 20; // Messages per page
    $offset = ($page - 1) * $per_page;
    
    // Build query
    $sql = "SELECT * FROM contact_messages WHERE 1=1";
    $count_sql = "SELECT COUNT(*) as total FROM contact_messages WHERE 1=1";
    $params = [];
    $count_params = [];
    
    if ($status !== 'all') {
        $sql .= " AND status = ?";
        $count_sql .= " AND status = ?";
        $params[] = $status;
        $count_params[] = $status;
    }
    
    if ($search) {
        $sql .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
        $count_sql .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $count_params[] = $search_term;
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
    $sql .= " ORDER BY 
        CASE WHEN status = 'unread' THEN 0 ELSE 1 END,
        created_at DESC 
        LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $messages = $stmt->fetchAll();
    
    // Get statistics
    $stats_sql = "
        SELECT 
            COUNT(*) as total_messages,
            COUNT(CASE WHEN status = 'unread' THEN 1 END) as unread_count,
            COUNT(CASE WHEN status = 'read' THEN 1 END) as read_count,
            COUNT(CASE WHEN status = 'replied' THEN 1 END) as replied_count,
            COUNT(CASE WHEN status = 'archived' THEN 1 END) as archived_count,
            COUNT(CASE WHEN is_urgent = 1 THEN 1 END) as urgent_count,
            DATE(created_at) as date
        FROM contact_messages 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date DESC
        LIMIT 30
    ";
    $stats_stmt = $pdo->query($stats_sql);
    $daily_stats = $stats_stmt->fetchAll();
    
    // Get category statistics
    $category_sql = "
        SELECT 
            category,
            COUNT(*) as count
        FROM contact_messages 
        WHERE category IS NOT NULL
        GROUP BY category
        ORDER BY count DESC
    ";
    $category_stmt = $pdo->query($category_sql);
    $category_stats = $category_stmt->fetchAll();
    
    include 'includes/header.php';
    ?>
    <div class="admin-content">
        <div class="dashboard-header">
            <h1><i class="fas fa-envelope"></i> Messages</h1>
            <p>Manage contact form submissions and inquiries</p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #667eea;">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($total_items); ?></h3>
                    <p>Total Messages</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #57cc99;">
                    <i class="fas fa-envelope-open"></i>
                </div>
                <div class="stat-info">
                    <h3>
                        <?php 
                        $unread_stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'");
                        $unread = $unread_stmt->fetch();
                        echo number_format($unread['count']); 
                        ?>
                    </h3>
                    <p>Unread Messages</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #ff9d0b;">
                    <i class="fas fa-reply"></i>
                </div>
                <div class="stat-info">
                    <h3>
                        <?php 
                        $replied_stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'replied'");
                        $replied = $replied_stmt->fetch();
                        echo number_format($replied['count']); 
                        ?>
                    </h3>
                    <p>Replied Messages</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #764ba2;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3>
                        <?php 
                        $urgent_stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_urgent = 1");
                        $urgent = $urgent_stmt->fetch();
                        echo number_format($urgent['count']); 
                        ?>
                    </h3>
                    <p>Urgent Messages</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="action-buttons">
                <a href="messages.php?status=unread" class="btn btn-warning">
                    <i class="fas fa-envelope"></i> Unread Messages
                </a>
                <a href="messages.php?status=replied" class="btn btn-success">
                    <i class="fas fa-reply"></i> Replied Messages
                </a>
                <a href="messages.php?status=archived" class="btn btn-secondary">
                    <i class="fas fa-archive"></i> Archived Messages
                </a>
                <a href="messages.php?export=csv" class="btn btn-info">
                    <i class="fas fa-download"></i> Export CSV
                </a>
                <?php if (hasPermission('admin')): ?>
                    <a href="javascript:void(0)" class="btn btn-danger" onclick="bulkDelete()">
                        <i class="fas fa-trash"></i> Bulk Delete
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Filters and Search -->
        <div class="messages-filters">
            <div class="filters-left">
                <div class="filter-group">
                    <label>Status:</label>
                    <select id="statusFilter" class="form-control">
                        <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Messages</option>
                        <option value="unread" <?php echo $status === 'unread' ? 'selected' : ''; ?>>Unread</option>
                        <option value="read" <?php echo $status === 'read' ? 'selected' : ''; ?>>Read</option>
                        <option value="replied" <?php echo $status === 'replied' ? 'selected' : ''; ?>>Replied</option>
                        <option value="archived" <?php echo $status === 'archived' ? 'selected' : ''; ?>>Archived</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Category:</label>
                    <select id="categoryFilter" class="form-control">
                        <option value="all">All Categories</option>
                        <?php foreach ($category_stats as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['category']); ?>">
                                <?php echo htmlspecialchars($cat['category']); ?> (<?php echo $cat['count']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="filters-right">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search messages..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button onclick="applyFilters()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Messages Table -->
        <div class="messages-container">
            <?php if (empty($messages)): ?>
                <div class="empty-state">
                    <i class="fas fa-envelope fa-3x"></i>
                    <h4>No messages found</h4>
                    <p>All caught up! No messages match your current filters.</p>
                    <?php if ($status !== 'all' || $search): ?>
                        <a href="messages.php" class="btn btn-primary">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="messages-table-container">
                    <table class="messages-table">
                        <thead>
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th width="50">Status</th>
                                <th>Sender</th>
                                <th>Subject</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $message): ?>
                                <tr class="message-row status-<?php echo $message['status']; ?> <?php echo $message['is_urgent'] ? 'urgent' : ''; ?>" data-id="<?php echo $message['id']; ?>">
                                    <td>
                                        <input type="checkbox" class="message-checkbox" value="<?php echo $message['id']; ?>">
                                    </td>
                                    <td>
                                        <div class="status-indicator status-<?php echo $message['status']; ?>" 
                                             title="<?php echo ucfirst($message['status']); ?>">
                                            <?php if ($message['status'] === 'unread'): ?>
                                                <i class="fas fa-envelope"></i>
                                            <?php elseif ($message['status'] === 'read'): ?>
                                                <i class="fas fa-envelope-open"></i>
                                            <?php elseif ($message['status'] === 'replied'): ?>
                                                <i class="fas fa-reply"></i>
                                            <?php elseif ($message['status'] === 'archived'): ?>
                                                <i class="fas fa-archive"></i>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($message['is_urgent']): ?>
                                            <div class="urgent-indicator" title="Urgent">
                                                <i class="fas fa-exclamation-circle"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="sender-info">
                                            <div class="sender-name">
                                                <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                                <?php if ($message['is_urgent']): ?>
                                                    <span class="urgent-badge">URGENT</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="sender-email">
                                                <i class="fas fa-envelope"></i>
                                                <?php echo htmlspecialchars($message['email']); ?>
                                            </div>
                                            <?php if ($message['phone']): ?>
                                                <div class="sender-phone">
                                                    <i class="fas fa-phone"></i>
                                                    <?php echo htmlspecialchars($message['phone']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="message-subject">
                                            <strong><?php echo htmlspecialchars($message['subject']); ?></strong>
                                            <div class="message-preview">
                                                <?php echo htmlspecialchars(truncateText($message['message'], 100)); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($message['category']): ?>
                                            <span class="category-badge">
                                                <?php echo htmlspecialchars($message['category']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">General</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="message-date">
                                            <?php echo date('M d, Y', strtotime($message['created_at'])); ?>
                                            <div class="message-time">
                                                <?php echo date('g:i A', strtotime($message['created_at'])); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="message-actions">
                                            <a href="messages.php?action=view&id=<?php echo $message['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="View Message">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="messages.php?action=reply&id=<?php echo $message['id']; ?>" 
                                               class="btn btn-sm btn-success" title="Reply">
                                                <i class="fas fa-reply"></i>
                                            </a>
                                            <?php if ($message['status'] === 'unread'): ?>
                                                <a href="messages.php?action=mark-read&id=<?php echo $message['id']; ?>" 
                                                   class="btn btn-sm btn-secondary" title="Mark as Read">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="messages.php?action=mark-unread&id=<?php echo $message['id']; ?>" 
                                                   class="btn btn-sm btn-warning" title="Mark as Unread">
                                                    <i class="fas fa-envelope"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (hasPermission('admin')): ?>
                                                <a href="messages.php?action=delete&id=<?php echo $message['id']; ?>" 
                                                   class="btn btn-sm btn-danger" title="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this message?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <nav aria-label="Page navigation">
                        <ul class="pagination-list">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">
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
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    
                    <div class="pagination-info">
                        Showing <?php echo min(($page - 1) * $per_page + 1, $total_items); ?> - 
                        <?php echo min($page * $per_page, $total_items); ?> of 
                        <?php echo number_format($total_items); ?> messages
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- Statistics Chart -->
        <div class="chart-container">
            <div class="chart-card">
                <h3><i class="fas fa-chart-line"></i> Messages Overview (Last 30 Days)</h3>
                <div class="chart-wrapper">
                    <canvas id="messagesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    function applyFilters() {
        const status = document.getElementById('statusFilter').value;
        const category = document.getElementById('categoryFilter').value;
        const search = document.getElementById('searchInput').value;
        
        let url = 'messages.php?';
        const params = [];
        
        if (status !== 'all') params.push('status=' + encodeURIComponent(status));
        if (category !== 'all') params.push('category=' + encodeURIComponent(category));
        if (search) params.push('search=' + encodeURIComponent(search));
        
        window.location.href = url + params.join('&');
    }
    
    // Auto-apply filters on change
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('categoryFilter').addEventListener('change', applyFilters);
    
    // Search on Enter key
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyFilters();
        }
    });
    
    // Bulk selection
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.message-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    
    function bulkDelete() {
        const selected = Array.from(document.querySelectorAll('.message-checkbox:checked'))
            .map(cb => cb.value);
        
        if (selected.length === 0) {
            alert('Please select at least one message to delete.');
            return;
        }
        
        if (confirm(`Are you sure you want to delete ${selected.length} selected message(s)?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'messages.php?action=bulk-delete';
            
            selected.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    // Messages Chart
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (!empty($daily_stats)): ?>
        const ctx = document.getElementById('messagesChart').getContext('2d');
        const dates = <?php echo json_encode(array_column($daily_stats, 'date')); ?>;
        const counts = <?php echo json_encode(array_column($daily_stats, 'total_messages')); ?>;
        
        // Reverse to show chronological order
        dates.reverse();
        counts.reverse();
        
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates.map(date => {
                    const d = new Date(date);
                    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }),
                datasets: [{
                    label: 'Messages Received',
                    data: counts,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Messages'
                        },
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                }
            }
        });
        <?php endif; ?>
        
        // Mark row as read on click
        document.querySelectorAll('.message-row').forEach(row => {
            row.addEventListener('click', function(e) {
                // Don't trigger if clicking on links/buttons/checkboxes
                if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || 
                    e.target.tagName === 'INPUT' || e.target.closest('a') || 
                    e.target.closest('button') || e.target.closest('.message-actions')) {
                    return;
                }
                
                const messageId = this.getAttribute('data-id');
                window.location.href = `messages.php?action=view&id=${messageId}`;
            });
        });
    });
    </script>
    
    <style>
    .messages-filters {
        background: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .filters-left {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
        min-width: 200px;
    }
    
    .filter-group label {
        font-weight: 600;
        margin-bottom: 5px;
        color: #555;
        font-size: 14px;
    }
    
    .filters-right {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .search-box {
        display: flex;
        align-items: center;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 5px 15px;
        border: 1px solid #dee2e6;
    }
    
    .search-box input {
        border: none;
        background: transparent;
        padding: 8px;
        width: 250px;
        outline: none;
    }
    
    .search-box button {
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
        padding: 5px;
    }
    
    .quick-actions {
        margin-bottom: 20px;
    }
    
    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .messages-container {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .messages-table-container {
        overflow-x: auto;
    }
    
    .messages-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .messages-table th {
        background: #f8f9fa;
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #555;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }
    
    .messages-table td {
        padding: 15px;
        border-bottom: 1px solid #dee2e6;
        vertical-align: top;
    }
    
    .message-row {
        transition: background-color 0.2s;
    }
    
    .message-row:hover {
        background-color: #f8f9fa;
    }
    
    .message-row.status-unread {
        background-color: #fff9e6;
    }
    
    .message-row.status-unread:hover {
        background-color: #fff4d1;
    }
    
    .message-row.urgent {
        background-color: #ffeaea;
    }
    
    .message-row.urgent:hover {
        background-color: #ffd6d6;
    }
    
    .status-indicator {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
    
    .status-indicator.status-unread {
        background-color: #ffc107;
        color: white;
    }
    
    .status-indicator.status-read {
        background-color: #28a745;
        color: white;
    }
    
    .status-indicator.status-replied {
        background-color: #17a2b8;
        color: white;
    }
    
    .status-indicator.status-archived {
        background-color: #6c757d;
        color: white;
    }
    
    .urgent-indicator {
        color: #dc3545;
        font-size: 12px;
        margin-top: 2px;
        text-align: center;
    }
    
    .sender-info {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .sender-name {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .urgent-badge {
        background-color: #dc3545;
        color: white;
        padding: 2px 6px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .sender-email, .sender-phone {
        font-size: 12px;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .sender-email i, .sender-phone i {
        font-size: 11px;
    }
    
    .message-subject {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .message-preview {
        font-size: 13px;
        color: #6c757d;
        line-height: 1.4;
    }
    
    .category-badge {
        display: inline-block;
        padding: 4px 10px;
        background-color: #e9ecef;
        color: #495057;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .message-date {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    
    .message-time {
        font-size: 12px;
        color: #6c757d;
    }
    
    .message-actions {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }
    
    .message-actions .btn {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    
    .empty-state i {
        margin-bottom: 20px;
        color: #dee2e6;
    }
    
    .empty-state h4 {
        margin-bottom: 10px;
        color: #495057;
    }
    
    .empty-state p {
        margin-bottom: 20px;
    }
    
    .chart-container {
        margin-top: 30px;
    }
    
    .chart-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .chart-card h3 {
        margin: 0 0 20px 0;
        color: #333;
        font-size: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .chart-wrapper {
        height: 300px;
        position: relative;
    }
    </style>
    <?php
    include 'includes/footer.php';
}

function handleMessageView() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    
    // Fetch message
    $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->execute([$id]);
    $message = $stmt->fetch();
    
    if (!$message) {
        $_SESSION['error'] = 'Message not found.';
        header('Location: messages.php');
        exit();
    }
    
    // Mark as read
    if ($message['status'] === 'unread') {
        $updateStmt = $pdo->prepare("UPDATE contact_messages SET status = 'read', read_at = NOW() WHERE id = ?");
        $updateStmt->execute([$id]);
        $message['status'] = 'read';
        $message['read_at'] = date('Y-m-d H:i:s');
    }
    
    // Get replies if any
    $replies_stmt = $pdo->prepare("SELECT * FROM message_replies WHERE message_id = ? ORDER BY created_at DESC");
    $replies_stmt->execute([$id]);
    $replies = $replies_stmt->fetchAll();
    
    include 'includes/header.php';
    ?>
    <div class="admin-content">
        <div class="message-detail-view">
            <div class="detail-header">
                <a href="messages.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Messages
                </a>
                <div class="header-actions">
                    <a href="messages.php?action=reply&id=<?php echo $message['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-reply"></i> Reply
                    </a>
                    <?php if ($message['status'] === 'unread'): ?>
                        <a href="messages.php?action=mark-read&id=<?php echo $message['id']; ?>" class="btn btn-success">
                            <i class="fas fa-check"></i> Mark as Read
                        </a>
                    <?php else: ?>
                        <a href="messages.php?action=mark-unread&id=<?php echo $message['id']; ?>" class="btn btn-warning">
                            <i class="fas fa-envelope"></i> Mark as Unread
                        </a>
                    <?php endif; ?>
                    <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: <?php echo urlencode($message['subject']); ?>" 
                       class="btn btn-info">
                        <i class="fas fa-external-link-alt"></i> Compose Email
                    </a>
                    <?php if (hasPermission('admin')): ?>
                        <a href="messages.php?action=delete&id=<?php echo $message['id']; ?>" 
                           class="btn btn-danger"
                           onclick="return confirm('Are you sure you want to delete this message?')">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="message-content">
                <!-- Message Header -->
                <div class="message-header">
                    <div class="message-status-badge status-<?php echo $message['status']; ?>">
                        <?php echo strtoupper($message['status']); ?>
                    </div>
                    <?php if ($message['is_urgent']): ?>
                        <div class="urgent-badge-large">
                            <i class="fas fa-exclamation-circle"></i> URGENT
                        </div>
                    <?php endif; ?>
                    
                    <h1><?php echo htmlspecialchars($message['subject']); ?></h1>
                    
                    <div class="sender-details">
                        <div class="sender-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="sender-info">
                            <h3><?php echo htmlspecialchars($message['name']); ?></h3>
                            <div class="contact-info">
                                <span class="email">
                                    <i class="fas fa-envelope"></i>
                                    <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>">
                                        <?php echo htmlspecialchars($message['email']); ?>
                                    </a>
                                </span>
                                <?php if ($message['phone']): ?>
                                    <span class="phone">
                                        <i class="fas fa-phone"></i>
                                        <?php echo htmlspecialchars($message['phone']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($message['category']): ?>
                        <div class="message-category">
                            <strong>Category:</strong>
                            <span class="category-tag"><?php echo htmlspecialchars($message['category']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Message Body -->
                <div class="message-body">
                    <div class="message-text">
                        <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                    </div>
                    
                    <?php if ($message['attachments']): ?>
                        <div class="message-attachments">
                            <h4><i class="fas fa-paperclip"></i> Attachments</h4>
                            <div class="attachments-list">
                                <?php 
                                $attachments = json_decode($message['attachments'], true);
                                if (is_array($attachments)): 
                                    foreach ($attachments as $attachment): 
                                ?>
                                    <a href="../<?php echo htmlspecialchars($attachment['path']); ?>" 
                                       target="_blank" class="attachment-item">
                                        <i class="fas fa-file"></i>
                                        <span><?php echo htmlspecialchars($attachment['name']); ?></span>
                                        <small><?php echo formatBytes($attachment['size']); ?></small>
                                    </a>
                                <?php endforeach; endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Message Meta -->
                <div class="message-meta">
                    <div class="meta-item">
                        <i class="far fa-clock"></i>
                        <div>
                            <strong>Received</strong>
                            <p><?php echo date('F j, Y g:i A', strtotime($message['created_at'])); ?></p>
                        </div>
                    </div>
                    
                    <?php if ($message['read_at']): ?>
                    <div class="meta-item">
                        <i class="far fa-eye"></i>
                        <div>
                            <strong>Read</strong>
                            <p><?php echo date('F j, Y g:i A', strtotime($message['read_at'])); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($message['ip_address']): ?>
                    <div class="meta-item">
                        <i class="fas fa-globe"></i>
                        <div>
                            <strong>IP Address</strong>
                            <p><?php echo htmlspecialchars($message['ip_address']); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="meta-item">
                        <i class="fas fa-user-agent"></i>
                        <div>
                            <strong>User Agent</strong>
                            <p class="user-agent"><?php echo htmlspecialchars(substr($message['user_agent'], 0, 100)); ?>...</p>
                        </div>
                    </div>
                </div>
                
                <!-- Replies Section -->
                <div class="replies-section">
                    <h3>
                        <i class="fas fa-reply"></i> 
                        Replies (<?php echo count($replies); ?>)
                    </h3>
                    
                    <?php if (empty($replies)): ?>
                        <div class="no-replies">
                            <p>No replies sent yet.</p>
                            <a href="messages.php?action=reply&id=<?php echo $message['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-reply"></i> Send First Reply
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="replies-list">
                            <?php foreach ($replies as $reply): ?>
                                <div class="reply-item">
                                    <div class="reply-header">
                                        <div class="reply-sender">
                                            <div class="reply-avatar">
                                                <i class="fas fa-user-tie"></i>
                                            </div>
                                            <div class="reply-info">
                                                <strong><?php echo htmlspecialchars($reply['sent_by_name']); ?></strong>
                                                <span class="reply-date">
                                                    <?php echo date('F j, Y g:i A', strtotime($reply['created_at'])); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="reply-actions">
                                            <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-envelope"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="reply-body">
                                        <?php echo nl2br(htmlspecialchars($reply['message'])); ?>
                                    </div>
                                    <?php if ($reply['attachments']): ?>
                                        <div class="reply-attachments">
                                            <?php 
                                            $reply_attachments = json_decode($reply['attachments'], true);
                                            if (is_array($reply_attachments)): 
                                                foreach ($reply_attachments as $attachment): 
                                            ?>
                                                <a href="../<?php echo htmlspecialchars($attachment['path']); ?>" 
                                                   target="_blank" class="attachment-small">
                                                    <i class="fas fa-paperclip"></i>
                                                    <?php echo htmlspecialchars($attachment['name']); ?>
                                                </a>
                                            <?php endforeach; endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Quick Reply Form -->
                <div class="quick-reply-form">
                    <h4><i class="fas fa-paper-plane"></i> Quick Reply</h4>
                    <form method="POST" action="messages.php?action=reply&id=<?php echo $message['id']; ?>">
                        <div class="form-group">
                            <textarea name="reply_message" class="form-control" rows="4" 
                                      placeholder="Type your reply here..." required></textarea>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="markAsReplied" name="mark_as_replied" checked>
                                <label class="form-check-label" for="markAsReplied">
                                    Mark message as replied
                                </label>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Send Reply
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    .message-detail-view {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        flex-wrap: wrap;
    }
    
    .message-content {
        max-width: 800px;
        margin: 0 auto;
    }
    
    .message-header {
        margin-bottom: 30px;
    }
    
    .message-status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 15px;
    }
    
    .message-status-badge.status-unread {
        background-color: #ffc107;
        color: white;
    }
    
    .message-status-badge.status-read {
        background-color: #28a745;
        color: white;
    }
    
    .message-status-badge.status-replied {
        background-color: #17a2b8;
        color: white;
    }
    
    .message-status-badge.status-archived {
        background-color: #6c757d;
        color: white;
    }
    
    .urgent-badge-large {
        display: inline-block;
        padding: 6px 12px;
        background-color: #dc3545;
        color: white;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        margin-left: 10px;
    }
    
    .message-header h1 {
        margin: 15px 0;
        color: #333;
        font-size: 24px;
    }
    
    .sender-details {
        display: flex;
        align-items: center;
        gap: 15px;
        margin: 20px 0;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .sender-avatar {
        width: 60px;
        height: 60px;
        background: #0e0c5e;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }
    
    .sender-info h3 {
        margin: 0 0 10px 0;
        color: #333;
    }
    
    .contact-info {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .contact-info span {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #6c757d;
        font-size: 14px;
    }
    
    .contact-info a {
        color: #0e0c5e;
        text-decoration: none;
    }
    
    .contact-info a:hover {
        text-decoration: underline;
    }
    
    .message-category {
        margin-top: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .category-tag {
        padding: 4px 12px;
        background: #e9ecef;
        color: #495057;
        border-radius: 20px;
        font-size: 14px;
    }
    
    .message-body {
        margin-bottom: 30px;
        padding: 30px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .message-text {
        line-height: 1.6;
        color: #333;
        white-space: pre-wrap;
    }
    
    .message-attachments {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #dee2e6;
    }
    
    .message-attachments h4 {
        margin-bottom: 15px;
        color: #555;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .attachments-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .attachment-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px 15px;
        background: white;
        border-radius: 8px;
        text-decoration: none;
        color: #333;
        border: 1px solid #dee2e6;
        transition: all 0.2s;
    }
    
    .attachment-item:hover {
        background: #f8f9fa;
        border-color: #0e0c5e;
    }
    
    .attachment-item i {
        color: #0e0c5e;
        font-size: 18px;
    }
    
    .attachment-item span {
        flex: 1;
    }
    
    .attachment-item small {
        color: #6c757d;
        font-size: 12px;
    }
    
    .message-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
        padding: 20px;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }
    
    .meta-item {
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }
    
    .meta-item i {
        font-size: 20px;
        color: #0e0c5e;
        margin-top: 2px;
    }
    
    .meta-item strong {
        display: block;
        margin-bottom: 5px;
        color: #555;
    }
    
    .meta-item p {
        margin: 0;
        color: #333;
        font-size: 14px;
    }
    
    .user-agent {
        word-break: break-all;
    }
    
    .replies-section {
        margin-bottom: 30px;
    }
    
    .replies-section h3 {
        margin-bottom: 20px;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .no-replies {
        text-align: center;
        padding: 40px;
        background: #f8f9fa;
        border-radius: 8px;
        color: #6c757d;
    }
    
    .replies-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .reply-item {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
    }
    
    .reply-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #dee2e6;
    }
    
    .reply-sender {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .reply-avatar {
        width: 40px;
        height: 40px;
        background: #17a2b8;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
    }
    
    .reply-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    
    .reply-info strong {
        color: #333;
    }
    
    .reply-date {
        font-size: 12px;
        color: #6c757d;
    }
    
    .reply-body {
        line-height: 1.6;
        color: #333;
        white-space: pre-wrap;
        margin-bottom: 15px;
    }
    
    .reply-attachments {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 15px;
    }
    
    .attachment-small {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        background: #f8f9fa;
        border-radius: 4px;
        text-decoration: none;
        color: #555;
        font-size: 13px;
        border: 1px solid #dee2e6;
    }
    
    .attachment-small:hover {
        background: #e9ecef;
    }
    
    .quick-reply-form {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
    }
    
    .quick-reply-form h4 {
        margin-bottom: 15px;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .form-actions {
        margin-top: 15px;
        display: flex;
        justify-content: flex-end;
    }
    </style>
    <?php
    include 'includes/footer.php';
}

function handleMessageReply() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    
    // Fetch message
    $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->execute([$id]);
    $message = $stmt->fetch();
    
    if (!$message) {
        $_SESSION['error'] = 'Message not found.';
        header('Location: messages.php');
        exit();
    }
    
    $error = '';
    $success = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $reply_message = trim($_POST['reply_message']);
        $mark_as_replied = isset($_POST['mark_as_replied']);
        
        if (empty($reply_message)) {
            $error = 'Please enter a reply message.';
        } else {
            try {
                // Start transaction
                $pdo->beginTransaction();
                
                // Save reply
                $stmt = $pdo->prepare("
                    INSERT INTO message_replies 
                    (message_id, message, sent_by_id, sent_by_name, attachments)
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                $attachments = [];
                if (isset($_FILES['attachments']) && is_array($_FILES['attachments']['name'])) {
                    foreach ($_FILES['attachments']['name'] as $key => $name) {
                        if ($_FILES['attachments']['error'][$key] === UPLOAD_ERR_OK) {
                            $uploadResult = uploadAttachment($_FILES['attachments'], $key);
                            if ($uploadResult['success']) {
                                $attachments[] = [
                                    'name' => $name,
                                    'path' => $uploadResult['path'],
                                    'size' => $uploadResult['size']
                                ];
                            }
                        }
                    }
                }
                
                $stmt->execute([
                    $id,
                    $reply_message,
                    $_SESSION['admin_id'],
                    $_SESSION['admin_name'],
                    json_encode($attachments)
                ]);
                
                // Update message status if requested
                if ($mark_as_replied) {
                    $updateStmt = $pdo->prepare("UPDATE contact_messages SET status = 'replied' WHERE id = ?");
                    $updateStmt->execute([$id]);
                }
                
                // Send email notification
                if (isset($_POST['send_email']) && $_POST['send_email'] === '1') {
                    sendReplyEmail($message, $reply_message);
                }
                
                // Log activity
                logActivity('reply', 'contact_messages', $id, null, [
                    'sent_by' => $_SESSION['admin_name']
                ]);
                
                $pdo->commit();
                
                $success = 'Reply sent successfully.';
                $_SESSION['message'] = $success;
                header('Location: messages.php?action=view&id=' . $id);
                exit();
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Error sending reply: ' . $e->getMessage();
            }
        }
    }
    
    include 'includes/header.php';
    ?>
    <div class="admin-content">
        <div class="form-card">
            <h2>
                <i class="fas fa-reply"></i> Reply to Message
            </h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="message-preview-card">
                <h4>Original Message</h4>
                <div class="preview-header">
                    <strong>From:</strong> <?php echo htmlspecialchars($message['name']); ?> 
                    &lt;<?php echo htmlspecialchars($message['email']); ?>&gt;<br>
                    <strong>Subject:</strong> <?php echo htmlspecialchars($message['subject']); ?><br>
                    <strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($message['created_at'])); ?>
                </div>
                <div class="preview-body">
                    <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                </div>
            </div>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">Your Reply *</label>
                    <textarea name="reply_message" class="form-control" rows="8" 
                              placeholder="Type your reply here..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Attachments (optional)</label>
                    <div class="file-upload-container">
                        <input type="file" name="attachments[]" id="attachments" multiple
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif,.zip,.rar">
                        <label for="attachments" class="upload-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Click to upload files or drag and drop</span>
                            <small>Max file size: 10MB each</small>
                        </label>
                        <div id="fileList" class="file-list"></div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="markAsReplied" name="mark_as_replied" checked>
                            <label class="form-check-label" for="markAsReplied">
                                Mark message as replied
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="sendEmail" name="send_email" value="1" checked>
                            <label class="form-check-label" for="sendEmail">
                                Send email notification
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Reply
                    </button>
                    <a href="messages.php?action=view&id=<?php echo $id; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('attachments');
        const fileList = document.getElementById('fileList');
        
        fileInput.addEventListener('change', function() {
            fileList.innerHTML = '';
            
            if (this.files.length > 0) {
                const list = document.createElement('ul');
                list.className = 'file-list-items';
                
                Array.from(this.files).forEach((file, index) => {
                    const li = document.createElement('li');
                    li.innerHTML = `
                        <span class="file-name">${file.name}</span>
                        <span class="file-size">(${formatBytes(file.size)})</span>
                        <button type="button" class="btn-remove-file" data-index="${index}">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    list.appendChild(li);
                });
                
                fileList.appendChild(list);
                
                // Add remove functionality
                document.querySelectorAll('.btn-remove-file').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const index = parseInt(this.getAttribute('data-index'));
                        removeFile(index);
                    });
                });
            }
        });
        
        // Drag and drop
        const uploadLabel = document.querySelector('.upload-label');
        
        ['dragenter', 'dragover'].forEach(event => {
            uploadLabel.addEventListener(event, function(e) {
                e.preventDefault();
                this.classList.add('drag-over');
            });
        });
        
        ['dragleave', 'drop'].forEach(event => {
            uploadLabel.addEventListener(event, function(e) {
                e.preventDefault();
                this.classList.remove('drag-over');
                
                if (event === 'drop') {
                    const files = e.dataTransfer.files;
                    fileInput.files = files;
                    fileInput.dispatchEvent(new Event('change'));
                }
            });
        });
        
        function removeFile(index) {
            const dt = new DataTransfer();
            const files = Array.from(fileInput.files);
            
            files.splice(index, 1);
            
            files.forEach(file => {
                dt.items.add(file);
            });
            
            fileInput.files = dt.files;
            fileInput.dispatchEvent(new Event('change'));
        }
        
        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    });
    </script>
    
    <style>
    .message-preview-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 30px;
        border-left: 4px solid #0e0c5e;
    }
    
    .message-preview-card h4 {
        margin-top: 0;
        color: #333;
        margin-bottom: 15px;
    }
    
    .preview-header {
        background: white;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 15px;
        font-size: 14px;
        color: #555;
    }
    
    .preview-body {
        background: white;
        padding: 15px;
        border-radius: 4px;
        line-height: 1.6;
        color: #333;
        white-space: pre-wrap;
    }
    
    .file-upload-container {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        transition: border-color 0.2s;
    }
    
    .file-upload-container:hover {
        border-color: #0e0c5e;
    }
    
    .file-upload-container input[type="file"] {
        display: none;
    }
    
    .upload-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        color: #6c757d;
    }
    
    .upload-label i {
        font-size: 48px;
        color: #0e0c5e;
    }
    
    .upload-label span {
        font-size: 16px;
        font-weight: 500;
    }
    
    .upload-label small {
        font-size: 12px;
        color: #adb5bd;
    }
    
    .upload-label.drag-over {
        border-color: #0e0c5e;
        background-color: rgba(14, 12, 94, 0.05);
    }
    
    .file-list {
        margin-top: 20px;
    }
    
    .file-list-items {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .file-list-items li {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 15px;
        background: white;
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }
    
    .file-name {
        flex: 1;
        color: #333;
    }
    
    .file-size {
        color: #6c757d;
        font-size: 14px;
        margin-right: 10px;
    }
    
    .btn-remove-file {
        background: none;
        border: none;
        color: #dc3545;
        cursor: pointer;
        padding: 5px;
    }
    
    .btn-remove-file:hover {
        color: #bd2130;
    }
    </style>
    <?php
    include 'includes/footer.php';
}

function handleMarkRead() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    
    $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'read', read_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);
    
    logActivity('update', 'contact_messages', $id, ['status' => 'unread'], ['status' => 'read']);
    
    $_SESSION['message'] = 'Message marked as read.';
    header('Location: messages.php');
    exit();
}

function handleMarkUnread() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    
    $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'unread', read_at = NULL WHERE id = ?");
    $stmt->execute([$id]);
    
    logActivity('update', 'contact_messages', $id, ['status' => 'read'], ['status' => 'unread']);
    
    $_SESSION['message'] = 'Message marked as unread.';
    header('Location: messages.php');
    exit();
}

function handleMessageDelete() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    
    try {
        // Get message details before deletion
        $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
        $stmt->execute([$id]);
        $message = $stmt->fetch();
        
        if (!$message) {
            $_SESSION['error'] = 'Message not found.';
            header('Location: messages.php');
            exit();
        }
        
        // Delete related replies
        $pdo->prepare("DELETE FROM message_replies WHERE message_id = ?")->execute([$id]);
        
        // Delete message
        $pdo->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$id]);
        
        logActivity('delete', 'contact_messages', $id, [
            'name' => $message['name'],
            'email' => $message['email'],
            'subject' => $message['subject']
        ], null);
        
        $_SESSION['message'] = 'Message deleted successfully.';
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error deleting message: ' . $e->getMessage();
    }
    
    header('Location: messages.php');
    exit();
}

function handleMessagesExport() {
    global $pdo;
    
    $format = $_GET['export'] ?? 'csv';
    $status = $_GET['status'] ?? 'all';
    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;
    
    // Build query
    $sql = "SELECT * FROM contact_messages WHERE 1=1";
    $params = [];
    
    if ($status !== 'all') {
        $sql .= " AND status = ?";
        $params[] = $status;
    }
    
    if ($start_date) {
        $sql .= " AND DATE(created_at) >= ?";
        $params[] = $start_date;
    }
    
    if ($end_date) {
        $sql .= " AND DATE(created_at) <= ?";
        $params[] = $end_date;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $messages = $stmt->fetchAll();
    
    if ($format === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=messages_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($output, [
            'ID', 'Name', 'Email', 'Phone', 'Subject', 'Message', 'Category', 
            'Status', 'Is Urgent', 'IP Address', 'Created At', 'Read At'
        ]);
        
        // Add data rows
        foreach ($messages as $message) {
            fputcsv($output, [
                $message['id'],
                $message['name'],
                $message['email'],
                $message['phone'] ?? '',
                $message['subject'],
                strip_tags($message['message']),
                $message['category'] ?? '',
                $message['status'],
                $message['is_urgent'] ? 'Yes' : 'No',
                $message['ip_address'] ?? '',
                $message['created_at'],
                $message['read_at'] ?? ''
            ]);
        }
        
        fclose($output);
        exit();
    }
    
    // Default to JSON if format not specified
    header('Content-Type: application/json');
    echo json_encode($messages, JSON_PRETTY_PRINT);
    exit();
}

function uploadAttachment($file, $index) {
    $uploadDir = '../uploads/message_attachments/';
    
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
    
    // Check file size (10MB max)
    $maxSize = 10 * 1024 * 1024;
    if ($file['size'][$index] > $maxSize) {
        return ['success' => false, 'error' => 'File too large. Maximum size: 10MB'];
    }
    
    // Get file info
    $name = $file['name'][$index];
    $tmp_name = $file['tmp_name'][$index];
    $size = $file['size'][$index];
    $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    
    // Generate unique filename
    $safeName = preg_replace('/[^a-zA-Z0-9\-_]/', '_', pathinfo($name, PATHINFO_FILENAME));
    $fileName = $safeName . '_' . uniqid() . '.' . $extension;
    $filePath = $monthDir . $fileName;
    
    // Move uploaded file
    if (move_uploaded_file($tmp_name, $filePath)) {
        return [
            'success' => true,
            'path' => 'uploads/message_attachments/' . $year . '/' . $month . '/' . $fileName,
            'name' => $name,
            'size' => $size
        ];
    }
    
    return ['success' => false, 'error' => 'Failed to upload file'];
}

function sendReplyEmail($message, $reply) {
    // Email configuration
    $to = $message['email'];
    $subject = "Re: " . $message['subject'];
    $admin_name = $_SESSION['admin_name'] ?? 'Administrator';
    $organization = "Sokatoto Muda Initiative Trust";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . $organization . " <noreply@sokatoto.org>\r\n";
    $headers .= "Reply-To: " . $organization . " <info@sokatoto.org>\r\n";
    
    // Email template
    $body = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>' . $subject . '</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; }
            .header { background: #0e0c5e; color: white; padding: 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
            .message { background: white; padding: 20px; border-left: 4px solid #0e0c5e; margin: 20px 0; }
            .footer { background: #f1f1f1; padding: 20px; text-align: center; font-size: 12px; color: #666; }
            .signature { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>' . $organization . '</h1>
        </div>
        
        <div class="content">
            <h2>Reply to Your Message</h2>
            
            <p>Dear ' . htmlspecialchars($message['name']) . ',</p>
            
            <p>Thank you for contacting us. Here is our response to your message:</p>
            
            <div class="message">
                ' . nl2br(htmlspecialchars($reply)) . '
            </div>
            
            <div class="signature">
                <p>Best regards,<br>
                <strong>' . htmlspecialchars($admin_name) . '</strong><br>
                ' . $organization . '</p>
            </div>
            
            <p><small>This is an automated response. Please do not reply to this email.</small></p>
        </div>
        
        <div class="footer">
            <p>&copy; ' . date('Y') . ' ' . $organization . '. All rights reserved.</p>
        </div>
    </body>
    </html>
    ';
    
    // Send email
    return mail($to, $subject, $body, $headers);
}

// Helper functions
function truncateText($text, $length = 50) {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . '...';
}
?>