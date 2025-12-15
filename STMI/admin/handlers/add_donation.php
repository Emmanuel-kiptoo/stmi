<?php
require_once '../../config/database.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    $donor_name = trim($_POST['donor_name'] ?? '');
    $donor_email = trim($_POST['donor_email'] ?? '');
    $donor_phone = trim($_POST['donor_phone'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $currency = $_POST['currency'] ?? 'KES';
    $payment_method = $_POST['payment_method'] ?? '';
    $transaction_id = trim($_POST['transaction_id'] ?? '');
    $purpose = trim($_POST['purpose'] ?? '');
    $campaign_id = $_POST['campaign_id'] ?? null;
    $notes = trim($_POST['notes'] ?? '');
    
    // Validation
    if (empty($donor_name)) {
        $response['message'] = 'Donor name is required';
    } elseif ($amount <= 0) {
        $response['message'] = 'Amount must be greater than 0';
    } elseif (empty($payment_method)) {
        $response['message'] = 'Payment method is required';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO donations 
                (donor_name, donor_email, donor_phone, amount, currency, 
                 payment_method, transaction_id, purpose, campaign_id, notes, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed')
            ");
            
            $stmt->execute([
                $donor_name, $donor_email, $donor_phone, $amount, $currency,
                $payment_method, $transaction_id, $purpose, $campaign_id, $notes
            ]);
            
            // Update campaign total if applicable
            if ($campaign_id) {
                $updateStmt = $pdo->prepare("
                    UPDATE donation_campaigns 
                    SET current_amount = current_amount + ? 
                    WHERE id = ?
                ");
                $updateStmt->execute([$amount, $campaign_id]);
            }
            
            // Send thank you email
            if (!empty($donor_email)) {
                $to = $donor_email;
                $subject = "Thank you for your donation to Soka Toto Muda Initiative Trust";
                $message = "
                    <html>
                    <body>
                        <h2>Thank You for Your Generous Donation!</h2>
                        <p>Dear $donor_name,</p>
                        <p>We have successfully received your donation of <strong>$currency $amount</strong>.</p>
                        <p><strong>Transaction Details:</strong></p>
                        <ul>
                            <li>Amount: $currency $amount</li>
                            <li>Payment Method: $payment_method</li>
                            <li>Transaction ID: $transaction_id</li>
                            <li>Date: " . date('F j, Y') . "</li>
                        </ul>
                        <p>Your contribution will help us continue our work in empowering children and young mothers through sports, creative arts, and psychosocial support.</p>
                        <p>If you need an official receipt for your records, please reply to this email.</p>
                        <p>With gratitude,<br>
                        The STMI Trust Team</p>
                    </body>
                    </html>
                ";
                
                $headers = [
                    'MIME-Version: 1.0',
                    'Content-type: text/html; charset=utf-8',
                    'From: stmitrust@gmail.com',
                    'Reply-To: stmitrust@gmail.com',
                    'X-Mailer: PHP/' . phpversion()
                ];
                
                mail($to, $subject, $message, implode("\r\n", $headers));
            }
            
            $response['success'] = true;
            $response['message'] = 'Donation recorded successfully';
            $response['donation_id'] = $pdo->lastInsertId();
            
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>