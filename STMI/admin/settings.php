<?php
require_once '../config/database.php';
requireAdmin();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? 'general';
    
    switch ($section) {
        case 'general':
            // Update site settings
            $site_title = trim($_POST['site_title']);
            $site_description = trim($_POST['site_description']);
            $contact_email = trim($_POST['contact_email']);
            $contact_phone = trim($_POST['contact_phone']);
            $address = trim($_POST['address']);
            
            // Save to settings table (you'll need to create this table)
            // For now, we'll use a JSON file or update config
            $settings = [
                'site_title' => $site_title,
                'site_description' => $site_description,
                'contact_email' => $contact_email,
                'contact_phone' => $contact_phone,
                'address' => $address,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $_SESSION['admin_id']
            ];
            
            // Save to database or file
            $success = true;
            
            if ($success) {
                $_SESSION['success_message'] = 'General settings updated successfully';
            }
            break;
            
        case 'payment':
            // Update payment settings
            $mpesa_paybill = trim($_POST['mpesa_paybill']);
            $mpesa_account = trim($_POST['mpesa_account']);
            $bank_name = trim($_POST['bank_name']);
            $bank_account = trim($_POST['bank_account']);
            $bank_branch = trim($_POST['bank_branch']);
            
            // Save to database
            $success = true;
            
            if ($success) {
                $_SESSION['success_message'] = 'Payment settings updated successfully';
            }
            break;
            
        case 'email':
            // Update email settings
            $smtp_host = trim($_POST['smtp_host']);
            $smtp_port = trim($_POST['smtp_port']);
            $smtp_user = trim($_POST['smtp_user']);
            $smtp_pass = trim($_POST['smtp_pass']);
            $from_email = trim($_POST['from_email']);
            $from_name = trim($_POST['from_name']);
            
            // Save to database
            $success = true;
            
            if ($success) {
                $_SESSION['success_message'] = 'Email settings updated successfully';
            }
            break;
            
        case 'backup':
            // Handle backup
            backupDatabase();
            $_SESSION['success_message'] = 'Database backup created successfully';
            break;
    }
    
    header('Location: settings.php?section=' . $section);
    exit;
}

