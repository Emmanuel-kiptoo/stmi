<?php
// Footer data
$footerData = [
    'mission' => 'Empowers children and teen mothers in informal settlements through talent exploration, psychosocial support, skills development, and mentorship, fostering resilience, independence, and a brighter future.',
    'address' => [
        'title' => 'Address',
        'lines' => [
            'Email: stmitrust@gmail.com',
            'Telephone: +254 728 274304',
            'Address: 105-00508 Nairobi',
            'Location: Kabiria,  Kenya'
            
            
        ]
    ],
    'quickLinks' => [
        'title' => 'Quick Links',
        'links' => [
            'Home' => 'index.php',
            'About' => 'about.php',
            'Media' => 'media.php',
            'Contact' => 'contact.php',
            'Terms and Conditions' => 'terms.php',
            'Donate' => 'donate.php'
        ]
    ],
    'newsletter' => [
        'title' => 'Reach out to us',
        'description' => 'Stay updated with our latest projects and initiatives. Subscribe to our newsletter.',
        'placeholder' => 'Enter your email address'
    ],
    'socialMedia' => [
        'facebook' => '#',
        'twitter' => '#',
        'youtube' => '#',
        'instagram' => '#',
        'whatsapp' => '#',
        'tiktok' => '#'
    ],
    'copyright' => '© Soko Toto Muda Initiative Trust, All Right Reserved. Designed by K-tech'
];
?>

<footer class="footer">
    <div class="footer-top">
        <div class="footer-container">
            <!-- Column 1: Mission Statement & Social Media -->
            <div class="footer-column">
                <div class="footer-logo">
                    <img src="images/sk_logo.png" alt="SMIT Logo" class="footer-logo-image">
                </div>
                <p class="mission-text">
                    <?php echo $footerData['mission']; ?>
                </p>
                <div class="footer-social">
                    <span class="social-title">Follow Us:</span>
                    <div class="social-icons">
                        <a href="<?php echo $footerData['socialMedia']['facebook']; ?>" class="social-icon" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="<?php echo $footerData['socialMedia']['twitter']; ?>" class="social-icon" title="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="<?php echo $footerData['socialMedia']['youtube']; ?>" class="social-icon" title="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="<?php echo $footerData['socialMedia']['instagram']; ?>" class="social-icon" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="<?php echo $footerData['socialMedia']['whatsapp']; ?>" class="social-icon" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="<?php echo $footerData['socialMedia']['tiktok']; ?>" class="social-icon" title="TikTok">
                            <i class="fab fa-tiktok"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Column 2: Address -->
            <div class="footer-column">
                <h3 class="footer-title"><?php echo $footerData['address']['title']; ?></h3>
                <div class="address-info">
                    <?php foreach ($footerData['address']['lines'] as $line): ?>
                        <p class="address-line"><?php echo $line; ?></p>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Column 3: Quick Links -->
            <div class="footer-column">
                <h3 class="footer-title"><?php echo $footerData['quickLinks']['title']; ?></h3>
                <ul class="quick-links">
                    <?php foreach ($footerData['quickLinks']['links'] as $text => $url): ?>
                        <li class="quick-link-item">
                            <a href="<?php echo $url; ?>" class="quick-link">
                                <i class="fas fa-chevron-right"></i>
                                <?php echo $text; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Column 4: Newsletter -->
            <div class="footer-column">
                <h3 class="footer-title"><?php echo $footerData['newsletter']['title']; ?></h3>
                <p class="newsletter-desc">
                    <?php echo $footerData['newsletter']['description']; ?>
                </p>
                <form class="newsletter-form" action="subscribe.php" method="POST">
                    <div class="input-group">
                        <input type="email" 
                               name="email" 
                               class="email-input" 
                               placeholder="<?php echo $footerData['newsletter']['placeholder']; ?>" 
                               required>
                        <button type="submit" class="subscribe-btn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                    <p class="form-note">We respect your privacy. Unsubscribe at any time.</p>
                </form>
            </div>
        </div>
    </div>

    <!-- Copyright Section -->
    <div class="footer-bottom">
        <div class="footer-container">
            <p class="copyright-text">
                <?php echo $footerData['copyright']; ?>
            </p>
        </div>
    </div>
</footer>