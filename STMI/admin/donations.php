<?php
require_once '../config/database.php';
requireAdmin();

// Get filter
$filter = $_GET['filter'] ?? 'all';
$campaign_id = $_GET['campaign'] ?? '';

// Build query
$sql = "SELECT d.*, c.title as campaign_title FROM donations d 
        LEFT JOIN donation_campaigns c ON d.campaign_id = c.id 
        WHERE 1=1";
$params = [];

if ($filter === 'completed') {
    $sql .= " AND d.status = 'completed'";
} elseif ($filter === 'pending') {
    $sql .= " AND d.status = 'pending'";
} elseif ($filter === 'failed') {
    $sql .= " AND d.status = 'failed'";
}

if (!empty($campaign_id) && is_numeric($campaign_id)) {
    $sql .= " AND d.campaign_id = ?";
    $params[] = $campaign_id;
}

$sql .= " ORDER BY d.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$donations = $stmt->fetchAll();

// Get campaigns for filter
$campaigns = $pdo->query("SELECT id, title FROM donation_campaigns WHERE status = 'active'")->fetchAll();

// Get statistics
$stats = [];
$stmt = $pdo->query("SELECT COUNT(*) as count FROM donations");
$stats['total'] = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT SUM(amount) as total FROM donations WHERE status = 'completed'");
$stats['total_amount'] = $stmt->fetch()['total'] ?: 0;

