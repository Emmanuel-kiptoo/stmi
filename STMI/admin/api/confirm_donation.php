<?php
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'donor_name' => trim($_POST['name']),
        'donor_email' => trim($_POST['email']),
        'donor_phone' => trim($_POST['phone']),
        'amount' => floatval($_POST['amount']),
        'payment_method' => $_POST['payment_method'],
        'transaction_id' => trim($_POST['transaction_id']),
        'purpose' => $_POST['purpose'] ?? 'general',
        'notes' => trim($_POST['notes'] ?? '')
    ];
    
    // Validation
    $errors = [];
    if (empty($data['donor_name'])) $errors[] = 'Name is required';
    if ($data['amount'] <= 0) $errors[] = 'Valid amount is required';
    if (empty($data['payment_method'])) $errors[] = 'Payment method is required';
    if (empty($data['transaction_id'])) $errors[] = 'Transaction ID is required';
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO admin_donations 
                (donor_name, donor_email, donor_phone, amount, payment_method, transaction_id, purpose, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['donor_name'],
                $data['donor_email'],
                $data['donor_phone'],
                $data['amount'],
                $data['payment_method'],
                $data['transaction_id'],
                $data['purpose'],
                $data['notes']
            ]);
            
            $response = [
                'success' => true,
                'message' => 'Thank you! Your donation has been recorded. We will send you a receipt shortly.',
                'receipt_id' => $pdo->lastInsertId()
            ];
            
            // You can send email confirmation here
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate transaction ID
                $response = [
                    'success' => false,
                    'message' => 'This transaction has already been recorded.'
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage()
                ];
            }
        }
    } else {
        $response = [
            'success' => false,
            'message' => 'Please fix the following errors: ' . implode(', ', $errors)
        ];
    }
} else {
    $response = [
        'success' => false,
        'message' => 'Invalid request method.'
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
?>