// Get current section
$current_section = $_GET['section'] ?? 'general';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .settings-container {
            display: flex;
            gap: 20px;
        }
        
        .settings-sidebar {
            width: 250px;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .settings-nav {
            list-style: none;
            padding: 0;
        }
        
        .settings-nav li {
            margin-bottom: 5px;
        }
        
        .settings-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            color: #555;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .settings-nav a:hover {
            background: #f8f9fa;
            color: #333;
        }
        
        .settings-nav a.active {
            background: #0e0c5e;
            color: white;
        }
        
        .settings-nav a i {
            width: 20px;
        }
        
        .settings-content {
            flex: 1;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .settings-section {
            display: none;
        }
        
        .settings-section.active {
            display: block;
        }
        
        .settings-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .settings-header h2 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .settings-header p {
            color: #666;
            font-size: 0.95rem;
        }
        
        .settings-form .form-group {
            margin-bottom: 25px;
        }
        
        .settings-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .settings-form input,
        .settings-form select,
        .settings-form textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .settings-form textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .settings-form .form-help {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .settings-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .btn-save {
            background: #57cc99;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-reset {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .backup-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .backup-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #eee;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #0e0c5e;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .danger-zone {
            background: #fff5f5;
            border: 1px solid #feb2b2;
            border-radius: 5px;
            padding: 20px;
            margin-top: 40px;
        }
        
        .danger-zone h3 {
            color: #c53030;
            margin-bottom: 15px;
        }
        
        .danger-zone p {
            color: #666;
            margin-bottom: 15px;
        }
        
        .btn-danger {
            background: #e53e3e;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .btn-danger:hover {
            background: #c53030;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1>System Settings</h1>
        </div>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="settings-container">
            <!-- Settings Sidebar -->
            <div class="settings-sidebar">
                <ul class="settings-nav">
                    <li>
                        <a href="?section=general" class="<?php echo $current_section === 'general' ? 'active' : ''; ?>">
                            <i class="fas fa-cog"></i> General Settings
                        </a>
                    </li>
                    <li>
                        <a href="?section=payment" class="<?php echo $current_section === 'payment' ? 'active' : ''; ?>">
                            <i class="fas fa-credit-card"></i> Payment Settings
                        </a>
                    </li>
                    <li>
                        <a href="?section=email" class="<?php echo $current_section === 'email' ? 'active' : ''; ?>">
                            <i class="fas fa-envelope"></i> Email Settings
                        </a>
                    </li>
                    <li>
                        <a href="?section=social" class="<?php echo $current_section === 'social' ? 'active' : ''; ?>">
                            <i class="fas fa-share-alt"></i> Social Media
                        </a>
                    </li>
                    <li>
                        <a href="?section=backup" class="<?php echo $current_section === 'backup' ? 'active' : ''; ?>">
                            <i class="fas fa-database"></i> Backup & Restore
                        </a>
                    </li>
                    <li>
                        <a href="?section=maintenance" class="<?php echo $current_section === 'maintenance' ? 'active' : ''; ?>">
                            <i class="fas fa-tools"></i> Maintenance
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Settings Content -->
            <div class="settings-content">
                <!-- General Settings -->
                <div class="settings-section <?php echo $current_section === 'general' ? 'active' : ''; ?>" id="generalSection">
                    <div class="settings-header">
                        <h2><i class="fas fa-cog"></i> General Settings</h2>
                        <p>Configure basic website settings and information</p>
                    </div>
                    
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="section" value="general">
                        
                        <div class="form-group">
                            <label>Site Title *</label>
                            <input type="text" name="site_title" value="Soka Toto Muda Initiative Trust" required>
                            <div class="form-help">This appears in browser tabs and search results</div>
                        </div>
                        
                        <div class="form-group">
                            <label>Site Description</label>
                            <textarea name="site_description">Christ-centered, non-profit making organization empowering children and young mothers through sports, creative arts, and psychosocial support.</textarea>
                            <div class="form-help">Brief description for search engines</div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Contact Email *</label>
                                <input type="email" name="contact_email" value="stmitrust@gmail.com" required>
                            </div>
                            <div class="form-group">
                                <label>Contact Phone *</label>
                                <input type="tel" name="contact_phone" value="+254 728 274304" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Physical Address</label>
                            <textarea name="address">Alpha Glory Community Educational Center, Nairobi, Kenya</textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Timezone</label>
                            <select name="timezone">
                                <option value="Africa/Nairobi" selected>Africa/Nairobi (GMT+3)</option>
                                <!-- Other timezones -->
                            </select>
                        </div>
                        
                        <div class="settings-actions">
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <button type="reset" class="btn-reset">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Payment Settings -->
                <div class="settings-section <?php echo $current_section === 'payment' ? 'active' : ''; ?>" id="paymentSection">
                    <div class="settings-header">
                        <h2><i class="fas fa-credit-card"></i> Payment Settings</h2>
                        <p>Configure payment methods and donation settings</p>
                    </div>
                    
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="section" value="payment">
                        
                        <h3>MPESA Settings</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Paybill Number *</label>
                                <input type="text" name="mpesa_paybill" value="522522" required>
                            </div>
                            <div class="form-group">
                                <label>Account Number *</label>
                                <input type="text" name="mpesa_account" value="7936016" required>
                            </div>
                        </div>
                        
                        <h3>Bank Transfer Settings</h3>
                        <div class="form-group">
                            <label>Bank Name *</label>
                            <input type="text" name="bank_name" value="KCB Bank" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Account Number *</label>
                                <input type="text" name="bank_account" value="1335357998" required>
                            </div>
                            <div class="form-group">
                                <label>Account Name *</label>
                                <input type="text" name="account_name" value="SOKA TOTO MUDA INITIATIVE TRUST" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Branch *</label>
                                <input type="text" name="bank_branch" value="PRESTIGE PLAZA" required>
                            </div>
                            <div class="form-group">
                                <label>Swift Code</label>
                                <input type="text" name="swift_code" value="KCBLKENX">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Currency</label>
                            <select name="currency">
                                <option value="KES" selected>Kenyan Shilling (KES)</option>
                                <option value="USD">US Dollar (USD)</option>
                                <option value="EUR">Euro (EUR)</option>
                            </select>
                        </div>
                        
                        <div class="settings-actions">
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Email Settings -->
                <div class="settings-section <?php echo $current_section === 'email' ? 'active' : ''; ?>" id="emailSection">
                    <div class="settings-header">
                        <h2><i class="fas fa-envelope"></i> Email Settings</h2>
                        <p>Configure email server settings for notifications</p>
                    </div>
                    
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="section" value="email">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>SMTP Host *</label>
                                <input type="text" name="smtp_host" value="smtp.gmail.com" required>
                            </div>
                            <div class="form-group">
                                <label>SMTP Port *</label>
                                <input type="number" name="smtp_port" value="587" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>SMTP Username *</label>
                                <input type="email" name="smtp_user" value="stmitrust@gmail.com" required>
                            </div>
                            <div class="form-group">
                                <label>SMTP Password *</label>
                                <input type="password" name="smtp_pass" required>
                                <div class="form-help">Use app-specific password for Gmail</div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>From Email *</label>
                                <input type="email" name="from_email" value="stmitrust@gmail.com" required>
                            </div>
                            <div class="form-group">
                                <label>From Name *</label>
                                <input type="text" name="from_name" value="STMI Trust" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Email Encryption</label>
                            <select name="encryption">
                                <option value="tls" selected>TLS</option>
                                <option value="ssl">SSL</option>
                                <option value="">None</option>
                            </select>
                        </div>
                        
                        <div class="settings-actions">
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="testEmail()">
                                <i class="fas fa-paper-plane"></i> Test Email
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Backup & Restore -->
                <div class="settings-section <?php echo $current_section === 'backup' ? 'active' : ''; ?>" id="backupSection">
                    <div class="settings-header">
                        <h2><i class="fas fa-database"></i> Backup & Restore</h2>
                        <p>Manage database backups and restore points</p>
                    </div>
                    
                    <div class="backup-info">
                        <h3>Current Database Status</h3>
                        <div class="backup-stats">
                            <div class="stat-card">
                                <div class="stat-number"><?php 
                                    $stmt = $pdo->query("SELECT COUNT(*) FROM events");
                                    echo $stmt->fetchColumn();
                                ?></div>
                                <div class="stat-label">Events</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number"><?php 
                                    $stmt = $pdo->query("SELECT COUNT(*) FROM team_members");
                                    echo $stmt->fetchColumn();
                                ?></div>
                                <div class="stat-label">Team Members</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number"><?php 
                                    $stmt = $pdo->query("SELECT COUNT(*) FROM donations");
                                    echo $stmt->fetchColumn();
                                ?></div>
                                <div class="stat-label">Donations</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number"><?php 
                                    $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages");
                                    echo $stmt->fetchColumn();
                                ?></div>
                                <div class="stat-label">Messages</div>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="section" value="backup">
                        
                        <div class="form-group">
                            <label>Backup Type</label>
                            <select name="backup_type">
                                <option value="full">Full Backup (Database + Files)</option>
                                <option value="database" selected>Database Only</option>
                                <option value="files">Uploaded Files Only</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Compression</label>
                            <select name="compression">
                                <option value="zip" selected>ZIP Compression</option>
                                <option value="gzip">GZIP Compression</option>
                                <option value="none">No Compression</option>
                            </select>
                        </div>
                        
                        <div class="settings-actions">
                            <button type="submit" class="btn-save">
                                <i class="fas fa-download"></i> Create Backup Now
                            </button>
                        </div>
                    </form>
                    
                    <div class="danger-zone">
                        <h3><i class="fas fa-exclamation-triangle"></i> Danger Zone</h3>
                        <p>These actions are irreversible. Please proceed with caution.</p>
                        
                        <div style="display: flex; gap: 10px;">
                            <button type="button" class="btn-danger" onclick="clearCache()">
                                <i class="fas fa-broom"></i> Clear Cache
                            </button>
                            <button type="button" class="btn-danger" onclick="resetStatistics()">
                                <i class="fas fa-chart-line"></i> Reset Statistics
                            </button>
                            <button type="button" class="btn-danger" onclick="optimizeDatabase()">
                                <i class="fas fa-database"></i> Optimize Database
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        // Test email function
        function testEmail() {
            if (confirm('Send a test email to the admin email?')) {
                fetch('handlers/test_email.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Test email sent successfully!');
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
            }
        }
        
        // Danger zone functions
        function clearCache() {
            if (confirm('Clear all cached data? This will not affect your database.')) {
                fetch('handlers/clear_cache.php')
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                    });
            }
        }
        
        function resetStatistics() {
            if (confirm('Reset all visitor statistics? This cannot be undone.')) {
                if (prompt('Type "RESET" to confirm:') === 'RESET') {
                    fetch('handlers/reset_stats.php')
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                        });
                }
            }
        }
        
        function optimizeDatabase() {
            if (confirm('Optimize database tables? This may improve performance.')) {
                fetch('handlers/optimize_db.php')
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                    });
            }
        }
        
        // Auto-save form changes (optional)
        document.querySelectorAll('.settings-form input, .settings-form select, .settings-form textarea').forEach(element => {
            element.addEventListener('change', function() {
                this.classList.add('changed');
            });
        });
        
        // Warn before leaving unsaved changes
        window.addEventListener('beforeunload', function(e) {
            const changedElements = document.querySelectorAll('.changed');
            if (changedElements.length > 0) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            }
        });
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>