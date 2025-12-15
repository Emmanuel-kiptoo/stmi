<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Campaigns - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .campaigns-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .campaigns-header {
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
        
        .campaigns-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .campaign-card {
            border: 1px solid #eee;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .campaign-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .campaign-image {
            height: 180px;
            overflow: hidden;
            position: relative;
        }
        
        .campaign-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .campaign-status {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-active {
            background: #57cc99;
            color: white;
        }
        
        .status-completed {
            background: #3498db;
            color: white;
        }
        
        .status-cancelled {
            background: #e74c3c;
            color: white;
        }
        
        .campaign-content {
            padding: 20px;
        }
        
        .campaign-title {
            font-weight: 600;
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 10px;
        }
        
        .campaign-description {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        
        .campaign-progress {
            margin-bottom: 20px;
        }
        
        .progress-bar {
            height: 8px;
            background: #f0f0f0;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 5px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #0e0c5e, #3498db);
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        
        .progress-text {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: #666;
        }
        
        .campaign-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .stat-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .stat-number {
            font-weight: 600;
            font-size: 1.1rem;
            color: #0e0c5e;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: #666;
        }
        
        .campaign-dates {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .campaign-actions {
            display: flex;
            gap: 5px;
        }
        
        .btn-action {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        
        .btn-edit { background: #3498db; color: white; }
        .btn-delete { background: #e74c3c; color: white; }
        .btn-view { background: #57cc99; color: white; }
        .btn-donations { background: #9b59b6; color: white; }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
            grid-column: 1 / -1;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        .urgent-badge {
            background: #e74c3c;
            color: white;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1>Donation Campaigns</h1>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="window.location.href='donation_campaigns.php?action=add'">
                    <i class="fas fa-plus"></i> New Campaign
                </button>
            </div>
        </div>
        
        <!-- Filter Controls -->
        <div class="campaigns-header">
            <div class="filter-controls">
                <button class="filter-btn <?php echo empty($status) ? 'active' : ''; ?>" 
                        onclick="window.location.href='donation_campaigns.php'">
                    All Campaigns
                </button>
                <button class="filter-btn <?php echo $status === 'active' ? 'active' : ''; ?>" 
                        onclick="window.location.href='donation_campaigns.php?status=active'">
                    Active
                </button>
                <button class="filter-btn <?php echo $status === 'completed' ? 'active' : ''; ?>" 
                        onclick="window.location.href='donation_campaigns.php?status=completed'">
                    Completed
                </button>
                <button class="filter-btn <?php echo $status === 'cancelled' ? 'active' : ''; ?>" 
                        onclick="window.location.href='donation_campaigns.php?status=cancelled'">
                    Cancelled
                </button>
            </div>
        </div>
        
        <!-- Campaigns Grid -->
        <div class="campaigns-container">
            <?php if (empty($campaigns)): ?>
                <div class="empty-state">
                    <i class="fas fa-hand-holding-heart"></i>
                    <h3>No Campaigns Found</h3>
                    <p>Create your first donation campaign to get started.</p>
                    <button class="btn btn-primary" onclick="window.location.href='donation_campaigns.php?action=add'">
                        <i class="fas fa-plus"></i> Create First Campaign
                    </button>
                </div>
            <?php else: ?>
                <div class="campaigns-grid">
                    <?php foreach ($campaigns as $campaign): 
                        $progress = $campaign['target_amount'] > 0 ? ($campaign['current_amount'] / $campaign['target_amount'] * 100) : 0;
                        $isUrgent = $campaign['end_date'] && strtotime($campaign['end_date']) < strtotime('+7 days');
                    ?>
                        <div class="campaign-card">
                            <div class="campaign-image">
                                <?php if ($campaign['featured_image']): ?>
                                    <img src="../<?php echo htmlspecialchars($campaign['featured_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($campaign['title']); ?>">
                                <?php else: ?>
                                    <div style="width:100%;height:100%;background:linear-gradient(135deg,#0e0c5e,#3498db);display:flex;align-items:center;justify-content:center;color:white;">
                                        <i class="fas fa-hand-holding-heart" style="font-size:3rem;"></i>
                                    </div>
                                <?php endif; ?>
                                <span class="campaign-status status-<?php echo $campaign['status']; ?>">
                                    <?php echo ucfirst($campaign['status']); ?>
                                </span>
                            </div>
                            
                            <div class="campaign-content">
                                <h3 class="campaign-title">
                                    <?php echo htmlspecialchars($campaign['title']); ?>
                                    <?php if ($isUrgent && $campaign['status'] === 'active'): ?>
                                        <span class="urgent-badge">Urgent</span>
                                    <?php endif; ?>
                                </h3>
                                
                                <?php if (!empty($campaign['description'])): ?>
                                    <p class="campaign-description">
                                        <?php echo substr(htmlspecialchars($campaign['description']), 0, 120); ?>...
                                    </p>
                                <?php endif; ?>
                                
                                <div class="campaign-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo min($progress, 100); ?>%;"></div>
                                    </div>
                                    <div class="progress-text">
                                        <span>KES <?php echo number_format($campaign['current_amount'], 0); ?></span>
                                        <span><?php echo round($progress, 1); ?>%</span>
                                        <span>KES <?php echo number_format($campaign['target_amount'], 0); ?></span>
                                    </div>
                                </div>
                                
                                <div class="campaign-stats">
                                    <div class="stat-item">
                                        <div class="stat-number">
                                            <?php 
                                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM donations WHERE campaign_id = ?");
                                            $stmt->execute([$campaign['id']]);
                                            echo $stmt->fetchColumn();
                                            ?>
                                        </div>
                                        <div class="stat-label">Donations</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number">
                                            <?php 
                                            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT donor_email) FROM donations WHERE campaign_id = ? AND donor_email != ''");
                                            $stmt->execute([$campaign['id']]);
                                            echo $stmt->fetchColumn();
                                            ?>
                                        </div>
                                        <div class="stat-label">Donors</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-number">
                                            <?php 
                                            $days_left = $campaign['end_date'] ? ceil((strtotime($campaign['end_date']) - time()) / (60 * 60 * 24)) : '∞';
                                            echo $days_left > 0 ? $days_left : 0;
                                            ?>
                                        </div>
                                        <div class="stat-label">Days Left</div>
                                    </div>
                                </div>
                                
                                <div class="campaign-dates">
                                    <span>
                                        <i class="far fa-calendar-plus"></i>
                                        <?php echo date('M d, Y', strtotime($campaign['start_date'])); ?>
                                    </span>
                                    <span>
                                        <i class="far fa-calendar-check"></i>
                                        <?php echo date('M d, Y', strtotime($campaign['end_date'])); ?>
                                    </span>
                                </div>
                                
                                <div class="campaign-actions">
                                    <button class="btn-action btn-view" 
                                            onclick="window.open('../campaign-details.php?id=<?php echo $campaign['id']; ?>', '_blank')">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn-action btn-donations" 
                                            onclick="window.location.href='donations.php?campaign=<?php echo $campaign['id']; ?>'">
                                        <i class="fas fa-donate"></i> Donations
                                    </button>
                                    <button class="btn-action btn-edit" 
                                            onclick="window.location.href='donation_campaigns.php?action=edit&id=<?php echo $campaign['id']; ?>'">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn-action btn-delete" 
                                            onclick="deleteCampaign(<?php echo $campaign['id']; ?>)">
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
        function deleteCampaign(id) {
            if (confirm('Are you sure you want to delete this campaign? All associated donations will be moved to general donations.')) {
                window.location.href = 'donation_campaigns.php?action=delete&id=' + id;
            }
        }
        
        // Quick status update
        function updateCampaignStatus(id, currentStatus) {
            const newStatus = prompt('Update status (active/completed/cancelled):', currentStatus);
            if (newStatus && ['active', 'completed', 'cancelled'].includes(newStatus)) {
                fetch('handlers/update_campaign_status.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        campaign_id: id,
                        status: newStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }
        
        // Export campaign donations
        function exportCampaignDonations(campaignId) {
            window.open('handlers/export_donations.php?campaign=' + campaignId, '_blank');
        }
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>