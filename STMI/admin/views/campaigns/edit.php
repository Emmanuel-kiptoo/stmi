<?php if (!$campaign): ?>
    <div class="alert alert-danger">
        Campaign not found.
    </div>
    <a href="donation_campaigns.php" class="btn btn-secondary">Back to Campaigns</a>
<?php else: ?>
<div class="form-container">
    <div class="form-header">
        <h2><i class="fas fa-edit"></i> Edit Campaign: <?php echo htmlspecialchars($campaign['title']); ?></h2>
        <a href="donation_campaigns.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Campaigns
        </a>
    </div>
    
    <form id="campaignForm" action="handlers/save_campaign.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="campaign_id" value="<?php echo $campaign['id']; ?>">
        
        <div class="form-group">
            <label>Campaign Title *</label>
            <input type="text" name="title" required 
                   value="<?php echo htmlspecialchars($campaign['title']); ?>"
                   placeholder="Enter campaign title" 
                   maxlength="200">
        </div>
        
        <div class="form-group">
            <label>Campaign Description *</label>
            <textarea id="description" name="description" rows="5" required 
                      placeholder="Describe the purpose and impact of this campaign..."><?php echo htmlspecialchars($campaign['description']); ?></textarea>
            <div class="form-help">Explain what the funds will be used for and the expected impact</div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Target Amount (KES) *</label>
                <input type="number" name="target_amount" required 
                       min="1" step="0.01"
                       value="<?php echo $campaign['target_amount']; ?>"
                       placeholder="Enter target amount">
            </div>
            
            <div class="form-group">
                <label>Current Amount (KES)</label>
                <input type="number" name="current_amount" 
                       min="0" step="0.01"
                       value="<?php echo $campaign['current_amount']; ?>"
                       placeholder="Already raised amount">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Start Date *</label>
                <input type="date" name="start_date" required 
                       value="<?php echo $campaign['start_date']; ?>">
            </div>
            
            <div class="form-group">
                <label>End Date *</label>
                <input type="date" name="end_date" required 
                       value="<?php echo $campaign['end_date']; ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label>Status *</label>
            <select name="status" required>
                <option value="active" <?php echo $campaign['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="completed" <?php echo $campaign['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="cancelled" <?php echo $campaign['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Featured Image</label>
            <?php if ($campaign['featured_image']): ?>
                <div class="current-image">
                    <img src="../<?php echo htmlspecialchars($campaign['featured_image']); ?>" 
                         alt="Current Image" style="max-width: 200px; margin-bottom: 10px;">
                    <br>
                    <label>
                        <input type="checkbox" name="remove_image" value="1">
                        Remove current image
                    </label>
                </div>
            <?php endif; ?>
            
            <div class="file-upload">
                <input type="file" name="featured_image" id="featuredImage" 
                       accept="image/*" onchange="previewImage(this)">
                <label for="featuredImage" class="upload-btn">
                    <i class="fas fa-cloud-upload-alt"></i> 
                    <?php echo $campaign['featured_image'] ? 'Change Image' : 'Choose Image'; ?>
                </label>
                <div class="file-name" id="fileName">No file chosen</div>
            </div>
            <div class="image-preview" id="imagePreview" style="display: none;">
                <img id="previewImg" src="#" alt="Preview">
            </div>
            <div class="form-help">Recommended size: 800x450px. Max size: 2MB</div>
        </div>
        
        <div class="campaign-info-card">
            <h3>Campaign Statistics</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>Total Donations:</label>
                    <span>
                        <?php 
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM donations WHERE campaign_id = ?");
                        $stmt->execute([$campaign['id']]);
                        echo $stmt->fetchColumn();
                        ?>
                    </span>
                </div>
                <div class="info-item">
                    <label>Total Donors:</label>
                    <span>
                        <?php 
                        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT donor_email) FROM donations WHERE campaign_id = ? AND donor_email != ''");
                        $stmt->execute([$campaign['id']]);
                        echo $stmt->fetchColumn();
                        ?>
                    </span>
                </div>
                <div class="info-item">
                    <label>Progress:</label>
                    <span>
                        <?php echo $campaign['target_amount'] > 0 ? round(($campaign['current_amount'] / $campaign['target_amount'] * 100), 1) : 0; ?>%
                    </span>
                </div>
                <div class="info-item">
                    <label>Days Left:</label>
                    <span>
                        <?php 
                        $days_left = ceil((strtotime($campaign['end_date']) - time()) / (60 * 60 * 24));
                        echo $days_left > 0 ? $days_left : 'Ended';
                        ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Campaign
            </button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='donation_campaigns.php'">
                Cancel
            </button>
            <button type="button" class="btn btn-info" onclick="viewDonations()">
                <i class="fas fa-donate"></i> View Donations
            </button>
            <button type="button" class="btn btn-warning" onclick="closeCampaign()">
                <i class="fas fa-ban"></i> Close Campaign
            </button>
        </div>
    </form>
</div>

<style>
    .campaign-info-card {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin: 30px 0;
        border-left: 4px solid #0e0c5e;
    }
    
    .campaign-info-card h3 {
        margin: 0 0 15px 0;
        color: #333;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
        font-size: 1rem;
        font-weight: 600;
    }
</style>

<script>
    // Image preview
    function previewImage(input) {
        if (input.files && input.files[0]) {
            document.getElementById('fileName').textContent = input.files[0].name;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('previewImg');
                preview.src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // View donations
    function viewDonations() {
        window.location.href = 'donations.php?campaign=<?php echo $campaign['id']; ?>';
    }
    
    // Close campaign
    function closeCampaign() {
        if (confirm('Close this campaign? No new donations will be accepted.')) {
            fetch('handlers/update_campaign_status.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    campaign_id: <?php echo $campaign['id']; ?>,
                    status: 'completed'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Campaign closed successfully.');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }
    
    // Form validation
    document.getElementById('campaignForm').addEventListener('submit', function(e) {
        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;
        const targetAmount = document.querySelector('input[name="target_amount"]').value;
        
        // Check if end date is after start date
        if (startDate && endDate && startDate > endDate) {
            e.preventDefault();
            alert('End date must be after start date.');
            return false;
        }
        
        // Check if target amount is positive
        if (targetAmount <= 0) {
            e.preventDefault();
            alert('Target amount must be greater than 0.');
            return false;
        }
        
        // Check if current amount is less than or equal to target
        const currentAmount = document.querySelector('input[name="current_amount"]').value;
        if (parseFloat(currentAmount) > parseFloat(targetAmount)) {
            e.preventDefault();
            if (!confirm('Current amount exceeds target amount. Continue anyway?')) {
                return false;
            }
        }
        
        return true;
    });
</script>
<?php endif; ?>