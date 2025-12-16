<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

// Check if user has admin permissions
requirePermission('admin');

$action = $_GET['action'] ?? 'general';
$tab = $_GET['tab'] ?? 'general';

switch ($action) {
    case 'save':
        handleSaveSettings();
        break;
    case 'test-email':
        handleTestEmail();
        break;
    case 'backup':
        handleBackup();
        break;
    case 'restore':
        handleRestore();
        break;
    case 'clear-cache':
        handleClearCache();
        break;
    case 'logs':
        handleLogs();
        break;
    default:
        showSettingsPage();
}

function showSettingsPage() {
    global $pdo, $tab;
    
    // Get current settings
    $settings = [];
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    // Get site statistics
    $stats = getSiteStatistics();
    
    // Get recent activities
    $activities = getRecentActivities();
    
    include 'includes/header.php';
    ?>
    <div class="admin-content">
        <div class="settings-header">
            <h1><i class="fas fa-cog"></i> System Settings</h1>
            <p>Configure and manage system preferences</p>
        </div>
        
        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #667eea;">
                    <i class="fas fa-database"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['database_size']); ?> MB</h3>
                    <p>Database Size</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #57cc99;">
                    <i class="fas fa-file"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['backup_count']); ?></h3>
                    <p>Backups</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #ff9d0b;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['cache_size']); ?> MB</h3>
                    <p>Cache Size</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #764ba2;">
                    <i class="fas fa-history"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['log_count']); ?></h3>
                    <p>Log Entries</p>
                </div>
            </div>
        </div>
        
        <!-- Settings Tabs -->
        <div class="settings-container">
            <div class="settings-sidebar">
                <div class="settings-menu">
                    <a href="settings.php?tab=general" class="menu-item <?php echo $tab === 'general' ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i> General Settings
                    </a>
                    <a href="settings.php?tab=email" class="menu-item <?php echo $tab === 'email' ? 'active' : ''; ?>">
                        <i class="fas fa-envelope"></i> Email Settings
                    </a>
                    <a href="settings.php?tab=security" class="menu-item <?php echo $tab === 'security' ? 'active' : ''; ?>">
                        <i class="fas fa-shield-alt"></i> Security
                    </a>
                    <a href="settings.php?tab=backup" class="menu-item <?php echo $tab === 'backup' ? 'active' : ''; ?>">
                        <i class="fas fa-database"></i> Backup & Restore
                    </a>
                    <a href="settings.php?tab=maintenance" class="menu-item <?php echo $tab === 'maintenance' ? 'active' : ''; ?>">
                        <i class="fas fa-tools"></i> Maintenance
                    </a>
                    <a href="settings.php?tab=logs" class="menu-item <?php echo $tab === 'logs' ? 'active' : ''; ?>">
                        <i class="fas fa-history"></i> System Logs
                    </a>
                    <a href="settings.php?tab=api" class="menu-item <?php echo $tab === 'api' ? 'active' : ''; ?>">
                        <i class="fas fa-code"></i> API Settings
                    </a>
                    <a href="settings.php?tab=about" class="menu-item <?php echo $tab === 'about' ? 'active' : ''; ?>">
                        <i class="fas fa-info-circle"></i> About System
                    </a>
                </div>
                
                <div class="sidebar-actions">
                    <button type="button" class="btn btn-success btn-block" onclick="saveAllSettings()">
                        <i class="fas fa-save"></i> Save All Changes
                    </button>
                    <a href="settings.php?action=clear-cache" class="btn btn-warning btn-block mt-2"
                       onclick="return confirm('Clear all system cache?')">
                        <i class="fas fa-broom"></i> Clear Cache
                    </a>
                    <a href="settings.php?action=backup" class="btn btn-info btn-block mt-2">
                        <i class="fas fa-download"></i> Create Backup
                    </a>
                </div>
            </div>
            
            <div class="settings-content">
                <!-- General Settings Tab -->
                <div class="settings-tab <?php echo $tab === 'general' ? 'active' : ''; ?>" id="general-tab">
                    <h2><i class="fas fa-cog"></i> General Settings</h2>
                    <p class="tab-description">Configure basic system settings and preferences.</p>
                    
                    <form id="generalForm" class="settings-form">
                        <div class="form-section">
                            <h3>Site Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Site Name *</label>
                                    <input type="text" name="site_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['site_name'] ?? 'Sokatoto Muda Initiative Trust'); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Site URL *</label>
                                    <input type="url" name="site_url" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['site_url'] ?? 'https://sokatoto.org'); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Site Email *</label>
                                    <input type="email" name="site_email" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['site_email'] ?? 'admin@sokatoto.org'); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Site Phone</label>
                                    <input type="text" name="site_phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['site_phone'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Site Description</label>
                                <textarea name="site_description" class="form-control" rows="3"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>System Configuration</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Timezone *</label>
                                    <select name="timezone" class="form-control" required>
                                        <option value="">Select Timezone</option>
                                        <?php
                                        $timezones = timezone_identifiers_list();
                                        foreach ($timezones as $tz):
                                            $selected = ($settings['timezone'] ?? 'Africa/Nairobi') === $tz ? 'selected' : '';
                                        ?>
                                            <option value="<?php echo $tz; ?>" <?php echo $selected; ?>>
                                                <?php echo $tz; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Default Language *</label>
                                    <select name="default_language" class="form-control" required>
                                        <option value="en" <?php echo ($settings['default_language'] ?? 'en') === 'en' ? 'selected' : ''; ?>>English</option>
                                        <option value="sw" <?php echo ($settings['default_language'] ?? 'en') === 'sw' ? 'selected' : ''; ?>>Swahili</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Date Format *</label>
                                    <select name="date_format" class="form-control" required>
                                        <option value="Y-m-d" <?php echo ($settings['date_format'] ?? 'Y-m-d') === 'Y-m-d' ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                                        <option value="d/m/Y" <?php echo ($settings['date_format'] ?? 'Y-m-d') === 'd/m/Y' ? 'selected' : ''; ?>>DD/MM/YYYY</option>
                                        <option value="m/d/Y" <?php echo ($settings['date_format'] ?? 'Y-m-d') === 'm/d/Y' ? 'selected' : ''; ?>>MM/DD/YYYY</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Time Format *</label>
                                    <select name="time_format" class="form-control" required>
                                        <option value="H:i" <?php echo ($settings['time_format'] ?? 'H:i') === 'H:i' ? 'selected' : ''; ?>>24 Hour (14:30)</option>
                                        <option value="h:i A" <?php echo ($settings['time_format'] ?? 'H:i') === 'h:i A' ? 'selected' : ''; ?>>12 Hour (02:30 PM)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>Display Settings</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="maintenance_mode" name="maintenance_mode" value="1"
                                               <?php echo ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="maintenance_mode">
                                            Enable Maintenance Mode
                                        </label>
                                        <small class="form-text text-muted">Only administrators can access the site</small>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="registration_enabled" name="registration_enabled" value="1"
                                               <?php echo ($settings['registration_enabled'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="registration_enabled">
                                            Enable User Registration
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Items Per Page</label>
                                    <input type="number" name="items_per_page" class="form-control" min="5" max="100" 
                                           value="<?php echo htmlspecialchars($settings['items_per_page'] ?? '20'); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Admin Theme</label>
                                    <select name="admin_theme" class="form-control">
                                        <option value="light" <?php echo ($settings['admin_theme'] ?? 'light') === 'light' ? 'selected' : ''; ?>>Light</option>
                                        <option value="dark" <?php echo ($settings['admin_theme'] ?? 'light') === 'dark' ? 'selected' : ''; ?>>Dark</option>
                                        <option value="blue" <?php echo ($settings['admin_theme'] ?? 'light') === 'blue' ? 'selected' : ''; ?>>Blue</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Email Settings Tab -->
                <div class="settings-tab <?php echo $tab === 'email' ? 'active' : ''; ?>" id="email-tab">
                    <h2><i class="fas fa-envelope"></i> Email Settings</h2>
                    <p class="tab-description">Configure email server settings and templates.</p>
                    
                    <form id="emailForm" class="settings-form">
                        <div class="form-section">
                            <h3>SMTP Configuration</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>SMTP Host *</label>
                                    <input type="text" name="smtp_host" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['smtp_host'] ?? 'smtp.gmail.com'); ?>">
                                    <small class="form-text text-muted">e.g., smtp.gmail.com, smtp.yourdomain.com</small>
                                </div>
                                <div class="form-group">
                                    <label>SMTP Port *</label>
                                    <input type="number" name="smtp_port" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['smtp_port'] ?? '587'); ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>SMTP Username *</label>
                                    <input type="text" name="smtp_username" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['smtp_username'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>SMTP Password</label>
                                    <div class="password-input-group">
                                        <input type="password" name="smtp_password" class="form-control" 
                                               placeholder="Leave blank to keep current">
                                        <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>SMTP Encryption *</label>
                                    <select name="smtp_encryption" class="form-control">
                                        <option value="tls" <?php echo ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                        <option value="ssl" <?php echo ($settings['smtp_encryption'] ?? 'tls') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                        <option value="" <?php echo ($settings['smtp_encryption'] ?? 'tls') === '' ? 'selected' : ''; ?>>None</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>From Email *</label>
                                    <input type="email" name="from_email" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['from_email'] ?? 'noreply@sokatoto.org'); ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="smtp_auth" name="smtp_auth" value="1"
                                           <?php echo ($settings['smtp_auth'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="smtp_auth">
                                        SMTP Authentication Required
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>Email Templates</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Email Header Color</label>
                                    <input type="color" name="email_header_color" class="form-control-color" 
                                           value="<?php echo htmlspecialchars($settings['email_header_color'] ?? '#0e0c5e'); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Email Footer Text</label>
                                    <textarea name="email_footer_text" class="form-control" rows="2"><?php echo htmlspecialchars($settings['email_footer_text'] ?? '© ' . date('Y') . ' Sokatoto Muda Initiative Trust. All rights reserved.'); ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>Test Email Configuration</h3>
                            <div class="form-group">
                                <label>Test Email Address</label>
                                <div class="input-group">
                                    <input type="email" id="testEmail" class="form-control" placeholder="Enter email address to test">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-primary" onclick="testEmail()">
                                            <i class="fas fa-paper-plane"></i> Send Test Email
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Security Settings Tab -->
                <div class="settings-tab <?php echo $tab === 'security' ? 'active' : ''; ?>" id="security-tab">
                    <h2><i class="fas fa-shield-alt"></i> Security Settings</h2>
                    <p class="tab-description">Configure security preferences and access controls.</p>
                    
                    <form id="securityForm" class="settings-form">
                        <div class="form-section">
                            <h3>Login Security</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="two_factor_enabled" name="two_factor_enabled" value="1"
                                               <?php echo ($settings['two_factor_enabled'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="two_factor_enabled">
                                            Enable Two-Factor Authentication
                                        </label>
                                        <small class="form-text text-muted">Require OTP for admin login</small>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="login_attempt_limit" name="login_attempt_limit" value="1"
                                               <?php echo ($settings['login_attempt_limit'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="login_attempt_limit">
                                            Limit Login Attempts
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Max Login Attempts</label>
                                    <input type="number" name="max_login_attempts" class="form-control" min="1" max="10" 
                                           value="<?php echo htmlspecialchars($settings['max_login_attempts'] ?? '5'); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Lockout Duration (minutes)</label>
                                    <input type="number" name="lockout_duration" class="form-control" min="1" max="1440" 
                                           value="<?php echo htmlspecialchars($settings['lockout_duration'] ?? '15'); ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="session_timeout_enabled" name="session_timeout_enabled" value="1"
                                           <?php echo ($settings['session_timeout_enabled'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="session_timeout_enabled">
                                        Enable Session Timeout
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Session Timeout (minutes)</label>
                                    <input type="number" name="session_timeout" class="form-control" min="5" max="480" 
                                           value="<?php echo htmlspecialchars($settings['session_timeout'] ?? '30'); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>Password Policy</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Minimum Password Length</label>
                                    <input type="number" name="min_password_length" class="form-control" min="6" max="32" 
                                           value="<?php echo htmlspecialchars($settings['min_password_length'] ?? '8'); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Password Expiry (days)</label>
                                    <input type="number" name="password_expiry_days" class="form-control" min="0" max="365" 
                                           value="<?php echo htmlspecialchars($settings['password_expiry_days'] ?? '90'); ?>">
                                    <small class="form-text text-muted">0 = Never expire</small>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="require_uppercase" name="require_uppercase" value="1"
                                           <?php echo ($settings['require_uppercase'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="require_uppercase">
                                        Require Uppercase Letters
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="require_numbers" name="require_numbers" value="1"
                                           <?php echo ($settings['require_numbers'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="require_numbers">
                                        Require Numbers
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="require_special_chars" name="require_special_chars" value="1"
                                           <?php echo ($settings['require_special_chars'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="require_special_chars">
                                        Require Special Characters
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>Access Control</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Allowed IPs</label>
                                    <textarea name="allowed_ips" class="form-control" rows="3" 
                                              placeholder="Enter one IP per line or leave empty for all"><?php echo htmlspecialchars($settings['allowed_ips'] ?? ''); ?></textarea>
                                    <small class="form-text text-muted">Restrict admin access to specific IP addresses</small>
                                </div>
                                <div class="form-group">
                                    <label>Blocked IPs</label>
                                    <textarea name="blocked_ips" class="form-control" rows="3" 
                                              placeholder="Enter one IP per line"><?php echo htmlspecialchars($settings['blocked_ips'] ?? ''); ?></textarea>
                                    <small class="form-text text-muted">Block specific IP addresses from accessing the site</small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Backup & Restore Tab -->
                <div class="settings-tab <?php echo $tab === 'backup' ? 'active' : ''; ?>" id="backup-tab">
                    <h2><i class="fas fa-database"></i> Backup & Restore</h2>
                    <p class="tab-description">Manage database backups and restoration.</p>
                    
                    <div class="backup-container">
                        <!-- Backup Settings -->
                        <div class="backup-settings">
                            <h3>Backup Configuration</h3>
                            <form id="backupForm" class="settings-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Auto Backup Frequency</label>
                                        <select name="backup_frequency" class="form-control">
                                            <option value="daily" <?php echo ($settings['backup_frequency'] ?? 'weekly') === 'daily' ? 'selected' : ''; ?>>Daily</option>
                                            <option value="weekly" <?php echo ($settings['backup_frequency'] ?? 'weekly') === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                                            <option value="monthly" <?php echo ($settings['backup_frequency'] ?? 'weekly') === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                            <option value="disabled" <?php echo ($settings['backup_frequency'] ?? 'weekly') === 'disabled' ? 'selected' : ''; ?>>Disabled</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Keep Backups (days)</label>
                                        <input type="number" name="keep_backups_days" class="form-control" min="1" max="365" 
                                               value="<?php echo htmlspecialchars($settings['keep_backups_days'] ?? '30'); ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="compress_backups" name="compress_backups" value="1"
                                               <?php echo ($settings['compress_backups'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="compress_backups">
                                            Compress Backups (ZIP)
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Backup Location</label>
                                    <input type="text" name="backup_location" class="form-control" 
                                           value="<?php echo htmlspecialchars($settings['backup_location'] ?? '../backups/'); ?>">
                                    <small class="form-text text-muted">Relative path from root directory</small>
                                </div>
                            </form>
                            
                            <div class="backup-actions mt-4">
                                <button type="button" class="btn btn-primary" onclick="createBackup()">
                                    <i class="fas fa-database"></i> Create Manual Backup
                                </button>
                                <button type="button" class="btn btn-info" onclick="showRestoreModal()">
                                    <i class="fas fa-upload"></i> Restore Backup
                                </button>
                                <button type="button" class="btn btn-success" onclick="saveBackupSettings()">
                                    <i class="fas fa-save"></i> Save Backup Settings
                                </button>
                            </div>
                        </div>
                        
                        <!-- Backup List -->
                        <div class="backup-list">
                            <h3>Available Backups</h3>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Backup File</th>
                                            <th>Size</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $backupDir = '../backups/';
                                        if (file_exists($backupDir) && is_dir($backupDir)) {
                                            $files = scandir($backupDir);
                                            rsort($files);
                                            $count = 0;
                                            
                                            foreach ($files as $file) {
                                                if ($file !== '.' && $file !== '..' && (strpos($file, '.sql') !== false || strpos($file, '.zip') !== false)) {
                                                    $filePath = $backupDir . $file;
                                                    $fileSize = filesize($filePath);
                                                    $fileDate = date('Y-m-d H:i:s', filemtime($filePath));
                                                    $count++;
                                                    
                                                    if ($count > 10) break; // Show only last 10 backups
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($file); ?></td>
                                                        <td><?php echo formatFileSize($fileSize); ?></td>
                                                        <td><?php echo $fileDate; ?></td>
                                                        <td>
                                                            <a href="<?php echo $backupDir . $file; ?>" class="btn btn-sm btn-info" download>
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-warning" onclick="restoreBackup('<?php echo $file; ?>')">
                                                                <i class="fas fa-upload"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteBackup('<?php echo $file; ?>')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                            }
                                            
                                            if ($count === 0): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No backups found</td>
                                                </tr>
                                            <?php endif;
                                        } else { ?>
                                            <tr>
                                                <td colspan="4" class="text-center">Backup directory not found</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Maintenance Tab -->
                <div class="settings-tab <?php echo $tab === 'maintenance' ? 'active' : ''; ?>" id="maintenance-tab">
                    <h2><i class="fas fa-tools"></i> Maintenance</h2>
                    <p class="tab-description">System maintenance and optimization tools.</p>
                    
                    <div class="maintenance-actions">
                        <div class="action-card">
                            <div class="action-icon" style="background: #667eea;">
                                <i class="fas fa-broom"></i>
                            </div>
                            <div class="action-info">
                                <h4>Clear Cache</h4>
                                <p>Remove temporary files and cache data</p>
                            </div>
                            <div class="action-button">
                                <a href="settings.php?action=clear-cache" class="btn btn-primary"
                                   onclick="return confirm('Clear all cache files?')">
                                    Execute
                                </a>
                            </div>
                        </div>
                        
                        <div class="action-card">
                            <div class="action-icon" style="background: #57cc99;">
                                <i class="fas fa-eraser"></i>
                            </div>
                            <div class="action-info">
                                <h4>Clear Logs</h4>
                                <p>Remove old system log entries</p>
                            </div>
                            <div class="action-button">
                                <button type="button" class="btn btn-success" onclick="clearLogs()">
                                    Execute
                                </button>
                            </div>
                        </div>
                        
                        <div class="action-card">
                            <div class="action-icon" style="background: #ff9d0b;">
                                <i class="fas fa-database"></i>
                            </div>
                            <div class="action-info">
                                <h4>Optimize Database</h4>
                                <p>Optimize and repair database tables</p>
                            </div>
                            <div class="action-button">
                                <button type="button" class="btn btn-warning" onclick="optimizeDatabase()">
                                    Execute
                                </button>
                            </div>
                        </div>
                        
                        <div class="action-card">
                            <div class="action-icon" style="background: #764ba2;">
                                <i class="fas fa-sync"></i>
                            </div>
                            <div class="action-info">
                                <h4>Update Check</h4>
                                <p>Check for system updates</p>
                            </div>
                            <div class="action-button">
                                <button type="button" class="btn btn-info" onclick="checkUpdates()">
                                    Check
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="system-info mt-4">
                        <h3>System Information</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>PHP Version:</label>
                                <span><?php echo phpversion(); ?></span>
                            </div>
                            <div class="info-item">
                                <label>MySQL Version:</label>
                                <span><?php echo getMySQLVersion(); ?></span>
                            </div>
                            <div class="info-item">
                                <label>Server Software:</label>
                                <span><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span>
                            </div>
                            <div class="info-item">
                                <label>Max Upload Size:</label>
                                <span><?php echo ini_get('upload_max_filesize'); ?></span>
                            </div>
                            <div class="info-item">
                                <label>Max Execution Time:</label>
                                <span><?php echo ini_get('max_execution_time'); ?> seconds</span>
                            </div>
                            <div class="info-item">
                                <label>Memory Limit:</label>
                                <span><?php echo ini_get('memory_limit'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Logs Tab -->
                <div class="settings-tab <?php echo $tab === 'logs' ? 'active' : ''; ?>" id="logs-tab">
                    <h2><i class="fas fa-history"></i> System Logs</h2>
                    <p class="tab-description">View and manage system activity logs.</p>
                    
                    <div class="logs-filter">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Log Type:</label>
                                <select id="logType" class="form-control">
                                    <option value="all">All Logs</option>
                                    <option value="error">Errors</option>
                                    <option value="login">Logins</option>
                                    <option value="activity">Activities</option>
                                    <option value="system">System</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Date Range:</label>
                                <input type="date" id="logDateFrom" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>to</label>
                                <input type="date" id="logDateTo" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-primary" onclick="filterLogs()">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="logs-container">
                        <div class="logs-list">
                            <?php if (empty($activities)): ?>
                                <div class="no-logs">
                                    <i class="fas fa-info-circle fa-3x"></i>
                                    <h4>No logs found</h4>
                                    <p>No activity logs match your current filters.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($activities as $log): ?>
                                    <div class="log-item log-type-<?php echo strtolower($log['log_type'] ?? 'info'); ?>">
                                        <div class="log-icon">
                                            <?php 
                                            $icon = 'info-circle';
                                            $color = '#6c757d';
                                            
                                            switch ($log['log_type'] ?? 'info') {
                                                case 'error': $icon = 'exclamation-circle'; $color = '#dc3545'; break;
                                                case 'warning': $icon = 'exclamation-triangle'; $color = '#ffc107'; break;
                                                case 'success': $icon = 'check-circle'; $color = '#28a745'; break;
                                                case 'login': $icon = 'sign-in-alt'; $color = '#007bff'; break;
                                            }
                                            ?>
                                            <i class="fas fa-<?php echo $icon; ?>" style="color: <?php echo $color; ?>"></i>
                                        </div>
                                        <div class="log-content">
                                            <div class="log-header">
                                                <strong><?php echo htmlspecialchars($log['description']); ?></strong>
                                                <span class="log-time"><?php echo timeAgo($log['created_at']); ?></span>
                                            </div>
                                            <div class="log-details">
                                                <small>Type: <?php echo htmlspecialchars($log['log_type'] ?? 'info'); ?></small>
                                                <?php if ($log['user_id']): ?>
                                                    <small>• User ID: <?php echo $log['user_id']; ?></small>
                                                <?php endif; ?>
                                                <?php if ($log['ip_address']): ?>
                                                    <small>• IP: <?php echo htmlspecialchars($log['ip_address']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($log['details']): ?>
                                                <div class="log-meta">
                                                    <pre><?php echo htmlspecialchars($log['details']); ?></pre>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="logs-actions mt-3">
                        <button type="button" class="btn btn-danger" onclick="clearLogs()">
                            <i class="fas fa-trash"></i> Clear All Logs
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="exportLogs()">
                            <i class="fas fa-download"></i> Export Logs
                        </button>
                    </div>
                </div>
                
                <!-- About System Tab -->
                <div class="settings-tab <?php echo $tab === 'about' ? 'active' : ''; ?>" id="about-tab">
                    <h2><i class="fas fa-info-circle"></i> About System</h2>
                    <p class="tab-description">System information and version details.</p>
                    
                    <div class="about-container">
                        <div class="about-card">
                            <div class="about-header">
                                <h3>Sokatoto Muda Initiative Trust</h3>
                                <p>Administration System</p>
                            </div>
                            
                            <div class="about-content">
                                <div class="about-logo">
                                    <i class="fas fa-hands-helping fa-4x"></i>
                                </div>
                                
                                <div class="about-details">
                                    <div class="about-item">
                                        <label>System Version:</label>
                                        <span>2.0.1</span>
                                    </div>
                                    <div class="about-item">
                                        <label>Last Updated:</label>
                                        <span><?php echo date('F j, Y', strtotime('2024-01-15')); ?></span>
                                    </div>
                                    <div class="about-item">
                                        <label>License:</label>
                                        <span>Proprietary</span>
                                    </div>
                                    <div class="about-item">
                                        <label>Developer:</label>
                                        <span>Sokatoto Development Team</span>
                                    </div>
                                    <div class="about-item">
                                        <label>Support Email:</label>
                                        <span>support@sokatoto.org</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="about-footer">
                                <p>&copy; <?php echo date('Y'); ?> Sokatoto Muda Initiative Trust. All rights reserved.</p>
                            </div>
                        </div>
                        
                        <div class="modules-list">
                            <h3>System Modules</h3>
                            <div class="modules-grid">
                                <div class="module-item">
                                    <i class="fas fa-users"></i>
                                    <h5>User Management</h5>
                                    <p>Complete user administration system</p>
                                </div>
                                <div class="module-item">
                                    <i class="fas fa-cog"></i>
                                    <h5>Settings</h5>
                                    <p>System configuration and preferences</p>
                                </div>
                                <div class="module-item">
                                    <i class="fas fa-database"></i>
                                    <h5>Database</h5>
                                    <p>MySQL database with backup system</p>
                                </div>
                                <div class="module-item">
                                    <i class="fas fa-shield-alt"></i>
                                    <h5>Security</h5>
                                    <p>Advanced security features</p>
                                </div>
                                <div class="module-item">
                                    <i class="fas fa-envelope"></i>
                                    <h5>Email System</h5>
                                    <p>SMTP email configuration</p>
                                </div>
                                <div class="module-item">
                                    <i class="fas fa-history"></i>
                                    <h5>Activity Logs</h5>
                                    <p>Comprehensive logging system</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Restore Backup Modal -->
    <div class="modal fade" id="restoreModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Restore Backup</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Backup File</label>
                        <input type="file" id="backupFile" class="form-control" accept=".sql,.zip">
                        <small class="form-text text-muted">Select SQL or ZIP file containing backup</small>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> This will overwrite existing data. Make sure you have a current backup.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="uploadBackup()">Restore</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Tab switching
    document.querySelectorAll('.menu-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const tab = this.getAttribute('href').split('=')[1];
            
            // Remove active class from all tabs
            document.querySelectorAll('.settings-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.menu-item').forEach(item => item.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            document.getElementById(tab + '-tab').classList.add('active');
            
            // Update URL
            history.pushState(null, '', `settings.php?tab=${tab}`);
        });
    });
    
    // Password toggle
    function togglePassword(button) {
        const input = button.parentNode.querySelector('input');
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    
    // Save all settings
    function saveAllSettings() {
        const forms = ['generalForm', 'emailForm', 'securityForm'];
        const data = {};
        
        forms.forEach(formId => {
            const form = document.getElementById(formId);
            if (form) {
                const formData = new FormData(form);
                for (let [key, value] of formData.entries()) {
                    data[key] = value;
                }
            }
        });
        
        fetch('settings.php?action=save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('Settings saved successfully!', 'success');
            } else {
                showNotification('Error saving settings: ' + result.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Network error: ' + error, 'error');
        });
    }
    
    // Test email
    function testEmail() {
        const email = document.getElementById('testEmail').value;
        if (!email || !validateEmail(email)) {
            showNotification('Please enter a valid email address', 'warning');
            return;
        }
        
        fetch('settings.php?action=test-email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({email: email})
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('Test email sent successfully!', 'success');
            } else {
                showNotification('Error sending test email: ' + result.message, 'error');
            }
        });
    }
    
    // Create backup
    function createBackup() {
        if (!confirm('Create a new database backup?')) return;
        
        showNotification('Creating backup...', 'info');
        
        fetch('settings.php?action=backup')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('Backup created successfully!', 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showNotification('Error creating backup: ' + result.message, 'error');
            }
        });
    }
    
    // Show restore modal
    function showRestoreModal() {
        $('#restoreModal').modal('show');
    }
    
    // Upload and restore backup
    function uploadBackup() {
        const fileInput = document.getElementById('backupFile');
        const file = fileInput.files[0];
        
        if (!file) {
            showNotification('Please select a backup file', 'warning');
            return;
        }
        
        if (!confirm('WARNING: This will overwrite current database. Continue?')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('backup', file);
        
        fetch('settings.php?action=restore', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('Backup restored successfully!', 'success');
                $('#restoreModal').modal('hide');
                setTimeout(() => location.reload(), 2000);
            } else {
                showNotification('Error restoring backup: ' + result.message, 'error');
            }
        });
    }
    
    // Restore existing backup
    function restoreBackup(filename) {
        if (!confirm(`Restore backup: ${filename}? This will overwrite current data.`)) return;
        
        fetch('settings.php?action=restore&file=' + encodeURIComponent(filename))
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('Backup restored successfully!', 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showNotification('Error restoring backup: ' + result.message, 'error');
            }
        });
    }
    
    // Delete backup
    function deleteBackup(filename) {
        if (!confirm(`Delete backup: ${filename}?`)) return;
        
        fetch('settings.php?action=delete-backup&file=' + encodeURIComponent(filename))
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('Backup deleted successfully!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Error deleting backup: ' + result.message, 'error');
            }
        });
    }
    
    // Save backup settings
    function saveBackupSettings() {
        const form = document.getElementById('backupForm');
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        fetch('settings.php?action=save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('Backup settings saved!', 'success');
            } else {
                showNotification('Error saving settings: ' + result.message, 'error');
            }
        });
    }
    
    // Clear logs
    function clearLogs() {
        if (!confirm('Clear all system logs?')) return;
        
        fetch('settings.php?action=clear-logs')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('Logs cleared successfully!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Error clearing logs: ' + result.message, 'error');
            }
        });
    }
    
    // Optimize database
    function optimizeDatabase() {
        if (!confirm('Optimize database tables?')) return;
        
        fetch('settings.php?action=optimize-db')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('Database optimized successfully!', 'success');
            } else {
                showNotification('Error optimizing database: ' + result.message, 'error');
            }
        });
    }
    
    // Check for updates
    function checkUpdates() {
        showNotification('Checking for updates...', 'info');
        
        fetch('settings.php?action=check-updates')
        .then(response => response.json())
        .then(result => {
            if (result.update_available) {
                showNotification('Update available: ' + result.latest_version, 'success');
            } else {
                showNotification('System is up to date!', 'success');
            }
        });
    }
    
    // Filter logs
    function filterLogs() {
        const type = document.getElementById('logType').value;
        const from = document.getElementById('logDateFrom').value;
        const to = document.getElementById('logDateTo').value;
        
        let url = 'settings.php?tab=logs';
        if (type !== 'all') url += '&type=' + type;
        if (from) url += '&from=' + from;
        if (to) url += '&to=' + to;
        
        window.location.href = url;
    }
    
    // Export logs
    function exportLogs() {
        window.location.href = 'settings.php?action=export-logs';
    }
    
    // Helper functions
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    function showNotification(message, type = 'info') {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        `;
        
        document.querySelector('.admin-content').prepend(alert);
        
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
    </script>
    
    <style>
    .settings-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 8px;
        margin-bottom: 30px;
    }
    
    .settings-header h1 {
        margin: 0 0 10px 0;
        font-size: 32px;
    }
    
    .settings-header p {
        margin: 0;
        opacity: 0.9;
    }
    
    .settings-container {
        display: flex;
        gap: 30px;
        margin-top: 30px;
    }
    
    .settings-sidebar {
        width: 250px;
        flex-shrink: 0;
    }
    
    .settings-menu {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .menu-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 15px 20px;
        color: #495057;
        text-decoration: none;
        border-bottom: 1px solid #dee2e6;
        transition: all 0.3s;
    }
    
    .menu-item:last-child {
        border-bottom: none;
    }
    
    .menu-item:hover {
        background: #f8f9fa;
        color: #0e0c5e;
    }
    
    .menu-item.active {
        background: #0e0c5e;
        color: white;
    }
    
    .menu-item i {
        width: 20px;
        text-align: center;
    }
    
    .sidebar-actions {
        margin-top: 20px;
    }
    
    .settings-content {
        flex: 1;
        background: white;
        border-radius: 8px;
        padding: 30px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .settings-tab {
        display: none;
    }
    
    .settings-tab.active {
        display: block;
    }
    
    .settings-tab h2 {
        margin: 0 0 10px 0;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .tab-description {
        color: #6c757d;
        margin-bottom: 30px;
    }
    
    .settings-form {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }
    
    .form-section {
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .form-section h3 {
        margin: 0 0 20px 0;
        color: #555;
        font-size: 18px;
        padding-bottom: 10px;
        border-bottom: 2px solid #dee2e6;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .form-group label {
        font-weight: 600;
        color: #555;
        font-size: 14px;
    }
    
    .password-input-group {
        position: relative;
    }
    
    .password-input-group input {
        padding-right: 40px;
    }
    
    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
        padding: 5px;
    }
    
    .backup-container {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }
    
    .backup-settings {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
    }
    
    .backup-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .backup-list table {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .maintenance-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }
    
    .action-card {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }
    
    .action-icon {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }
    
    .action-info {
        flex: 1;
    }
    
    .action-info h4 {
        margin: 0 0 5px 0;
        color: #333;
    }
    
    .action-info p {
        margin: 0;
        color: #6c757d;
        font-size: 13px;
    }
    
    .system-info {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .info-item label {
        font-weight: 600;
        color: #666;
        font-size: 13px;
    }
    
    .info-item span {
        color: #333;
        word-break: break-all;
    }
    
    .logs-filter {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .logs-container {
        max-height: 500px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }
    
    .logs-list {
        padding: 20px;
    }
    
    .log-item {
        display: flex;
        gap: 15px;
        padding: 15px;
        margin-bottom: 10px;
        background: white;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }
    
    .log-item:last-child {
        margin-bottom: 0;
    }
    
    .log-item.log-type-error {
        border-left: 4px solid #dc3545;
    }
    
    .log-item.log-type-warning {
        border-left: 4px solid #ffc107;
    }
    
    .log-item.log-type-success {
        border-left: 4px solid #28a745;
    }
    
    .log-item.log-type-login {
        border-left: 4px solid #007bff;
    }
    
    .log-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    
    .log-content {
        flex: 1;
    }
    
    .log-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 5px;
    }
    
    .log-time {
        font-size: 12px;
        color: #6c757d;
        white-space: nowrap;
    }
    
    .log-details {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        font-size: 12px;
        color: #6c757d;
    }
    
    .log-meta {
        margin-top: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 4px;
        font-family: monospace;
        font-size: 12px;
        max-height: 100px;
        overflow-y: auto;
    }
    
    .no-logs {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }
    
    .no-logs i {
        margin-bottom: 15px;
    }
    
    .logs-actions {
        display: flex;
        gap: 10px;
    }
    
    .about-container {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }
    
    .about-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }
    
    .about-header {
        padding: 30px;
        text-align: center;
        border-bottom: 1px solid rgba(255,255,255,0.3);
    }
    
    .about-header h3 {
        margin: 0 0 10px 0;
        font-size: 28px;
    }
    
    .about-header p {
        margin: 0;
        opacity: 0.9;
    }
    
    .about-content {
        padding: 30px;
        display: flex;
        align-items: center;
        gap: 30px;
    }
    
    .about-logo {
        flex-shrink: 0;
    }
    
    .about-details {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .about-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .about-item:last-child {
        border-bottom: none;
    }
    
    .about-item label {
        font-weight: 600;
    }
    
    .about-footer {
        padding: 20px;
        text-align: center;
        background: rgba(0,0,0,0.2);
        font-size: 14px;
        opacity: 0.8;
    }
    
    .modules-list h3 {
        margin: 0 0 20px 0;
        color: #333;
    }
    
    .modules-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }
    
    .module-item {
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        text-align: center;
        transition: transform 0.3s;
    }
    
    .module-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .module-item i {
        font-size: 32px;
        color: #0e0c5e;
        margin-bottom: 15px;
    }
    
    .module-item h5 {
        margin: 0 0 10px 0;
        color: #333;
    }
    
    .module-item p {
        margin: 0;
        color: #6c757d;
        font-size: 13px;
    }
    
    @media (max-width: 992px) {
        .settings-container {
            flex-direction: column;
        }
        
        .settings-sidebar {
            width: 100%;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
    }
    </style>
    <?php
    include 'includes/footer.php';
}

function handleSaveSettings() {
    global $pdo;
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        foreach ($input as $key => $value) {
            // Check if setting exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM system_settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $exists = $stmt->fetchColumn();
            
            if ($exists) {
                // Update existing setting
                $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
                $stmt->execute([$value, $key]);
            } else {
                // Insert new setting
                $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value, created_at) VALUES (?, ?, NOW())");
                $stmt->execute([$key, $value]);
            }
        }
        
        $pdo->commit();
        
        // Log activity
        logActivity('update_settings', 'system_settings', null, null, ['settings_updated' => array_keys($input)]);
        
        echo json_encode(['success' => true, 'message' => 'Settings saved successfully']);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error saving settings: ' . $e->getMessage()]);
    }
    
    exit;
}

function handleTestEmail() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }
    
    // Get email settings
    $settings = [];
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    // Send test email
    $subject = "Test Email - Sokatoto Admin System";
    $body = "This is a test email from your Sokatoto Admin System.\n\nIf you received this email, your SMTP settings are configured correctly.\n\nTime: " . date('Y-m-d H:i:s');
    
    $headers = "From: " . ($settings['from_email'] ?? 'noreply@sokatoto.org') . "\r\n";
    
    if (mail($email, $subject, $body, $headers)) {
        logActivity('test_email', 'system_settings', null, null, ['email' => $email, 'status' => 'sent']);
        echo json_encode(['success' => true, 'message' => 'Test email sent successfully']);
    } else {
        logActivity('test_email', 'system_settings', null, null, ['email' => $email, 'status' => 'failed']);
        echo json_encode(['success' => false, 'message' => 'Failed to send test email']);
    }
    
    exit;
}

function handleBackup() {
    global $pdo;
    
    $backupDir = '../backups/';
    
    // Create backup directory if it doesn't exist
    if (!file_exists($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    $backupFile = $backupDir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    
    try {
        // Get all tables
        $tables = [];
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch()) {
            $tables[] = $row[0];
        }
        
        // Open backup file
        $handle = fopen($backupFile, 'w');
        
        // Write SQL header
        fwrite($handle, "-- Sokatoto Database Backup\n");
        fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- Database: " . DB_NAME . "\n\n");
        
        // Backup each table
        foreach ($tables as $table) {
            // Drop table if exists
            fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n\n");
            
            // Get create table statement
            $createStmt = $pdo->query("SHOW CREATE TABLE `$table`");
            $createRow = $createStmt->fetch();
            fwrite($handle, $createRow[1] . ";\n\n");
            
            // Get table data
            $dataStmt = $pdo->query("SELECT * FROM `$table`");
            $rowCount = 0;
            
            while ($row = $dataStmt->fetch(PDO::FETCH_ASSOC)) {
                if ($rowCount === 0) {
                    fwrite($handle, "INSERT INTO `$table` VALUES\n");
                } else {
                    fwrite($handle, ",\n");
                }
                
                $values = array_map(function($value) use ($pdo) {
                    if ($value === null) return 'NULL';
                    return $pdo->quote($value);
                }, array_values($row));
                
                fwrite($handle, "(" . implode(', ', $values) . ")");
                $rowCount++;
            }
            
            if ($rowCount > 0) {
                fwrite($handle, ";\n\n");
            }
        }
        
        fclose($handle);
        
        // Compress backup if setting enabled
        $compress = true; // Get from settings
        if ($compress) {
            $zipFile = str_replace('.sql', '.zip', $backupFile);
            $zip = new ZipArchive();
            
            if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
                $zip->addFile($backupFile, basename($backupFile));
                $zip->close();
                unlink($backupFile); // Remove SQL file after compression
                $backupFile = $zipFile;
            }
        }
        
        // Log activity
        logActivity('create_backup', 'system', null, null, [
            'file' => basename($backupFile),
            'size' => filesize($backupFile)
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Backup created successfully',
            'file' => basename($backupFile)
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error creating backup: ' . $e->getMessage()
        ]);
    }
    
    exit;
}

function handleRestore() {
    global $pdo;
    
    $backupDir = '../backups/';
    
    // Check if file was uploaded
    if (isset($_FILES['backup']) && $_FILES['backup']['error'] === 0) {
        $tempFile = $_FILES['backup']['tmp_name'];
        $fileName = $_FILES['backup']['name'];
        $backupFile = $backupDir . $fileName;
        
        move_uploaded_file($tempFile, $backupFile);
    } elseif (isset($_GET['file'])) {
        $fileName = $_GET['file'];
        $backupFile = $backupDir . $fileName;
    } else {
        echo json_encode(['success' => false, 'message' => 'No backup file specified']);
        exit;
    }
    
    if (!file_exists($backupFile)) {
        echo json_encode(['success' => false, 'message' => 'Backup file not found']);
        exit;
    }
    
    try {
        // Check if file is compressed
        if (strpos($fileName, '.zip') !== false) {
            $zip = new ZipArchive();
            if ($zip->open($backupFile) === TRUE) {
                $zip->extractTo($backupDir);
                $sqlFile = $backupDir . str_replace('.zip', '.sql', $fileName);
                $zip->close();
            } else {
                throw new Exception('Failed to extract ZIP file');
            }
        } else {
            $sqlFile = $backupFile;
        }
        
        // Read SQL file
        $sql = file_get_contents($sqlFile);
        
        // Execute SQL queries
        $pdo->beginTransaction();
        
        // Split SQL by semicolon
        $queries = explode(';', $sql);
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                $pdo->exec($query);
            }
        }
        
        $pdo->commit();
        
        // Clean up extracted file
        if (isset($sqlFile) && $sqlFile !== $backupFile && file_exists($sqlFile)) {
            unlink($sqlFile);
        }
        
        // Log activity
        logActivity('restore_backup', 'system', null, null, [
            'file' => basename($backupFile),
            'success' => true
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Backup restored successfully']);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error restoring backup: ' . $e->getMessage()]);
    }
    
    exit;
}

function handleClearCache() {
    // Clear cache directory
    $cacheDir = '../cache/';
    
    if (file_exists($cacheDir) && is_dir($cacheDir)) {
        $files = glob($cacheDir . '*');
        $deletedCount = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $deletedCount++;
            }
        }
        
        // Log activity
        logActivity('clear_cache', 'system', null, null, ['files_deleted' => $deletedCount]);
        
        $_SESSION['message'] = "Cache cleared successfully. {$deletedCount} files removed.";
    } else {
        $_SESSION['error'] = "Cache directory not found.";
    }
    
    header('Location: settings.php');
    exit;
}

function handleLogs() {
    // This would handle log viewing functionality
    // For now, redirect to settings page
    header('Location: settings.php?tab=logs');
    exit;
}

function getSiteStatistics() {
    global $pdo;
    
    $stats = [
        'database_size' => 0,
        'backup_count' => 0,
        'cache_size' => 0,
        'log_count' => 0
    ];
    
    try {
        // Get database size
        $stmt = $pdo->query("
            SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb 
            FROM information_schema.tables 
            WHERE table_schema = DATABASE()
        ");
        $result = $stmt->fetch();
        $stats['database_size'] = $result['size_mb'] ?? 0;
        
        // Get backup count
        $backupDir = '../backups/';
        if (file_exists($backupDir) && is_dir($backupDir)) {
            $files = glob($backupDir . '*.{sql,zip}', GLOB_BRACE);
            $stats['backup_count'] = count($files);
        }
        
        // Get cache size
        $cacheDir = '../cache/';
        if (file_exists($cacheDir) && is_dir($cacheDir)) {
            $size = 0;
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cacheDir)) as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
            $stats['cache_size'] = round($size / 1024 / 1024, 2);
        }
        
        // Get log count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM activity_logs");
        $result = $stmt->fetch();
        $stats['log_count'] = $result['count'] ?? 0;
        
    } catch (Exception $e) {
        // Silently fail statistics
    }
    
    return $stats;
}

function getRecentActivities() {
    global $pdo;
    
    $activities = [];
    
    try {
        $stmt = $pdo->query("
            SELECT * FROM activity_logs 
            ORDER BY created_at DESC 
            LIMIT 20
        ");
        $activities = $stmt->fetchAll();
    } catch (Exception $e) {
        // Return empty array if table doesn't exist
    }
    
    return $activities;
}

function getMySQLVersion() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT VERSION() as version");
        $result = $stmt->fetch();
        return $result['version'] ?? 'Unknown';
    } catch (Exception $e) {
        return 'Unknown';
    }
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}
?>