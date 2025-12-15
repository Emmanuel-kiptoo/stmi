<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $response = ['success' => false, 'message' => ''];
    
    if (!isset($data['user_id'])) {
        $response['message'] = 'User ID required.';
        echo json_encode($response);
        exit;
    }
    
    $user_id = intval($data['user_id']);
    
    try {
        // Get user details
        $stmt = $pdo->prepare("SELECT email, full_name FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new Exception('User not found.');
        }
        
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store token in database
        $stmt = $pdo->prepare("
            INSERT INTO password_resets 
            (user_id, token, expires_at) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            token = VALUES(token), 
            expires_at = VALUES(expires_at), 
            created_at = NOW()
        ");
        $stmt->execute([$user_id, $token, $expires]);
        
        // Send email with reset link
        $reset_link = "https://" . $_SERVER['HTTP_HOST'] . "/admin/reset_password.php?token=$token";
        
        $to = $user['email'];
        $subject = "Password Reset Request - STMI Trust Admin";
        $message = "
            <html>
            <body>
                <h2>Password Reset Request</h2>
                <p>Hello " . htmlspecialchars($user['full_name']) . ",</p>
                <p>You have requested a password reset for your STMI Trust Admin account.</p>
                <p>Click the link below to reset your password:</p>
                <p><a href='$reset_link' style='background:#0e0c5e;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;'>
                    Reset Password
                </a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you did not request this reset, please ignore this email.</p>
                <p>Best regards,<br>
                STMI Trust Admin Team</p>
            </body>
            </html>
        ";
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: STMI Trust Admin <admin@stmitrust.org>',
            'Reply-To: admin@stmitrust.org',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        if (mail($to, $subject, $message, implode("\r\n", $headers))) {
            $response['success'] = true;
            $response['message'] = 'Reset link sent successfully.';
            logAction('send_reset_link', "Sent reset link to user ID: $user_id");
        } else {
            throw new Exception('Failed to send email.');
        }
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method']);
?>