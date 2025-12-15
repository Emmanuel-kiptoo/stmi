<?php
session_start();
require_once 'config/database.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // Store data in session for re-population
    $_SESSION['contact_data'] = [
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'message' => $message
    ];
    
    // Validate data
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required';
    } elseif (strlen($message) < 10) {
        $errors[] = 'Message must be at least 10 characters long';
    }
    
    // If there are errors, redirect back
    if (!empty($errors)) {
        $_SESSION['contact_errors'] = $errors;
        header('Location: contact.php');
        exit;
    }
    
    try {
        // Save to database
        $stmt = $pdo->prepare("
            INSERT INTO contact_messages 
            (name, email, subject, message, ip_address)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$name, $email, $subject, $message, $ip_address]);
        
        // Send email notification to admin
        $admin_email = 'stmitrust@gmail.com';
        $email_subject = "New Contact Message: $subject";
        $email_body = "
            <html>
            <body>
                <h2>New Contact Message Received</h2>
                <p><strong>From:</strong> $name</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Subject:</strong> $subject</p>
                <p><strong>Message:</strong></p>
                <p>" . nl2br(htmlspecialchars($message)) . "</p>
                <p><strong>Received:</strong> " . date('Y-m-d H:i:s') . "</p>
                <p><strong>IP Address:</strong> $ip_address</p>
            </body>
            </html>
        ";
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . $email,
            'Reply-To: ' . $email,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        mail($admin_email, $email_subject, $email_body, implode("\r\n", $headers));
        
        // Clear session data
        unset($_SESSION['contact_data']);
        unset($_SESSION['contact_errors']);
        
        // Show success page
        header('Location: contact-success.php');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['contact_errors'] = ['An error occurred. Please try again.'];
        header('Location: contact.php');
        exit;
    }
} else {
    // If not POST, redirect to contact page
    header('Location: contact.php');
    exit;
}
?>