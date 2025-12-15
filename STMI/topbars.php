<?php
// Get current page filename for active state
$current_page = basename($_SERVER['PHP_SELF']);
$current_section = isset($_GET['section']) ? $_GET['section'] : '';

// Top Bars Data
$organizationName = "SOKATOTO MUDA INITIATIVE TRUST";
$contacts = [
    'phone' => '+254 728 274304',
    'email' => 'stmitrust@gmail.com',
    'address' => '105-00508 Nairobi. Kenya'
];

$socialMedia = [
    'facebook' => '#',
    'twitter' => '#',
    'youtube' => '#',
    'instagram' => '#',
    'whatsapp' => '#',
    'tiktok' => '#'
];

// Define about dropdown items
$aboutDropdown = [
    'who-we-are' => [
        'title' => 'Who We Are',
        'icon' => 'fas fa-users',
        'link' => 'about.php?section=who-we-are'
    ],
    'core-values' => [
        'title' => 'Our Core Values',
        'icon' => 'fas fa-heart',
        'link' => 'about.php?section=core-values'
    ],
    'our-programs' => [
        'title' => 'Our Programs',
        'icon' => 'fas fa-project-diagram',
        'link' => 'about.php?section=our-programs'
    ],
    'our-team' => [
        'title' => 'Our Team',
        'icon' => 'fas fa-user-tie',
        'link' => 'about.php?section=our-team'
    ],
    'history' => [
        'title' => 'Organisation History',
        'icon' => 'fas fa-history',
        'link' => 'about.php?section=history'
    ]
];

// Define media dropdown items
$mediaDropdown = [
    'articles' => [
        'title' => 'Articles',
        'icon' => 'fas fa-newspaper',
        'link' => 'media.php?section=articles'
    ],
    'newsletters' => [
        'title' => 'Newsletters',
        'icon' => 'fas fa-envelope-open-text',
        'link' => 'media.php?section=newsletters'
    ],
    'resources' => [
        'title' => 'Resources',
        'icon' => 'fas fa-book',
        'link' => 'media.php?section=resources'
    ],
    'gallery' => [
        'title' => 'Gallery',
        'icon' => 'fas fa-images',
        'link' => 'media.php?section=gallery'
    ],
    'reports' => [
        'title' => 'Reports',
        'icon' => 'fas fa-chart-bar',
        'link' => 'media.php?section=reports'
    ]
];

// Define menu items with their respective links
$menuItems = [
    'Home' => [
        'icon' => 'fas fa-home',
        'link' => 'index.php',
        'dropdown' => false
    ],
    'About' => [
        'icon' => 'fas fa-info-circle',
        'link' => 'about.php',
        'dropdown' => true,
        'items' => $aboutDropdown
    ],
    'Media' => [
        'icon' => 'fas fa-photo-video',
        'link' => 'media.php',
        'dropdown' => true,
        'items' => $mediaDropdown
    ],
    'Contact' => [
        'icon' => 'fas fa-envelope',
        'link' => 'contact.php',
        'dropdown' => false
    ],
    'Donate' => [
        'icon' => 'fas fa-hand-holding-heart',
        'link' => 'donate.php',
        'dropdown' => false
    ]
];

// Function to check if current page is active
function isActivePage($page, $current_page, $item_link = '') {
    if ($page === 'about.php' && strpos($current_page, 'about') !== false) {
        return true;
    }
    if ($page === 'media.php' && strpos($current_page, 'media') !== false) {
        return true;
    }
    return ($current_page === $item_link);
}
?>

<!-- Top Bar 1: Organization Name & Social Media -->
<div class="top-bar top-bar-1">
    <div class="bar-container">
        <div class="org-name">
            <?php echo $organizationName; ?>
        </div>
        <div class="social-section">
            <span class="follow-text">Follow us:</span>
            <div class="social-icons">
                <a href="<?php echo $socialMedia['facebook']; ?>" class="social-icon" title="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="<?php echo $socialMedia['twitter']; ?>" class="social-icon" title="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="<?php echo $socialMedia['youtube']; ?>" class="social-icon" title="YouTube">
                    <i class="fab fa-youtube"></i>
                </a>
                <a href="<?php echo $socialMedia['instagram']; ?>" class="social-icon" title="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="<?php echo $socialMedia['whatsapp']; ?>" class="social-icon" title="WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </a>
                <a href="<?php echo $socialMedia['tiktok']; ?>" class="social-icon" title="TikTok">
                    <i class="fab fa-tiktok"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Top Bar 2: Logo & Contact Info -->
<div class="top-bar top-bar-2">
    <div class="bar-container">
        <div class="logo-section">
            <!-- Logo from images folder -->
            <a href="index.php" class="logo-link">
                <img src="images/sk_logo.png" alt="Sokatoto Muda Initiative Trust Logo" class="logo-image">
            </a>
        </div>
        <div class="contact-info">
            <div class="contact-item">
                <div class="contact-label">Call us:</div>
                <div class="contact-detail"><?php echo $contacts['phone']; ?></div>
            </div>
            <div class="contact-item">
                <div class="contact-label">Email us:</div>
                <div class="contact-detail"><?php echo $contacts['email']; ?></div>
            </div>
            <div class="contact-item">
                <div class="contact-label">Address:</div>
                <div class="contact-detail"><?php echo $contacts['address']; ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Top Bar 3: Navigation Menu -->
<div class="top-bar top-bar-3">
    <div class="bar-container">
        <nav class="main-nav">
            <?php foreach ($menuItems as $item => $data): 
                $is_active = isActivePage($current_page, $data['link']) ? 'active' : '';
                
                if ($data['dropdown']): 
            ?>
                <div class="nav-item dropdown <?php echo $is_active; ?>">
                    <a href="<?php echo $data['link']; ?>" class="dropdown-toggle">
                        <i class="<?php echo $data['icon']; ?>"></i>
                        <span><?php echo $item; ?></span>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </a>
                    <div class="dropdown-menu">
                        <?php foreach ($data['items'] as $sectionId => $dropdownItem): 
                            $dropdown_active = '';
                            
                            // Check if this dropdown item is active
                            if ($current_page === 'about.php' && isset($_GET['section']) && $_GET['section'] === $sectionId) {
                                $dropdown_active = 'active';
                            }
                            if ($current_page === 'media.php' && isset($_GET['section']) && $_GET['section'] === $sectionId) {
                                $dropdown_active = 'active';
                            }
                        ?>
                            <a href="<?php echo $dropdownItem['link']; ?>" class="dropdown-item <?php echo $dropdown_active; ?>">
                                <i class="<?php echo $dropdownItem['icon']; ?>"></i>
                                <span><?php echo $dropdownItem['title']; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo $data['link']; ?>" class="nav-item <?php echo $is_active; ?>">
                    <i class="<?php echo $data['icon']; ?>"></i>
                    <span><?php echo $item; ?></span>
                </a>
            <?php endif; endforeach; ?>
        </nav>
    </div>
</div>