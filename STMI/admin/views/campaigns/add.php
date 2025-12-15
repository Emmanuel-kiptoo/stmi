<div class="form-container">
    <div class="form-header">
        <h2><i class="fas fa-plus-circle"></i> Create New Donation Campaign</h2>
        <a href="donation_campaigns.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Campaigns
        </a>
    </div>
    
    <form id="campaignForm" action="handlers/save_campaign.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        
        <div class="form-group">
            <label>Campaign Title *</label>
            <input type="text" name="title" required 
                   placeholder="Enter campaign title" 
                   maxlength="200">
        </div>
        
        <div class="form-group">
            <label>Campaign Description *</label>
            <textarea id="description" name="description" rows="5" required 
                      placeholder="Describe the purpose and impact of this campaign..."></textarea>
            <div class="form-help">Explain what the funds will be used for and the expected impact</div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Target Amount (KES) *</label>
                <input type="number" name="target_amount" required 
                       min="1" step="0.01"
                       placeholder="Enter target amount">
            </div>
            
            <div class="form-group">
                <label>Current Amount (KES)</label>
                <input type="number" name="current_amount" 
                       min="0" step="0.01" value="0"
                       placeholder="Already raised amount">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Start Date *</label>
                <input type="date" name="start_date" required 
                       value="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="form-group">
                <label>End Date *</label>
                <input type="date" name="end_date" required 
                       min="<?php echo date('Y-m-d'); ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label>Status *</label>
            <select name="status" required>
                <option value="active" selected>Active</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Featured Image</label>
            <div class="file-upload">
                <input type="file" name="featured_image" id="featuredImage" 
                       accept="image/*" onchange="previewImage(this)">
                <label for="featuredImage" class="upload-btn">
                    <i class="fas fa-cloud-upload-alt"></i> Choose Image
                </label>
                <div class="file-name" id="fileName">No file chosen</div>
            </div>
            <div class="image-preview" id="imagePreview" style="display: none;">
                <img id="previewImg" src="#" alt="Preview">
            </div>
            <div class="form-help">Recommended size: 800x450px. Max size: 2MB</div>
        </div>
        
        <div class="form-group">
            <label>Campaign Type</label>
            <select name="campaign_type">
                <option value="general">General Donation</option>
                <option value="sports">SOKA TOTO Sports Program</option>
                <option value="arts">MUDA Creative Arts</option>
                <option value="teen_mothers">Teen Mothers Support</option>
                <option value="education">Digital Literacy Program</option>
                <option value="emergency">Emergency Fund</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Impact Statement</label>
            <textarea name="impact_statement" rows="3" 
                      placeholder="How will this campaign change lives?"></textarea>
        </div>
        
        <div class="form-group">
            <label>Campaign Goal Breakdown (Optional)</label>
            <div id="goalsContainer">
                <div class="goal-item">
                    <input type="text" name="goals[]" placeholder="KES 50,000 - Sports Equipment">
                    <button type="button" class="btn-remove-goal" onclick="removeGoal(this)">×</button>
                </div>
            </div>
            <button type="button" class="btn-add-goal" onclick="addGoal()">
                <i class="fas fa-plus"></i> Add Goal Item
            </button>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Create Campaign
            </button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='donation_campaigns.php'">
                Cancel
            </button>
        </div>
    </form>
</div>

<style>
    .goal-item {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
    }
    
    .goal-item input {
        flex: 1;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    
    .btn-remove-goal {
        background: #e74c3c;
        color: white;
        border: none;
        width: 30px;
        border-radius: 5px;
        cursor: pointer;
    }
    
    .btn-add-goal {
        background: #f8f9fa;
        color: #333;
        border: 1px solid #ddd;
        padding: 8px 15px;
        border-radius: 5px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-top: 10px;
    }
    
    .btn-add-goal:hover {
        background: #e9ecef;
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
    
    // Goal management
    function addGoal() {
        const container = document.getElementById('goalsContainer');
        const goalItem = document.createElement('div');
        goalItem.className = 'goal-item';
        goalItem.innerHTML = `
            <input type="text" name="goals[]" placeholder="KES 50,000 - Sports Equipment">
            <button type="button" class="btn-remove-goal" onclick="removeGoal(this)">×</button>
        `;
        container.appendChild(goalItem);
    }
    
    function removeGoal(button) {
        button.parentElement.remove();
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
        
        return true;
    });
</script>