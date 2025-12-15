<?php
require_once '../config/database.php';
requireAdmin();

// Get filter
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT * FROM contact_messages WHERE 1=1";
$params = [];

if ($filter === 'unread') {
    $sql .= " AND status = 'unread'";
} elseif ($filter === 'read') {
    $sql .= " AND status = 'read'";
} elseif ($filter === 'replied') {
    $sql .= " AND status = 'replied'";
}

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_fill(0, 4, $searchTerm);
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1>Contact Messages</h1>
            <div class="header-actions">
                <form class="search-form" method="GET" action="">
                    <input type="text" name="search" placeholder="Search messages..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>
        
        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                All Messages
            </a>
            <a href="?filter=unread" class="filter-tab <?php echo $filter === 'unread' ? 'active' : ''; ?>">
                Unread <span class="badge"><?php 
                    $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'");
                    echo $stmt->fetchColumn();
                ?></span>
            </a>
            <a href="?filter=read" class="filter-tab <?php echo $filter === 'read' ? 'active' : ''; ?>">
                Read
            </a>
            <a href="?filter=replied" class="filter-tab <?php echo $filter === 'replied' ? 'active' : ''; ?>">
                Replied
            </a>
        </div>
        
        <!-- Messages Table -->
        <div class="messages-table">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $message): ?>
                    <tr class="<?php echo $message['status'] === 'unread' ? 'unread' : ''; ?>">
                        <td>
                            <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($message['email']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($message['subject']); ?>
                            <?php if (strlen($message['message']) > 50): ?>
                                <br><small><?php echo substr(htmlspecialchars($message['message']), 0, 50); ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $message['status']; ?>">
                                <?php echo ucfirst($message['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-view" data-id="<?php echo $message['id']; ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-reply" data-email="<?php echo htmlspecialchars($message['email']); ?>">
                                    <i class="fas fa-reply"></i>
                                </button>
                                <button class="btn-delete" data-id="<?php echo $message['id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <!-- Message View Modal -->
    <div class="modal" id="messageModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Message Details</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body" id="messageContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
    
    <script>
        // View message details
        document.querySelectorAll('.btn-view').forEach(btn => {
            btn.addEventListener('click', function() {
                const messageId = this.getAttribute('data-id');
                
                fetch(`handlers/get_message.php?id=${messageId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('messageContent').innerHTML = `
                                <div class="message-details">
                                    <div class="detail-row">
                                        <label>From:</label>
                                        <strong>${data.message.name}</strong>
                                    </div>
                                    <div class="detail-row">
                                        <label>Email:</label>
                                        <a href="mailto:${data.message.email}">${data.message.email}</a>
                                    </div>
                                    <div class="detail-row">
                                        <label>Subject:</label>
                                        <span>${data.message.subject}</span>
                                    </div>
                                    <div class="detail-row">
                                        <label>Date:</label>
                                        <span>${data.message.formatted_date}</span>
                                    </div>
                                    <div class="detail-row">
                                        <label>IP Address:</label>
                                        <span>${data.message.ip_address}</span>
                                    </div>
                                    <div class="message-body">
                                        <h4>Message:</h4>
                                        <div class="message-text">${data.message.message}</div>
                                    </div>
                                </div>
                            `;
                            
                            // Mark as read
                            fetch(`handlers/update_message.php`, {
                                method: 'POST',
                                headers: {'Content-Type': 'application/json'},
                                body: JSON.stringify({
                                    id: messageId,
                                    status: 'read'
                                })
                            });
                            
                            document.getElementById('messageModal').style.display = 'block';
                        }
                    });
            });
        });
        
        // Reply to message
        document.querySelectorAll('.btn-reply').forEach(btn => {
            btn.addEventListener('click', function() {
                const email = this.getAttribute('data-email');
                window.location.href = `mailto:${email}`;
            });
        });
        
        // Delete message
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this message?')) {
                    const messageId = this.getAttribute('data-id');
                    
                    fetch(`handlers/delete_message.php?id=${messageId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            }
                        });
                }
            });
        });
        
        // Close modal
        document.querySelector('.modal-close').addEventListener('click', function() {
            document.getElementById('messageModal').style.display = 'none';
        });
        
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('messageModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    </script>
</body>
</html>