$stmt = $pdo->query("SELECT COUNT(*) as count FROM donations WHERE status = 'pending'");
$stats['pending'] = $stmt->fetch()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donations Management - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1>Donations Management</h1>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="window.location.href='donation_campaigns.php'">
                    <i class="fas fa-plus"></i> New Campaign
                </button>
            </div>
        </div>
        
        <!-- Stats Overview -->
        <div class="stats-overview">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <div class="stat-details">
                    <h3>KES <?php echo number_format($stats['total_amount'], 2); ?></h3>
                    <p>Total Donations</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['total']; ?></h3>
                    <p>Total Donors</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $stats['pending']; ?></h3>
                    <p>Pending Donations</p>
                </div>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" class="filter-form">
                <select name="filter" onchange="this.form.submit()">
                    <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Donations</option>
                    <option value="completed" <?php echo $filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="pending" <?php echo $filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="failed" <?php echo $filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                </select>
                
                <select name="campaign" onchange="this.form.submit()">
                    <option value="">All Campaigns</option>
                    <?php foreach ($campaigns as $campaign): ?>
                        <option value="<?php echo $campaign['id']; ?>" 
                            <?php echo $campaign_id == $campaign['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($campaign['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="btn-filter">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </form>
        </div>
        
        <!-- Donations Table -->
        <div class="donations-table">
            <table>
                <thead>
                    <tr>
                        <th>Donor</th>
                        <th>Amount</th>
                        <th>Campaign</th>
                        <th>Payment Method</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($donations as $donation): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($donation['donor_name'] ?? 'Anonymous'); ?></strong>
                            <?php if ($donation['donor_email']): ?>
                                <br><small><?php echo htmlspecialchars($donation['donor_email']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo $donation['currency'] . ' ' . number_format($donation['amount'], 2); ?></strong>
                        </td>
                        <td>
                            <?php echo $donation['campaign_title'] ? htmlspecialchars($donation['campaign_title']) : 'General Donation'; ?>
                        </td>
                        <td><?php echo ucfirst($donation['payment_method'] ?? 'Unknown'); ?></td>
                        <td><?php echo date('M d, Y', strtotime($donation['created_at'])); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $donation['status']; ?>">
                                <?php echo ucfirst($donation['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-view" data-id="<?php echo $donation['id']; ?>">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="btn-update" data-id="<?php echo $donation['id']; ?>">
                                    <i class="fas fa-edit"></i> Update
                                </button>
                                <button class="btn-receipt" data-id="<?php echo $donation['id']; ?>">
                                    <i class="fas fa-file-invoice"></i> Receipt
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Donation Chart -->
        <div class="chart-section">
            <canvas id="donationsChart"></canvas>
        </div>
    </main>
    
    <!-- Donation Details Modal -->
    <div class="modal" id="donationModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Donation Details</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body" id="donationContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
    
    <script>
        // View donation details
        document.querySelectorAll('.btn-view').forEach(btn => {
            btn.addEventListener('click', function() {
                const donationId = this.getAttribute('data-id');
                
                fetch(`handlers/get_donation.php?id=${donationId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('donationContent').innerHTML = `
                                <div class="donation-details">
                                    <div class="detail-row">
                                        <label>Donor Name:</label>
                                        <span>${data.donation.donor_name || 'Anonymous'}</span>
                                    </div>
                                    <div class="detail-row">
                                        <label>Email:</label>
                                        <span>${data.donation.donor_email || 'Not provided'}</span>
                                    </div>
                                    <div class="detail-row">
                                        <label>Phone:</label>
                                        <span>${data.donation.donor_phone || 'Not provided'}</span>
                                    </div>
                                    <div class="detail-row">
                                        <label>Amount:</label>
                                        <strong>${data.donation.currency} ${parseFloat(data.donation.amount).toFixed(2)}</strong>
                                    </div>
                                    <div class="detail-row">
                                        <label>Payment Method:</label>
                                        <span>${data.donation.payment_method}</span>
                                    </div>
                                    <div class="detail-row">
                                        <label>Transaction ID:</label>
                                        <span>${data.donation.transaction_id || 'Not available'}</span>
                                    </div>
                                    <div class="detail-row">
                                        <label>Status:</label>
                                        <span class="status-badge status-${data.donation.status}">
                                            ${data.donation.status}
                                        </span>
                                    </div>
                                    <div class="detail-row">
                                        <label>Date:</label>
                                        <span>${data.donation.formatted_date}</span>
                                    </div>
                                    <div class="detail-row">
                                        <label>Purpose:</label>
                                        <span>${data.donation.purpose || 'General Donation'}</span>
                                    </div>
                                    ${data.donation.notes ? `
                                    <div class="detail-row">
                                        <label>Notes:</label>
                                        <p>${data.donation.notes}</p>
                                    </div>` : ''}
                                </div>
                            `;
                            
                            document.getElementById('donationModal').style.display = 'block';
                        }
                    });
            });
        });
        
        // Update donation status
        document.querySelectorAll('.btn-update').forEach(btn => {
            btn.addEventListener('click', function() {
                const donationId = this.getAttribute('data-id');
                const newStatus = prompt('Update status (pending/completed/failed/refunded):');
                
                if (newStatus && ['pending', 'completed', 'failed', 'refunded'].includes(newStatus)) {
                    fetch(`handlers/update_donation.php`, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            id: donationId,
                            status: newStatus
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        }
                    });
                }
            });
        });
        
        // Generate receipt
        document.querySelectorAll('.btn-receipt').forEach(btn => {
            btn.addEventListener('click', function() {
                const donationId = this.getAttribute('data-id');
                window.open(`handlers/generate_receipt.php?id=${donationId}`, '_blank');
            });
        });
        
        // Close modal
        document.querySelector('.modal-close').addEventListener('click', function() {
            document.getElementById('donationModal').style.display = 'none';
        });
        
        // Donations chart
        document.addEventListener('DOMContentLoaded', function() {
            fetch('handlers/get_donation_stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const ctx = document.getElementById('donationsChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: data.months,
                                datasets: [{
                                    label: 'Donations (KES)',
                                    data: data.amounts,
                                    borderColor: '#3498db',
                                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    title: {
                                        display: true,
                                        text: 'Donations Over Time'
                                    }
                                }
                            }
                        });
                    }
                });
        });
    </script>
</body>
</html>