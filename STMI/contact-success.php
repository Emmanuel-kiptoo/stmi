<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Sent - STMI Trust</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .success-container {
            min-height: 70vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 40px 20px;
        }
        .success-icon {
            font-size: 80px;
            color: #57cc99;
            margin-bottom: 30px;
        }
        .success-message h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 2.5rem;
        }
        .success-message p {
            color: #666;
            font-size: 1.2rem;
            margin-bottom: 30px;
            max-width: 600px;
            line-height: 1.6;
        }
        .action-buttons {
            display: flex;
            gap: 20px;
            margin-top: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .btn {
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
        }
        .btn-secondary:hover {
            background: #e9ecef;
        }
        .whats-next {
            margin-top: 50px;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 10px;
            max-width: 800px;
        }
        .whats-next h3 {
            color: #333;
            margin-bottom: 20px;
        }
        .next-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            text-align: left;
        }
        .next-step {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .next-step h4 {
            color: #3498db;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php include 'topbars.php'; ?>
    
    <main class="success-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <div class="success-message">
            <h1>Message Sent Successfully!</h1>
            <p>Thank you for contacting Soka Toto Muda Initiative Trust. Your message has been received and our team will get back to you as soon as possible.</p>
            
            <div class="action-buttons">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Back to Home
                </a>
                <a href="contact.php" class="btn btn-secondary">
                    <i class="fas fa-envelope"></i> Send Another Message
                </a>
                <a href="donate.php" class="btn btn-primary">
                    <i class="fas fa-heart"></i> Make a Donation
                </a>
            </div>
        </div>
        
        <div class="whats-next">
            <h3>What happens next?</h3>
            <div class="next-steps">
                <div class="next-step">
                    <h4><i class="fas fa-inbox"></i> We'll Review Your Message</h4>
                    <p>Our team will read your message and determine the best person to handle your inquiry.</p>
                </div>
                <div class="next-step">
                    <h4><i class="fas fa-reply"></i> You'll Get a Response</h4>
                    <p>We typically respond within 24-48 hours during business days.</p>
                </div>
                <div class="next-step">
                    <h4><i class="fas fa-headset"></i> Further Assistance</h4>
                    <p>If urgent, you can call us at +254 728 274304</p>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
</body>
</html>