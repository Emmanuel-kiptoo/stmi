<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));
    
    // Validate required fields
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    if (empty($subject)) {
        $errors[] = "Subject is required.";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required.";
    }
    
    // If no errors, process the message
    if (empty($errors)) {
        // You can choose one of these options:
        
        // OPTION 1: Save to database (uncomment and configure)
        /*
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=your_db', 'username', 'password');
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $subject, $message]);
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
        */
        
        // OPTION 2: Save to file (uncomment)
        /*
        $file_data = date('Y-m-d H:i:s') . " | $name | $email | $subject | $message\n";
        file_put_contents('contact_messages.txt', $file_data, FILE_APPEND);
        */
        
        // OPTION 3: Send email (currently active)
        $to = "stmitrust@gmail.com"; // Your organization email
        $email_subject = "New Contact Form Message: " . $subject;
        $email_body = "
        <html>
        <head>
            <title>New Contact Form Submission</title>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #0e0c5e; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f8f9fa; }
                .field { margin-bottom: 15px; }
                .field-label { font-weight: bold; color: #0e0c5e; }
                .footer { background-color: #ff9d0b; color: white; padding: 10px; text-align: center; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>New Message from SMIT Contact Form</h2>
                </div>
                <div class='content'>
                    <div class='field'>
                        <span class='field-label'>Name:</span><br>
                        $name
                    </div>
                    <div class='field'>
                        <span class='field-label'>Email:</span><br>
                        $email
                    </div>
                    <div class='field'>
                        <span class='field-label'>Subject:</span><br>
                        $subject
                    </div>
                    <div class='field'>
                        <span class='field-label'>Message:</span><br>
                        $message
                    </div>
                </div>
                <div class='footer'>
                    <p>This message was sent from the SMIT website contact form</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Email headers
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: SMIT Website <noreply@stmitrust.org>" . "\r\n";
        $headers .= "Reply-To: $email" . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        // Send email
        if (mail($to, $email_subject, $email_body, $headers)) {
            // Success - redirect to thank you page
            header("Location: contact_thankyou.php");
            exit();
        } else {
            $errors[] = "Sorry, there was an error sending your message. Please try again later.";
        }
    }
    
    // If there are errors, store them in session and redirect back
    $_SESSION['contact_errors'] = $errors;
    $_SESSION['contact_data'] = [
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'message' => $message
    ];
    header("Location: contact.php");
    exit();
} else {
    // If not POST request, redirect to contact page
    header("Location: contact.php");
    exit();
}
?>