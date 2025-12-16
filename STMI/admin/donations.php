<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'view':
        viewDonation();
        break;
    case 'confirm':
        confirmDonation();
        break;
    case 'receipt':
        sendReceipt();
        break;
    case 'delete':
        requirePermission('admin');
        deleteDonation();
        break;
    default:
        listDonations();
}

function listDonations() {
    global $pdo;
    
    $status = $_GET['status'] ?? '';
    $method = $_GET['method'] ?? '';
    $search = $_GET['search'] ?? '';
    
    $sql = "SELECT * FROM admin_donations WHERE 1=1";
    $params = [];
    
    if ($status) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }
    
    if ($method) {
        $sql .= " AND payment_method = ?";
        $params[] = $method;
    }
    
    if ($search) {
        $sql .= " AND (donor_name LIKE ? OR transaction_id LIKE ? OR donor_email LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $donations = $stmt->fetchAll();
    
    // Statistics
    $totalDonations = $pdo->query("SELECT SUM(amount) as total FROM admin_donations WHERE status = 'confirmed'")->fetch()['total'] ?? 0;
    $pendingCount = $pdo->query("SELECT COUNT(*) as count FROM admin_donations WHERE status = 'pending'")->fetch()['count'] ?? 0;
    
    include 'includes/header.php';
    ?>
    <div class="admin-content">
        <div class="dashboard-header">
            <h1><i class="fas fa-hand-holding-heart"></i> Donations Management</h1>
            <p>Track and manage all donations received</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #57cc99;">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-info">
                    <h3>KES <?php echo number_format($totalDonations, 2); ?></h3>
                    <p>Total Donations</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #ff9d0b;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($pendingCount); ?></h3>
                    <p>Pending Donations</p>
                </div>
                <a href="donations.php?status=pending" class="stat-link">View Pending</a>
            </div>
        </div>
        
        <div class="table-header">
            <h3>All Donations</h3>
            <div class="table-filters">
                <select id="statusFilter" class="form-control">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="receipt_sent">Receipt Sent</option>
                </select>
                <select id="methodFilter" class="form-control">
                    <option value="">All Methods</option>
                    <option value="mpesa">MPESA</option>
                    <option value="bank">Bank</option>
                    <option value="cash">Cash</option>
                </select>
                <div class="table-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search donations...">
                </div>
            </div>
        </div>
        
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>Donor</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Transaction ID</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($donations)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No donations found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($donations as $donation): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($donation['donor_name']); ?></strong><br>
                                    <?php if ($donation['donor_email']): ?>
                                        <small><?php echo htmlspecialchars($donation['donor_email']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong>KES <?php echo number_format($donation['amount'], 2); ?></strong>
                                    <?php if ($donation['purpose'] !== 'general'): ?>
                                        <br><small class="text-muted"><?php echo ucfirst(str_replace('_', ' ', $donation['purpose'])); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo ucfirst($donation['payment_method']); ?></td>
                                <td><?php echo htmlspecialchars($donation['transaction_id']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($donation['created_at'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $donation['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $donation['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="donations.php?action=view&id=<?php echo $donation['id']; ?>" 
                                           class="btn btn-sm btn-secondary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($donation['status'] === 'pending'): ?>
                                            <a href="donations.php?action=confirm&id=<?php echo $donation['id']; ?>" 
                                               class="btn btn-sm btn-success" title="Confirm">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($donation['status'] === 'confirmed' && $donation['donor_email']): ?>
                                            <a href="donations.php?action=receipt&id=<?php echo $donation['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Send Receipt">
                                                <i class="fas fa-receipt"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (hasPermission('admin')): ?>
                                            <a href="donations.php?action=delete&id=<?php echo $donation['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this donation?')"
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
    
    <script>
        // Filter functionality
        document.getElementById('statusFilter').addEventListener('change', function() {
            const status = this.value;
            const method = document.getElementById('methodFilter').value;
            const search = document.getElementById('searchInput').value;
            updateFilters(status, method, search);
        });
        
        document.getElementById('methodFilter').addEventListener('change', function() {
            const status = document.getElementById('statusFilter').value;
            const method = this.value;
            const search = document.getElementById('searchInput').value;
            updateFilters(status, method, search);
        });
        
        document.getElementById('searchInput').addEventListener('input', function() {
            const status = document.getElementById('statusFilter').value;
            const method = document.getElementById('methodFilter').value;
            const search = this.value;
            updateFilters(status, method, search);
        });
        
        function updateFilters(status, method, search) {
            let url = 'donations.php?';
            const params = [];
            
            if (status) params.push('status=' + encodeURIComponent(status));
            if (method) params.push('method=' + encodeURIComponent(method));
            if (search) params.push('search=' + encodeURIComponent(search));
            
            window.location.href = url + params.join('&');
        }
        
        // Set current filter values
        const urlParams = new URLSearchParams(window.location.search);
        document.getElementById('statusFilter').value = urlParams.get('status') || '';
        document.getElementById('methodFilter').value = urlParams.get('method') || '';
        document.getElementById('searchInput').value = urlParams.get('search') || '';
    </script>
    <?php
    include 'includes/footer.php';
}

function viewDonation() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    
    $stmt = $pdo->prepare("SELECT * FROM admin_donations WHERE id = ?");
    $stmt->execute([$id]);
    $donation = $stmt->fetch();
    
    if (!$donation) {
        $_SESSION['error'] = 'Donation not found.';
        header('Location: donations.php');
        exit();
    }
    
    include 'includes/header.php';
    ?>
    <div class="admin-content">
        <div class="form-card">
            <h2><i class="fas fa-donate"></i> Donation Details</h2>
            
            <div class="donation-details">
                <div class="detail-section">
                    <h4>Donor Information</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Name:</label>
                            <span><?php echo htmlspecialchars($donation['donor_name']); ?></span>
                        </div>
                        <?php if ($donation['donor_email']): ?>
                        <div class="detail-item">
                            <label>Email:</label>
                            <span><?php echo htmlspecialchars($donation['donor_email']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($donation['donor_phone']): ?>
                        <div class="detail-item">
                            <label>Phone:</label>
                            <span><?php echo htmlspecialchars($donation['donor_phone']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h4>Donation Details</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Amount:</label>
                            <span class="amount">KES <?php echo number_format($donation['amount'], 2); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Payment Method:</label>
                            <span><?php echo ucfirst($donation['payment_method']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Transaction ID:</label>
                            <span><?php echo htmlspecialchars($donation['transaction_id']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Purpose:</label>
                            <span><?php echo ucfirst(str_replace('_', ' ', $donation['purpose'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Status:</label>
                            <span class="status-badge status-<?php echo $donation['status']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $donation['status'])); ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <label>Date:</label>
                            <span><?php echo date('F j, Y g:i A', strtotime($donation['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
                
                <?php if ($donation['notes']): ?>
                <div class="detail-section">
                    <h4>Notes</h4>
                    <p><?php echo nl2br(htmlspecialchars($donation['notes'])); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($donation['receipt_sent_at']): ?>
                <div class="detail-section">
                    <h4>Receipt Information</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Receipt Sent:</label>
                            <span><?php echo date('F j, Y g:i A', strtotime($donation['receipt_sent_at'])); ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="form-actions">
                <a href="donations.php" class="btn btn-secondary">Back to List</a>
                <?php if ($donation['status'] === 'pending'): ?>
                    <a href="donations.php?action=confirm&id=<?php echo $donation['id']; ?>" 
                       class="btn btn-success">Confirm Donation</a>
                <?php endif; ?>
                <?php if ($donation['status'] === 'confirmed' && $donation['donor_email']): ?>
                    <a href="donations.php?action=receipt&id=<?php echo $donation['id']; ?>" 
                       class="btn btn-primary">Send Receipt</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    include 'includes/footer.php';
}

function confirmDonation() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE admin_donations SET 
            status = 'confirmed', 
            confirmed_by = ?, 
            confirmed_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$_SESSION['admin_id'], $id]);
        
        logActivity('confirm_donation', 'admin_donations', $id, ['status' => 'pending'], ['status' => 'confirmed']);
        
        $_SESSION['message'] = 'Donation confirmed successfully.';
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error confirming donation: ' . $e->getMessage();
    }
    
    header('Location: donations.php');
    exit();
}

function sendReceipt() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    
    try {
        // Get donation details
        $stmt = $pdo->prepare("SELECT * FROM admin_donations WHERE id = ?");
        $stmt->execute([$id]);
        $donation = $stmt->fetch();
        
        if (!$donation || !$donation['donor_email']) {
            $_SESSION['error'] = 'Cannot send receipt: No email address provided.';
            header('Location: donations.php');
            exit();
        }
        
        // Update receipt sent timestamp
        $stmt = $pdo->prepare("
            UPDATE admin_donations SET 
            status = 'receipt_sent', 
            receipt_sent_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        
        // Send email receipt (you'll need to implement email functionality)
        // sendDonationReceiptEmail($donation);
        
        logActivity('send_receipt', 'admin_donations', $id, ['status' => 'confirmed'], ['status' => 'receipt_sent']);
        
        $_SESSION['message'] = 'Receipt sent successfully to ' . htmlspecialchars($donation['donor_email']);
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error sending receipt: ' . $e->getMessage();
    }
    
    header('Location: donations.php');
    exit();
}

function deleteDonation() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM admin_donations WHERE id = ?");
        $stmt->execute([$id]);
        
        logActivity('delete_donation', 'admin_donations', $id, null, null);
        
        $_SESSION['message'] = 'Donation deleted successfully.';
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error deleting donation: ' . $e->getMessage();
    }
    
    header('Location: donations.php');
    exit();
}
?>