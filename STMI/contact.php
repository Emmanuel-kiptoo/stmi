<?php
// Start session for form errors
session_start();

// Retrieve form data if it exists in session
$formData = $_SESSION['contact_data'] ?? [
    'name' => '',
    'email' => '',
    'subject' => '',
    'message' => ''
];

// Retrieve errors if they exist
$errors = $_SESSION['contact_errors'] ?? [];

// Clear session data after retrieving
unset($_SESSION['contact_data']);
unset($_SESSION['contact_errors']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Sokatoto Muda Initiative Trust</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'topbars.php'; ?>
    
    <!-- Hero Banner with Image -->
    <section class="contact-hero">
        <div class="contact-hero-content">
            <h1>Contact Us</h1>
            <p>Get in touch with the STMI team. We're here to help and answer any questions you might have.</p>
        </div>
    </section>

    <!-- Contact Form and Map Section -->
    <main class="contact-container">
        <!-- Display errors if any -->
        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <h3>Please fix the following errors:</h3>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="contact-grid">
            <!-- Column 1: Contact Form -->
            <div class="contact-form-section">
                <h2 class="form-subtitle">Want to get in touch?</h2>
                <p class="form-description">
                    Send us a message using this form and we will get back to you as soon as we can.
                </p>
                
                <form class="contact-form" action="send_message.php" method="POST">
                    <!-- Row 1: Name and Email -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name" class="form-label">
                                Your Name <span class="required">*</span>
                            </label>
                            <input type="text" id="name" name="name" class="form-input" 
                                   value="<?php echo htmlspecialchars($formData['name']); ?>"
                                   required placeholder="Enter your full name">
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">
                                Your Email <span class="required">*</span>
                            </label>
                            <input type="email" id="email" name="email" class="form-input" 
                                   value="<?php echo htmlspecialchars($formData['email']); ?>"
                                   required placeholder="Enter your email address">
                        </div>
                    </div>
                    
                    <!-- Row 2: Subject -->
                    <div class="form-group">
                        <label for="subject" class="form-label">
                            Subject <span class="required">*</span>
                        </label>
                        <input type="text" id="subject" name="subject" class="form-input" 
                               value="<?php echo htmlspecialchars($formData['subject']); ?>"
                               required placeholder="What is this regarding?">
                    </div>
                    
                    <!-- Row 3: Message -->
                    <div class="form-group">
                        <label for="message" class="form-label">
                            Message <span class="required">*</span>
                        </label>
                        <textarea id="message" name="message" class="form-textarea" 
                                  required placeholder="Type your message here..."><?php echo htmlspecialchars($formData['message']); ?></textarea>
                    </div>
                    
                    <!-- Send Message Button -->
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-paper-plane"></i>
                        Send Message
                    </button>
                </form>
            </div>
            
            <!-- Column 2: Map and Contact Info -->
            <div class="map-section">
                <h2 class="map-title">Our Location</h2>
                
                <!-- Google Map -->
                <div class="map-container">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.854743462391!2d36.726367574717834!3d-1.290331099999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f1bcf8f986cbf%3A0x2b58956283dd24d2!2sAlpha%20Glory%20Community%20Educational%20Center!5e0!3m2!1sen!2ske!4v1702486400000!5m2!1sen!2ske" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
                
                
            </div>
        </div>
    </main>
    <script src="script.js"></script>
    <?php include 'footer.php'; ?>
</body>
</html>