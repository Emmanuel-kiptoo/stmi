<?php
require_once '../config/database.php';
requireAdmin();

// Get admin profile
$admin_id = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address';
    }
    
    // Password change
    if (!empty($new_password)) {
        if (!password_verify($current_password, $admin['password'])) {
            $errors[] = 'Current password is incorrect';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'New passwords do not match';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'New password must be at least 6 characters';
        }
    }
    
    if (empty($errors)) {
        $update_data = [
            'full_name' => $full_name,
            'email' => $email
        ];
        
        // Update password if provided
        if (!empty($new_password)) {
            $update_data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }
        
        $stmt = $pdo->prepare("
            UPDATE users 
            SET full_name = ?, email = ?" . 
            (!empty($new_password) ? ", password = ?" : "") . "
            WHERE id = ?
        ");
        
        $params = [$full_name, $email];
        if (!empty($new_password)) {
            $params[] = password_hash($new_password, PASSWORD_DEFAULT);
        }
        $params[] = $admin_id;
        
        $stmt->execute($params);
        
        $_SESSION['success_message'] = 'Profile updated successfully';
        header('Location: profile.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1>My Profile</h1>
        </div>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="profile-container">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php 
                        $initials = '';
                        if ($admin['full_name']) {
                            $nameParts = explode(' ', $admin['full_name']);
                            $initials = strtoupper(substr($nameParts[0], 0, 1));
                            if (count($nameParts) > 1) {
                                $initials .= strtoupper(substr($nameParts[1], 0, 1));
                            }
                        } else {
                            $initials = 'A';
                        }
                        ?>
                        <div class="avatar-circle">
                            <?php echo $initials; ?>
                        </div>
                        <h3><?php echo htmlspecialchars($admin['full_name']); ?></h3>
                        <p><?php echo ucfirst($admin['role']); ?></p>
                    </div>
                </div>
                
                <form method="POST" class="profile-form">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" value="<?php echo htmlspecialchars($admin['username']); ?>" readonly>
                        <small>Username cannot be changed</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Account Created</label>
                        <input type="text" value="<?php echo date('F j, Y', strtotime($admin['created_at'])); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Last Login</label>
                        <input type="text" value="<?php echo $admin['last_login'] ? date('F j, Y H:i', strtotime($admin['last_login'])) : 'Never'; ?>" readonly>
                    </div>
                    
                    <h3>Change Password</h3>
                    
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password">
                    </div>
                    
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password">
                    </div>
                    
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>