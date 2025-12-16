<?php
// Add database connection at the top of about.php
require_once 'config/database.php';

// Get active section from URL or default to first
$activeSection = isset($_GET['section']) ? $_GET['section'] : 'who-we-are';

// Define about page sections
$aboutSections = [
    'who-we-are' => [
        'title' => 'Who We Are',
        'icon' => 'fas fa-users',
        'active' => ($activeSection === 'who-we-are')
    ],
    'core-values' => [
        'title' => 'Our Core Values',
        'icon' => 'fas fa-heart',
        'active' => ($activeSection === 'core-values')
    ],
    'our-programs' => [
        'title' => 'Our Programs',
        'icon' => 'fas fa-project-diagram',
        'active' => ($activeSection === 'our-programs')
    ],
    'our-team' => [
        'title' => 'Our Team',
        'icon' => 'fas fa-user-tie',
        'active' => ($activeSection === 'our-team')
    ],
    'history' => [
        'title' => 'Organisation History',
        'icon' => 'fas fa-history',
        'active' => ($activeSection === 'history')
    ]
];

// Fetch team members from database (only active ones)
if ($activeSection === 'our-team') {
    $stmt = $pdo->prepare("
        SELECT * FROM admin_team 
        WHERE status = 'active'
        ORDER BY display_order, department, name
    ");
    $stmt->execute();
    $team_members = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Sokatoto Muda Initiative Trust</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="about.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'topbars.php'; ?>
    
    <!-- About Hero Section -->
    <section class="about-hero">
        <div class="about-hero-content">
            <h1>About Sokatoto Muda Initiative Trust</h1>
            <p>Empowering communities, transforming lives, and building a brighter future for children and teen mothers in informal settlements.</p>
        </div>
    </section>

    <!-- Main About Content -->
    <main class="about-container">
        <!-- About Navigation Sidebar -->
        <div class="about-sidebar">
            <div class="sidebar-header">
                <h2>About STMI</h2>
                <p>Explore our organization</p>
            </div>
            
            <nav class="about-nav">
                <?php foreach ($aboutSections as $sectionId => $section): 
                    $isActive = $section['active'] ? 'active' : '';
                ?>
                    <a href="about.php?section=<?php echo $sectionId; ?>" 
                       class="about-nav-item <?php echo $isActive; ?>">
                        <div class="nav-icon">
                            <i class="<?php echo $section['icon']; ?>"></i>
                        </div>
                        <div class="nav-text">
                            <h3><?php echo $section['title']; ?></h3>
                        </div>
                        <div class="nav-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                <?php endforeach; ?>
            </nav>
            
            <!-- Quick Contact Box -->
            <div class="sidebar-contact">
                <h3>Quick Contact</h3>
                <p><i class="fas fa-phone"></i> +254 728 274304</p>
                <p><i class="fas fa-envelope"></i> stmitrust@gmail.com</p>
                <a href="contact.php" class="contact-btn">
                    <i class="fas fa-envelope"></i> Send Message
                </a>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="about-content">
            <!-- Who We Are Section -->
            <div class="who-we-are-section" style="display: <?php echo $activeSection === 'who-we-are' ? 'block' : 'none'; ?>;">
                <!-- Main Title -->
                <div class="section-title-main">
                    <div class="section-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="section-title">
                        <h1>Who We Are</h1>
                        <p>Learn more about our organization</p>
                    </div>
                </div>

                <!-- Two Column Layout -->
                <div class="two-column-layout">
                   <!-- Column 1: Two Pictures stacked vertically -->
                    <div class="column column-1">
                        <!-- First Image - Children Focus -->
                        <div class="image-hover-container">
                            <div class="main-image">
                                <img src="images/children-engagement.jpg" alt="SMIT Children Engagement Activities" class="featured-image">
                                <div class="image-overlay">
                                    <div class="overlay-content">
                                        <i class="fas fa-futbol"></i>
                                        <h3>SOKA TOTO</h3>
                                        <p>Sports program for children's talent development</p>
                                    </div>
                                </div>
                            </div>
                            <div class="image-caption">
                                <p><strong>SOKA TOTO Program:</strong> Nurturing children's talents through sports and physical activities</p>
                            </div>
                        </div>
                        
                        <!-- Second Image - Young Mothers Focus -->
                        <div class="image-hover-container">
                            <div class="main-image">
                                <img src="images/young-mothers.jpg" alt="SMIT Young Mothers Empowerment" class="featured-image">
                                <div class="image-overlay">
                                    <div class="overlay-content">
                                        <i class="fas fa-paint-brush"></i>
                                        <h3>MUDA Program</h3>
                                        <p>Creative arts and skills for young mothers</p>
                                    </div>
                                </div>
                            </div>
                            <div class="image-caption">
                                <p><strong>MUDA Program:</strong> Empowering young mothers through creative arts and life skills training</p>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Content -->
                    <div class="column column-2">
                        <!-- Title -->
                        <h2 class="column-title">
                            We are Christ-centered, non-profit making organization Empowering children and young mothers through faith.
                        </h2>

                        <!-- Founder's Statement -->
                        <div class="founder-statement">
                            <div class="quote-icon">
                                <i class="fas fa-quote-left"></i>
                            </div>
                            <blockquote>
                                "By touching a child's heart, we not only transform a community but also create a future filled with hope, opportunity, and empowered generations who will make a lasting impact."
                            </blockquote>
                            <div class="founder-info">
                                <div class="founder-name">Brian Nathan,</div>
                                <div class="founder-title">Founder</div>
                            </div>
                            <div class="separator-line"></div>
                        </div>

                        <!-- Main Text -->
                        <div class="main-text">
                            <p>
                                We are a Christian founded, non-profit making organization. We reach out to vulnerable and talented children through Sports (SOKA TOTO), Creative Arts (MUDA), Mentorship, Discipleship and Outreaches, Life skills, empowerment and psycho-Social Support to Young Mothers.
                            </p>
                            
                            <p>
                                We believe that by touching the heart of a child, we have impacted the community at large. Moreover, by supporting the young mothers, their children will have wide range of opportunities when they grow up because of empowerment.
                            </p>
                            
                            <p>
                                We therefore ensure that no child is left out/behind by exposing them to various activities which suitably fits their abilities. This will enable them become more disciplined, God fearing, cultivate critical thinking, problem solving and finally be holistically self reliant citizens in the society.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Core Values Section -->
            <div class="core-values-section" style="display: <?php echo $activeSection === 'core-values' ? 'block' : 'none'; ?>;">
                <!-- Section Header -->
                <div class="section-title-main">
                    <div class="section-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="section-title">
                        <h1>Our Core Values</h1>
                        <p>The guiding principles that shape everything we do</p>
                    </div>
                </div>

                <!-- Introduction -->
                <div class="core-values-intro">
                    <p>At Sokatoto Muda Initiative Trust, our core values are the foundation of our work. They guide our decisions, shape our programs, and define our relationships with the children, young mothers, and communities we serve.</p>
                </div>

                <!-- Core Values Grid - 2x2 Layout -->
                <div class="core-values-grid">
                    <!-- Row 1 -->
                    <div class="value-row">
                        <!-- Value 1: Holistic Care -->
                        <div class="value-card">
                            <div class="value-icon">
                                <i class="fas fa-hand-holding-heart"></i>
                            </div>
                            <div class="value-content">
                                <h3 class="value-title">Holistic Care</h3>
                                <p class="value-description">Nurturing the body, mind and spirit of every child and young mother we serve.</p>
                                <div class="value-details">
                                    <ul>
                                        <li>Physical health and wellness programs</li>
                                        <li>Mental and emotional support systems</li>
                                        <li>Spiritual growth and development</li>
                                        <li>Comprehensive well-being approach</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="value-number">01</div>
                        </div>

                        <!-- Value 2: Integrity -->
                        <div class="value-card">
                            <div class="value-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="value-content">
                                <h3 class="value-title">Integrity</h3>
                                <p class="value-description">Encouraging strong moral character both on and off the field in all our interactions.</p>
                                <div class="value-details">
                                    <ul>
                                        <li>Honesty and transparency in all dealings</li>
                                        <li>Accountability to our beneficiaries</li>
                                        <li>Ethical leadership and governance</li>
                                        <li>Consistent moral principles</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="value-number">02</div>
                        </div>
                    </div>

                    <!-- Row 2 -->
                    <div class="value-row">
                        <!-- Value 3: Faith -->
                        <div class="value-card">
                            <div class="value-icon">
                                <i class="fas fa-church"></i>
                            </div>
                            <div class="value-content">
                                <h3 class="value-title">Faith</h3>
                                <p class="value-description">Building trust in God as a foundation of life for spiritual growth and resilience.</p>
                                <div class="value-details">
                                    <ul>
                                        <li>Christ-centered approach</li>
                                        <li>Prayer and spiritual guidance</li>
                                        <li>Biblical principles in programming</li>
                                        <li>Faith-based mentorship</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="value-number">03</div>
                        </div>

                        <!-- Value 4: Empowerment -->
                        <div class="value-card">
                            <div class="value-icon">
                                <i class="fas fa-users-cog"></i>
                            </div>
                            <div class="value-content">
                                <h3 class="value-title">Empowerment</h3>
                                <p class="value-description">Equipping children with skills, confidence and leadership for self-reliance.</p>
                                <div class="value-details">
                                    <ul>
                                        <li>Skill development programs</li>
                                        <li>Confidence building activities</li>
                                        <li>Leadership training</li>
                                        <li>Sustainable empowerment</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="value-number">04</div>
                        </div>
                    </div>
                </div>

                <!-- Core Values Summary -->
                <div class="values-summary">
                    <div class="summary-box">
                        <div class="summary-icon">
                            <i class="fas fa-star-and-crescent"></i>
                        </div>
                        <div class="summary-content">
                            <h3>Living Our Values</h3>
                            <p>These four core values are not just words on paper - they are lived experiences in our daily work. They shape how we engage with communities, design our programs, and measure our impact.</p>
                            <p>Through Holistic Care, Integrity, Faith, and Empowerment, we create sustainable transformation in the lives of children and young mothers.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Our Programs Section -->
            <div class="our-programs-section" style="display: <?php echo $activeSection === 'our-programs' ? 'block' : 'none'; ?>;">
                <!-- Section Header -->
                <div class="section-title-main">
                    <div class="section-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <div class="section-title">
                        <h1>Our Programs</h1>
                        <p>Comprehensive initiatives transforming lives through targeted interventions</p>
                    </div>
                </div>

                <!-- Introduction -->
                <div class="programs-intro">
                    <p>Our holistic approach addresses both immediate needs and long-term development through seven key programs. Each program is designed to complement the others, creating a comprehensive support system for children and young mothers.</p>
                </div>

                <!-- Programs Grid - 3 Columns -->
                <div class="programs-grid">
                    <!-- Program 1: Sports -->
                    <div class="program-card">
                        <div class="program-card-inner">
                            <div class="program-header">
                                <div class="program-icon">
                                    <i class="fas fa-futbol"></i>
                                </div>
                                <div class="program-number">01</div>
                            </div>
                            <h3 class="program-title">Sports Program</h3>
                            <p class="program-description">We nurture children's physical and mental well-being through football and other sports, focusing on teamwork, discipline, and personal growth.</p>
                            <div class="program-details">
                                <h4>Key Focus Areas:</h4>
                                <ul>
                                    <li>Football training and competitions</li>
                                    <li>Team building activities</li>
                                    <li>Physical fitness development</li>
                                    <li>Sportsmanship and discipline</li>
                                    <li>Mental toughness training</li>
                                </ul>
                            </div>
                            <div class="program-tag">
                                <span class="tag-icon"><i class="fas fa-running"></i></span>
                                <span>SOKA TOTO Initiative</span>
                            </div>
                        </div>
                    </div>

                    <!-- Program 2: Creative Arts -->
                    <div class="program-card">
                        <div class="program-card-inner">
                            <div class="program-header">
                                <div class="program-icon">
                                    <i class="fas fa-paint-brush"></i>
                                </div>
                                <div class="program-number">02</div>
                            </div>
                            <h3 class="program-title">Creative Arts</h3>
                            <p class="program-description">We offer opportunities in music, dance, elocution, and instrument learning to help children express themselves creatively and explore their artistic talents.</p>
                            <div class="program-details">
                                <h4>Artistic Disciplines:</h4>
                                <ul>
                                    <li>Music and vocal training</li>
                                    <li>Traditional and modern dance</li>
                                    <li>Public speaking and elocution</li>
                                    <li>Instrument learning</li>
                                    <li>Visual arts and crafts</li>
                                </ul>
                            </div>
                            <div class="program-tag">
                                <span class="tag-icon"><i class="fas fa-palette"></i></span>
                                <span>MUDA Initiative</span>
                            </div>
                        </div>
                    </div>

                    <!-- Program 3: Teen Mothers Program -->
                    <div class="program-card">
                        <div class="program-card-inner">
                            <div class="program-header">
                                <div class="program-icon">
                                    <i class="fas fa-hands-helping"></i>
                                </div>
                                <div class="program-number">03</div>
                            </div>
                            <h3 class="program-title">Teen Mothers Program</h3>
                            <p class="program-description">We empower teen mothers to overcome challenges such as stigma and financial instability by providing financial training, vocational skills, mentorship, and psychosocial support.</p>
                            <div class="program-details">
                                <h4>Support Services:</h4>
                                <ul>
                                    <li>Financial literacy training</li>
                                    <li>Vocational skills development</li>
                                    <li>One-on-one mentorship</li>
                                    <li>Psychosocial counseling</li>
                                    <li>Parenting skills workshops</li>
                                </ul>
                            </div>
                            <div class="program-tag">
                                <span class="tag-icon"><i class="fas fa-heart"></i></span>
                                <span>Holistic Support</span>
                            </div>
                        </div>
                    </div>

                    <!-- Program 4: Outreach and Discipleship -->
                    <div class="program-card">
                        <div class="program-card-inner">
                            <div class="program-header">
                                <div class="program-icon">
                                    <i class="fas fa-bible"></i>
                                </div>
                                <div class="program-number">04</div>
                            </div>
                            <h3 class="program-title">Outreach and Discipleship</h3>
                            <p class="program-description">Through Bible stories for younger children and Bible study for older ones, we foster spiritual growth and a strong sense of moral values.</p>
                            <div class="program-details">
                                <h4>Spiritual Activities:</h4>
                                <ul>
                                    <li>Children's Bible stories</li>
                                    <li>Youth Bible study groups</li>
                                    <li>Spiritual mentorship</li>
                                    <li>Values formation workshops</li>
                                    <li>Community outreach</li>
                                </ul>
                            </div>
                            <div class="program-tag">
                                <span class="tag-icon"><i class="fas fa-church"></i></span>
                                <span>Spiritual Growth</span>
                            </div>
                        </div>
                    </div>

                    <!-- Program 5: Digital Literacy -->
                    <div class="program-card">
                        <div class="program-card-inner">
                            <div class="program-header">
                                <div class="program-icon">
                                    <i class="fas fa-laptop-code"></i>
                                </div>
                                <div class="program-number">05</div>
                            </div>
                            <h3 class="program-title">Digital Literacy</h3>
                            <p class="program-description">We equip children and teen mothers with essential digital skills to bridge the technology gap and prepare them for future opportunities.</p>
                            <div class="program-details">
                                <h4>Digital Skills:</h4>
                                <ul>
                                    <li>Basic computer operations</li>
                                    <li>Internet and email skills</li>
                                    <li>Digital safety and ethics</li>
                                    <li>Online research methods</li>
                                    <li>Introduction to coding</li>
                                </ul>
                            </div>
                            <div class="program-tag">
                                <span class="tag-icon"><i class="fas fa-mobile-alt"></i></span>
                                <span>21st Century Skills</span>
                            </div>
                        </div>
                    </div>

                    <!-- Program 6: Mentorship and Life Skills -->
                    <div class="program-card">
                        <div class="program-card-inner">
                            <div class="program-header">
                                <div class="program-icon">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div class="program-number">06</div>
                            </div>
                            <h3 class="program-title">Mentorship and Life Skills</h3>
                            <p class="program-description">We guide children and teens to make informed life choices by offering mentorship sessions and practical life skills training.</p>
                            <div class="program-details">
                                <h4>Development Areas:</h4>
                                <ul>
                                    <li>One-on-one mentorship</li>
                                    <li>Decision-making skills</li>
                                    <li>Communication skills</li>
                                    <li>Conflict resolution</li>
                                    <li>Goal setting and planning</li>
                                </ul>
                            </div>
                            <div class="program-tag">
                                <span class="tag-icon"><i class="fas fa-comments"></i></span>
                                <span>Personal Development</span>
                            </div>
                        </div>
                    </div>

                    <!-- Program 7: Career Talks -->
                    <div class="program-card">
                        <div class="program-card-inner">
                            <div class="program-header">
                                <div class="program-icon">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                                <div class="program-number">07</div>
                            </div>
                            <h3 class="program-title">Career Talks</h3>
                            <p class="program-description">We expose children to various career paths and opportunities, inspiring them to pursue their dreams with confidence and clarity.</p>
                            <div class="program-details">
                                <h4>Career Exposure:</h4>
                                <ul>
                                    <li>Industry professional talks</li>
                                    <li>Career exploration sessions</li>
                                    <li>Educational pathway guidance</li>
                                    <li>Success story sharing</li>
                                    <li>Future planning workshops</li>
                                </ul>
                            </div>
                            <div class="program-tag">
                                <span class="tag-icon"><i class="fas fa-chart-line"></i></span>
                                <span>Future Readiness</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Programs Impact Summary -->
                <div class="programs-impact">
                    <div class="impact-header">
                        <h2>Our Program Impact</h2>
                        <p>Creating lasting change through comprehensive programming</p>
                    </div>
                    <div class="impact-stats">
                        <div class="impact-stat">
                            <div class="stat-number">7</div>
                            <div class="stat-label">Integrated Programs</div>
                        </div>
                        <div class="impact-stat">
                            <div class="stat-number">2</div>
                            <div class="stat-label">Target Groups</div>
                        </div>
                        <div class="impact-stat">
                            <div class="stat-number">4</div>
                            <div class="stat-label">Key Focus Areas</div>
                        </div>
                        <div class="impact-stat">
                            <div class="stat-number">100%</div>
                            <div class="stat-label">Holistic Approach</div>
                        </div>
                    </div>
                    <div class="impact-note">
                        <p><i class="fas fa-quote-left"></i> Our programs work together to address physical, mental, spiritual, and social development needs, ensuring comprehensive growth for every beneficiary. <i class="fas fa-quote-right"></i></p>
                    </div>
                </div>
            </div>

            <!-- Our Team Section -->
            <div class="our-team-section" style="display: <?php echo $activeSection === 'our-team' ? 'block' : 'none'; ?>;">
                <!-- Section Header -->
                <div class="section-title-main">
                    <div class="section-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="section-title">
                        <h1>Our Team</h1>
                        <p>Meet the dedicated individuals driving our mission forward</p>
                    </div>
                </div>

                <!-- Introduction -->
                <div class="team-intro">
                    <p>Our team comprises passionate professionals, volunteers, and community leaders committed to transforming lives. Each member brings unique skills and experiences that contribute to our holistic approach to child and youth development.</p>
                </div>

                <!-- Department Filter -->
                <?php if (!empty($team_members)): ?>
                <div class="team-departments-filter">
                    <button class="dept-filter-btn active" data-department="all">All Departments</button>
                    <?php 
                    // Get unique departments
                    $departments = [];
                    foreach ($team_members as $member) {
                        $departments[$member['department']] = ucfirst(str_replace('_', ' ', $member['department']));
                    }
                    foreach ($departments as $dept => $dept_name): ?>
                        <button class="dept-filter-btn" data-department="<?php echo $dept; ?>">
                            <?php echo $dept_name; ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Team Grid - Dynamic from Database -->
                <div class="team-grid-dynamic" id="teamContainer">
                    <?php if (empty($team_members)): ?>
                        <div class="no-team-members">
                            <i class="fas fa-users"></i>
                            <h3>No Team Members Available</h3>
                            <p>Our team information is currently being updated. Please check back soon to meet our amazing team.</p>
                            <a href="contact.php" class="cta-button">
                                <i class="fas fa-envelope"></i> Contact Us
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($team_members as $member): 
                            // Get social links
                            $social_links = $member['social_links'] ? json_decode($member['social_links'], true) : [];
                            
                            // Generate initials for placeholder
                            $initials = '';
                            $name_parts = explode(' ', $member['name']);
                            if (count($name_parts) >= 2) {
                                $initials = strtoupper(substr($name_parts[0], 0, 1) . substr($name_parts[1], 0, 1));
                            } else {
                                $initials = strtoupper(substr($member['name'], 0, 2));
                            }
                        ?>
                            <div class="team-member-dynamic" data-department="<?php echo $member['department']; ?>">
                                <div class="member-photo-dynamic">
                                    <?php if ($member['photo']): ?>
                                        <img src="<?php echo htmlspecialchars($member['photo']); ?>" 
                                             alt="<?php echo htmlspecialchars($member['name']); ?>"
                                             onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'initials-placeholder\'>' + '<?php echo $initials; ?>' + '</div>';">
                                    <?php else: ?>
                                        <div class="initials-placeholder">
                                            <?php echo $initials; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="photo-overlay-dynamic">
                                        <?php if (!empty($social_links['linkedin']) || !empty($social_links['twitter']) || !empty($social_links['facebook'])): ?>
                                            <div class="social-links-dynamic">
                                                <?php if (!empty($social_links['linkedin'])): ?>
                                                    <a href="<?php echo htmlspecialchars($social_links['linkedin']); ?>" 
                                                       target="_blank" 
                                                       class="social-link-dynamic" 
                                                       title="LinkedIn">
                                                        <i class="fab fa-linkedin"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!empty($social_links['twitter'])): ?>
                                                    <a href="<?php echo htmlspecialchars($social_links['twitter']); ?>" 
                                                       target="_blank" 
                                                       class="social-link-dynamic" 
                                                       title="Twitter">
                                                        <i class="fab fa-twitter"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!empty($social_links['facebook'])): ?>
                                                    <a href="<?php echo htmlspecialchars($social_links['facebook']); ?>" 
                                                       target="_blank" 
                                                       class="social-link-dynamic" 
                                                       title="Facebook">
                                                        <i class="fab fa-facebook"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="social-links-dynamic">
                                                <span style="color: white; font-size: 0.9rem;">No social links</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="member-info-dynamic">
                                    <h3 class="member-name-dynamic"><?php echo htmlspecialchars($member['name']); ?></h3>
                                    <p class="member-position-dynamic"><?php echo htmlspecialchars($member['position']); ?></p>
                                    
                                    <div class="member-department-dynamic department-<?php echo $member['department']; ?>">
                                        <i class="fas fa-users"></i>
                                        <?php echo ucfirst(str_replace('_', ' ', $member['department'])); ?>
                                    </div>
                                    
                                    <?php if (!empty($member['bio'])): ?>
                                        <p class="member-bio-dynamic">
                                            <?php echo htmlspecialchars(substr($member['bio'], 0, 120)); ?>
                                            <?php if (strlen($member['bio']) > 120): ?>...<?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="member-contact-dynamic">
                                        <?php if (!empty($member['email'])): ?>
                                            <p>
                                                <i class="fas fa-envelope"></i>
                                                <?php echo htmlspecialchars($member['email']); ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($member['phone'])): ?>
                                            <p>
                                                <i class="fas fa-phone"></i>
                                                <?php echo htmlspecialchars($member['phone']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Team Note for Backend -->
                <div class="team-backend-note">
                    <div class="note-content">
                        <i class="fas fa-database"></i>
                        <div>
                            <h4>Team Management System</h4>
                            <p>This section is connected to our backend system. Administrators can add, update, or remove team members through the admin panel. Each team member's information is dynamically loaded from our database.</p>
                        </div>
                    </div>
                </div>

                <!-- Volunteer Section -->
                <div class="volunteer-section">
                    <div class="volunteer-content">
                        <div class="volunteer-text">
                            <h3>Join Our Team</h3>
                            <p>We're always looking for passionate individuals to join our mission. Whether as staff, volunteers, or partners, your skills can make a difference.</p>
                            <a href="contact.php" class="volunteer-btn">
                                <i class="fas fa-hand-paper"></i>
                                Become a Volunteer
                            </a>
                        </div>
                        <div class="volunteer-image">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Organisation History Section -->
            <div class="history-section" style="display: <?php echo $activeSection === 'history' ? 'block' : 'none'; ?>;">
                <!-- Section Header -->
                <div class="section-title-main">
                    <div class="section-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="section-title">
                        <h1>Organisation History</h1>
                        <p>Our journey of impact, growth, and transformation since inception</p>
                    </div>
                </div>

                <!-- Hero Image -->
                <div class="history-hero">
                    <div class="hero-image-container">
                        <img src="images/history-hero.jpg" alt="Sokatoto Muda Initiative Trust History" class="hero-image">
                        <div class="hero-overlay">
                            <div class="hero-text">
                                <h2>Our Journey Since 2020</h2>
                                <p>From a vision to a movement transforming lives</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History Content -->
                <div class="history-content">
                    <!-- Introduction -->
                    <div class="history-intro">
                        <p>Sokatoto Muda Initiative Trust was born out of a deep passion to address the challenges faced by children and teen mothers in informal settlements. What started as a small community initiative has grown into a comprehensive organization impacting thousands of lives.</p>
                    </div>

                    <!-- Timeline Section -->
                    <div class="history-timeline">
                        <h2 class="timeline-title">Our Milestones & Growth Journey</h2>
                        
                        <div class="timeline">
                            <!-- Timeline Item 1 -->
                            <div class="timeline-item">
                                <div class="timeline-year">2020</div>
                                <div class="timeline-content">
                                    <h3>The Vision Takes Root</h3>
                                    <p>Brian Nathan, driven by his personal experiences and faith, begins informal sports sessions with children in Nairobi's informal settlements. The initial focus was on using football (SOKA) to keep children engaged and away from negative influences.</p>
                                    <div class="timeline-highlight">
                                        <i class="fas fa-seedling"></i>
                                        <span>Started with 15 children in one community</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline Item 2 -->
                            <div class="timeline-item">
                                <div class="timeline-year">2021</div>
                                <div class="timeline-content">
                                    <h3>Formal Registration & Expansion</h3>
                                    <p>Sokatoto Muda Initiative Trust is officially registered as a community-based organization. The program expands to include creative arts (MUDA) and basic mentorship programs. First partnerships with local churches and schools established.</p>
                                    <div class="timeline-highlight">
                                        <i class="fas fa-certificate"></i>
                                        <span>Officially registered as a CBO</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline Item 3 -->
                            <div class="timeline-item">
                                <div class="timeline-year">2022</div>
                                <div class="timeline-content">
                                    <h3>Teen Mothers Program Launch</h3>
                                    <p>Recognizing the interconnected needs of children and their young mothers, we launch the Teen Mothers Empowerment Program. This holistic approach includes skills training, psychosocial support, and parenting workshops.</p>
                                    <div class="timeline-highlight">
                                        <i class="fas fa-hands-helping"></i>
                                        <span>First cohort of 25 teen mothers enrolled</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline Item 4 -->
                            <div class="timeline-item">
                                <div class="timeline-year">2023</div>
                                <div class="timeline-content">
                                    <h3>Strategic Partnerships & Growth</h3>
                                    <p>Formal partnerships established with educational institutions, corporate organizations, and international NGOs. Programs expand to include digital literacy, career guidance, and comprehensive discipleship programs.</p>
                                    <div class="timeline-highlight">
                                        <i class="fas fa-handshake"></i>
                                        <span>Partnerships with 5 major organizations</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline Item 5 -->
                            <div class="timeline-item">
                                <div class="timeline-year">2024</div>
                                <div class="timeline-content">
                                    <h3>Digital Transformation & Scaling</h3>
                                    <p>Implementation of digital learning platforms and expansion to new communities. Development of structured mentorship frameworks and launch of the comprehensive 7-program model serving over 500 beneficiaries.</p>
                                    <div class="timeline-highlight">
                                        <i class="fas fa-laptop"></i>
                                        <span>Digital literacy program reaches 200+ beneficiaries</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Impact Statistics -->
                    <div class="history-impact">
                        <h2 class="impact-title">Our Impact Over the Years</h2>
                        <div class="impact-grid">
                            <div class="impact-stat">
                                <div class="stat-icon">
                                    <i class="fas fa-child"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-number">500+</div>
                                    <div class="stat-label">Children Supported</div>
                                </div>
                            </div>
                            <div class="impact-stat">
                                <div class="stat-icon">
                                    <i class="fas fa-female"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-number">150+</div>
                                    <div class="stat-label">Teen Mothers Empowered</div>
                                </div>
                            </div>
                            <div class="impact-stat">
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-number">10+</div>
                                    <div class="stat-label">Communities Reached</div>
                                </div>
                            </div>
                            <div class="impact-stat">
                                <div class="stat-icon">
                                    <i class="fas fa-handshake"></i>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-number">15+</div>
                                    <div class="stat-label">Partnerships Formed</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Partnerships & Collaboration Section -->
                    <div class="partnerships-section">
                        <div class="partnerships-header">
                            <h2>Partnerships & Collaborations</h2>
                            <p>Building bridges for greater impact through strategic alliances</p>
                        </div>

                        <div class="partnerships-content">
                            <div class="partnerships-intro">
                                <p>Our growth and impact have been significantly amplified through meaningful partnerships with various organizations that share our vision for transforming lives. These collaborations enable us to pool resources, share expertise, and reach more beneficiaries effectively.</p>
                            </div>

                            <div class="partnership-categories">
                                <!-- Category 1: Faith-Based -->
                                <div class="category-card">
                                    <div class="category-icon">
                                        <i class="fas fa-church"></i>
                                    </div>
                                    <div class="category-content">
                                        <h3>Faith-Based Organizations</h3>
                                        <p>Collaborating with churches and religious institutions for spiritual development programs, outreach events, and community mobilization.</p>
                                        <ul>
                                            <li>Local church partnerships for discipleship</li>
                                            <li>Joint community outreach programs</li>
                                            <li>Spiritual mentorship networks</li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Category 2: Educational -->
                                <div class="category-card">
                                    <div class="category-icon">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <div class="category-content">
                                        <h3>Educational Institutions</h3>
                                        <p>Partnering with schools, colleges, and training institutions to enhance educational opportunities and skills development.</p>
                                        <ul>
                                            <li>School-based talent development programs</li>
                                            <li>Vocational training collaborations</li>
                                            <li>Career guidance partnerships</li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Category 3: Corporate -->
                                <div class="category-card">
                                    <div class="category-icon">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                    <div class="category-content">
                                        <h3>Corporate Partners</h3>
                                        <p>Engaging with businesses for CSR initiatives, skills transfer, employment opportunities, and resource support.</p>
                                        <ul>
                                            <li>CSR funding and support</li>
                                            <li>Employee volunteer programs</li>
                                            <li>Skills development workshops</li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Category 4: NGO/Development -->
                                <div class="category-card">
                                    <div class="category-icon">
                                        <i class="fas fa-hands-helping"></i>
                                    </div>
                                    <div class="category-content">
                                        <h3>NGOs & Development Partners</h3>
                                        <p>Working with local and international NGOs to implement development programs and share best practices.</p>
                                        <ul>
                                            <li>Program implementation partnerships</li>
                                            <li>Capacity building collaborations</li>
                                            <li>Research and development initiatives</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Partnership Call to Action -->
                            <div class="partnership-cta">
                                <div class="cta-content">
                                    <h3>Partner With Us</h3>
                                    <p>Are you interested in collaborating with us to create lasting impact? We welcome partnerships with organizations that share our vision for empowering children and young mothers.</p>
                                    <a href="contact.php" class="cta-btn">
                                        <i class="fas fa-handshake"></i>
                                        Explore Partnership Opportunities
                                    </a>
                                </div>
                                <div class="cta-icon">
                                    <i class="fas fa-network-wired"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Footer -->
            <div class="content-footer">
                <div class="prev-next-nav">
                    <?php
                    // Find previous and next sections
                    $sectionKeys = array_keys($aboutSections);
                    $currentIndex = array_search($activeSection, $sectionKeys);
                    $prevIndex = ($currentIndex > 0) ? $currentIndex - 1 : null;
                    $nextIndex = ($currentIndex < count($sectionKeys) - 1) ? $currentIndex + 1 : null;
                    ?>
                    
                    <?php if ($prevIndex !== null): 
                        $prevSection = $sectionKeys[$prevIndex];
                    ?>
                        <a href="about.php?section=<?php echo $prevSection; ?>" class="nav-btn prev-btn">
                            <i class="fas fa-arrow-left"></i>
                            <span>Previous: <?php echo $aboutSections[$prevSection]['title']; ?></span>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($nextIndex !== null): 
                        $nextSection = $sectionKeys[$nextIndex];
                    ?>
                        <a href="about.php?section=<?php echo $nextSection; ?>" class="nav-btn next-btn">
                            <span>Next: <?php echo $aboutSections[$nextSection]['title']; ?></span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>

    <script>
    // Team Department Filtering
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('.dept-filter-btn');
        const teamMembers = document.querySelectorAll('.team-member-dynamic');
        
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                const department = this.getAttribute('data-department');
                
                // Filter team members
                teamMembers.forEach(member => {
                    if (department === 'all' || member.getAttribute('data-department') === department) {
                        member.style.display = 'block';
                        setTimeout(() => {
                            member.style.opacity = '1';
                            member.style.transform = 'translateY(0)';
                        }, 10);
                    } else {
                        member.style.opacity = '0';
                        member.style.transform = 'translateY(20px)';
                        setTimeout(() => {
                            member.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });
        
        // Initialize team member animations
        teamMembers.forEach((member, index) => {
            member.style.opacity = '0';
            member.style.transform = 'translateY(20px)';
            member.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            
            setTimeout(() => {
                member.style.opacity = '1';
                member.style.transform = 'translateY(0)';
            }, index * 100);
        });
        
        // Image error handling
        const teamImages = document.querySelectorAll('.member-photo-dynamic img');
        teamImages.forEach(img => {
            img.addEventListener('error', function() {
                const parent = this.parentElement;
                const name = this.alt;
                const nameParts = name.split(' ');
                let initials = '';
                
                if (nameParts.length >= 2) {
                    initials = nameParts[0].charAt(0) + nameParts[1].charAt(0);
                } else if (nameParts.length === 1) {
                    initials = nameParts[0].substring(0, 2);
                }
                
                initials = initials.toUpperCase();
                
                const placeholder = document.createElement('div');
                placeholder.className = 'initials-placeholder';
                placeholder.textContent = initials;
                placeholder.style.cssText = 'width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #0e0c5e 0%, #ff9d0b 100%); color: white; font-size: 3rem; font-weight: bold;';
                
                parent.innerHTML = '';
                parent.appendChild(placeholder);
            });
        });
    });
    </script>
</body>
</html>