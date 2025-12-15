<?php
require_once '../../config/database.php';
requireAdmin();

// This would be connected to MPESA API in production
// For now, we'll create a manual confirmation system

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    $transaction_id = trim($_POST['transaction_id'] ?? '');
    $mpesa_code = trim($_POST['mpesa_code'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $sender_name = trim($_POST['sender_name'] ?? '');
    $sender_phone = trim($_POST['sender_phone'] ?? '');
    
    // Validate MPESA transaction
    if (empty($mpesa_code)) {
        $response['message'] = 'MPESA code is required';
    } elseif (empty($amount)) {
        $response['message'] = 'Amount is required';
    } else {
        try {
            // Check if transaction already exists
            $stmt = $pdo->prepare("SELECT id FROM donations WHERE transaction_id = ?");
            $stmt->execute([$mpesa_code]);
            
            if ($stmt->fetch()) {
                $response['message'] = 'This MPESA transaction has already been recorded';
            } else {
                // Record the donation
                $stmt = $pdo->prepare("
                    INSERT INTO donations 
                    (donor_name, donor_phone, amount, currency, payment_method, 
                     transaction_id, purpose, status)
                    VALUES (?, ?, ?, 'KES', 'mpesa', ?, 'MPESA Donation', 'completed')
                ");
                
                $stmt->execute([
                    $sender_name ?: 'Anonymous',
                    $sender_phone,
                    $amount,
                    $mpesa_code
                ]);
                
                // Send SMS confirmation if phone provided
                if (!empty($sender_phone)) {
                    // In production, integrate with SMS API like Africa's Talking
                    $message = "Thank you for donating KES $amount to STMI Trust. MPESA Code: $mpesa_code";
                    
                    // This is a placeholder for SMS integration
                    // send_sms($sender_phone, $message);
                }
                
                $response['success'] = true;
                $response['message'] = 'MPESA donation confirmed and recorded';
                $response['donation_id'] = $pdo->lastInsertId();
            }
            
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>