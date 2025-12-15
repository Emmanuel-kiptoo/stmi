<?php
// Get active section from URL or default to first
$activeSection = isset($_GET['section']) ? $_GET['section'] : 'articles';

// Define media sections
$mediaSections = [
    'articles' => [
        'title' => 'Articles',
        'icon' => 'fas fa-newspaper',
        'active' => ($activeSection === 'articles')
    ],
    'newsletters' => [
        'title' => 'Newsletters',
        'icon' => 'fas fa-envelope-open-text',
        'active' => ($activeSection === 'newsletters')
    ],
    'resources' => [
        'title' => 'Resources',
        'icon' => 'fas fa-book',
        'active' => ($activeSection === 'resources')
    ],
    'gallery' => [
        'title' => 'Gallery',
        'icon' => 'fas fa-images',
        'active' => ($activeSection === 'gallery')
    ],
    'reports' => [
        'title' => 'Reports',
        'icon' => 'fas fa-chart-bar',
        'active' => ($activeSection === 'reports')
    ]
];

// Define gallery data ONLY for gallery section
if ($activeSection === 'gallery') {
    $galleryPhotos = [
        'sports' => [
            'title' => 'Sports & Recreation',
            'photos' => [
                [
                    'image' => 'images/gallery/sports1.jpg',
                    'date' => 'Dec 10, 2023',
                    'title' => 'Annual Sports Day 2023',
                    'description' => 'Children from our SOKA TOTO program participating in football matches and athletic competitions.'
                ],
                [
                    'image' => 'images/gallery/sports2.jpg',
                    'date' => 'Nov 22, 2023',
                    'title' => 'Football Training Session',
                    'description' => 'Professional coaches training children on basic football techniques.'
                ],
                [
                    'image' => 'images/gallery/sports3.jpg',
                    'date' => 'Dec 10, 2023',
                    'title' => 'Champions Celebration',
                    'description' => 'Celebrating the winning team with medals and trophies.'
                ]
            ]
        ],
        'training' => [
            'title' => 'Skills & Training',
            'photos' => [
                [
                    'image' => 'images/gallery/training1.jpg',
                    'date' => 'Nov 15, 2023',
                    'title' => 'Tailoring Skills Workshop',
                    'description' => 'Teen mothers learning tailoring and fashion design skills.'
                ],
                [
                    'image' => 'images/gallery/training2.jpg',
                    'date' => 'Oct 28, 2023',
                    'title' => 'Digital Literacy Class',
                    'description' => 'Children learning basic computer skills in our digital literacy program.'
                ],
                [
                    'image' => 'images/gallery/training3.jpg',
                    'date' => 'Nov 8, 2023',
                    'title' => 'Creative Arts Training',
                    'description' => 'Traditional beadwork and craft training sessions.'
                ]
            ]
        ],
        'events' => [
            'title' => 'Community Events',
            'photos' => [
                [
                    'image' => 'images/gallery/event1.jpg',
                    'date' => 'Oct 14, 2023',
                    'title' => 'Community Health Camp',
                    'description' => 'Free medical checkups and health education for children and families.'
                ],
                [
                    'image' => 'images/gallery/event2.jpg',
                    'date' => 'Sep 30, 2023',
                    'title' => 'Food Security Program',
                    'description' => 'Distribution of nutritious food packages to families in need.'
                ],
                [
                    'image' => 'images/gallery/event3.jpg',
                    'date' => 'Nov 5, 2023',
                    'title' => 'Environmental Conservation Day',
                    'description' => 'Children participating in tree planting activities.'
                ]
            ]
        ]
    ];

    $galleryVideos = [
        'highlights' => [
            'title' => 'Program Highlights',
            'videos' => [
                [
                    'video' => 'videos/sports-day-highlights.mp4',
                    'poster' => 'images/gallery/video1-thumb.jpg',
                    'title' => 'Annual Sports Day 2023 Highlights',
                    'description' => 'Experience the energy and excitement of our biggest sports event of the year!',
                    'duration' => '5:24 min',
                    'date' => 'December 10, 2023',
                    'views' => '1,245'
                ],
                [
                    'video' => 'videos/muda-program-journey.mp4',
                    'poster' => 'images/gallery/video2-thumb.jpg',
                    'title' => 'MUDA Program Success Stories',
                    'description' => 'Follow the transformative journey of three teen mothers from our MUDA program.',
                    'duration' => '8:15 min',
                    'date' => 'November 20, 2023',
                    'views' => '892'
                ]
            ]
        ],
        'documentaries' => [
            'title' => 'Documentaries',
            'videos' => [
                [
                    'video' => 'videos/digital-literacy-impact.mp4',
                    'poster' => 'images/gallery/video3-thumb.jpg',
                    'title' => 'Bridging the Digital Divide',
                    'description' => 'This documentary explores how our digital literacy program is transforming lives.',
                    'duration' => '12:45 min',
                    'date' => 'October 15, 2023',
                    'views' => '2,134'
                ]
            ]
        ],
        'testimonials' => [
            'title' => 'Testimonials',
            'videos' => [
                [
                    'video' => 'videos/parent-testimonials.mp4',
                    'poster' => 'images/gallery/video4-thumb.jpg',
                    'title' => 'Parent & Guardian Testimonials',
                    'description' => 'Hear directly from parents about the positive changes in their children.',
                    'duration' => '6:30 min',
                    'date' => 'September 28, 2023',
                    'views' => '1,567'
                ]
            ]
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media - Sokatoto Muda Initiative Trust</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="navigation.css">
    <link rel="stylesheet" href="media.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php if ($activeSection === 'gallery'): ?>
    <link rel="stylesheet" href="gallery.css">
    <?php endif; ?>
    <style>
        /* Show only active section */
        .media-section {
            display: none;
        }
        
        .media-section.active {
            display: block;
        }
    </style>
</head>
<body>
    <?php include 'topbars.php'; ?>
    
    <!-- Media Hero Section -->
    <section class="media-hero">
        <div class="media-hero-content">
            <h1>Media Center</h1>
            <p>Explore our articles, newsletters, resources, gallery, and reports</p>
        </div>
    </section>

    <!-- Main Media Content -->
    <main class="media-container">
        <!-- Media Navigation Sidebar -->
        <div class="media-sidebar">
            <div class="sidebar-header">
                <h2>Media Library</h2>
                <p>Browse our content</p>
            </div>
            
            <nav class="media-nav">
                <?php foreach ($mediaSections as $sectionId => $section): 
                    $isActive = $section['active'] ? 'active' : '';
                ?>
                    <a href="media.php?section=<?php echo $sectionId; ?>" 
                       class="media-nav-item <?php echo $isActive; ?>">
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
            
            <!-- Latest Updates -->
            <div class="sidebar-updates">
                <h3>Latest Updates</h3>
                <div class="update-item">
                    <div class="update-date">
                        <span class="day">15</span>
                        <span class="month">Dec</span>
                    </div>
                    <div class="update-content">
                        <h4>Annual Report 2023</h4>
                        <p>Our latest impact report now available</p>
                    </div>
                </div>
                <div class="update-item">
                    <div class="update-date">
                        <span class="day">10</span>
                        <span class="month">Dec</span>
                    </div>
                    <div class="update-content">
                        <h4>New Photo Gallery</h4>
                        <p>Photos from our recent sports day</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="media-content">
            <!-- Articles Section -->
            <div class="media-section articles-section <?php echo $mediaSections['articles']['active'] ? 'active' : ''; ?>">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="section-title">
                        <h1>Articles</h1>
                        <p>Read our latest articles and stories</p>
                    </div>
                </div>

                <div class="articles-grid">
                    <div class="article-card">
                        <div class="article-image">
                            <img src="images/media/article1.jpg" alt="Empowering Young Mothers">
                            <div class="article-category">Featured</div>
                        </div>
                        <div class="article-content">
                            <h3>Empowering Young Mothers Through Skills Training</h3>
                            <div class="article-meta">
                                <span><i class="far fa-calendar"></i> December 15, 2023</span>
                                <span><i class="far fa-user"></i> Brian Nathan</span>
                            </div>
                            <p>How our MUDA program is transforming the lives of teen mothers through vocational training and psychosocial support...</p>
                            <a href="#" class="read-more">Read Full Article <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>

                    <div class="article-card">
                        <div class="article-image">
                            <img src="images/media/article2.jpg" alt="Sports Program Impact">
                            <div class="article-category">Sports</div>
                        </div>
                        <div class="article-content">
                            <h3>The Impact of Sports on Child Development</h3>
                            <div class="article-meta">
                                <span><i class="far fa-calendar"></i> December 10, 2023</span>
                                <span><i class="far fa-user"></i> David Omondi</span>
                            </div>
                            <p>Exploring how our SOKA TOTO program uses football and other sports to build character, discipline, and teamwork...</p>
                            <a href="#" class="read-more">Read Full Article <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>

                    <div class="article-card">
                        <div class="article-image">
                            <img src="images/media/article3.jpg" alt="Digital Literacy">
                            <div class="article-category">Education</div>
                        </div>
                        <div class="article-content">
                            <h3>Bridging the Digital Divide in Informal Settlements</h3>
                            <div class="article-meta">
                                <span><i class="far fa-calendar"></i> December 5, 2023</span>
                                <span><i class="far fa-user"></i> Sarah Wangui</span>
                            </div>
                            <p>Our digital literacy program equips children and young mothers with essential technology skills for the 21st century...</p>
                            <a href="#" class="read-more">Read Full Article <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Newsletters Section -->
            <div class="media-section newsletters-section <?php echo $mediaSections['newsletters']['active'] ? 'active' : ''; ?>">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                    <div class="section-title">
                        <h1>Newsletters</h1>
                        <p>Stay updated with our monthly newsletters</p>
                    </div>
                </div>

                <div class="newsletters-list">
                    <div class="newsletter-card">
                        <div class="newsletter-header">
                            <div class="newsletter-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="newsletter-info">
                                <h3>December 2023 Newsletter</h3>
                                <p>Annual Review & Christmas Special</p>
                            </div>
                        </div>
                        <div class="newsletter-details">
                            <p>Highlights from our year-end activities, Christmas celebrations, and plans for 2024.</p>
                            <a href="#" class="download-btn"><i class="fas fa-download"></i> Download PDF</a>
                        </div>
                    </div>

                    <div class="newsletter-card">
                        <div class="newsletter-header">
                            <div class="newsletter-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="newsletter-info">
                                <h3>November 2023 Newsletter</h3>
                                <p>Sports Day Success & New Partnerships</p>
                            </div>
                        </div>
                        <div class="newsletter-details">
                            <p>Coverage of our annual sports day event and new partnership announcements.</p>
                            <a href="#" class="download-btn"><i class="fas fa-download"></i> Download PDF</a>
                        </div>
                    </div>

                    <div class="newsletter-card">
                        <div class="newsletter-header">
                            <div class="newsletter-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="newsletter-info">
                                <h3>October 2023 Newsletter</h3>
                                <p>Digital Literacy Launch & Teen Mothers Workshop</p>
                            </div>
                        </div>
                        <div class="newsletter-details">
                            <p>Launch of our digital literacy program and successful teen mothers empowerment workshop.</p>
                            <a href="#" class="download-btn"><i class="fas fa-download"></i> Download PDF</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resources Section -->
            <div class="media-section resources-section <?php echo $mediaSections['resources']['active'] ? 'active' : ''; ?>">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="section-title">
                        <h1>Resources</h1>
                        <p>Educational materials and useful documents</p>
                    </div>
                </div>

                <div class="resources-categories">
                    <div class="category">
                        <h3>Training Materials</h3>
                        <ul class="resource-list">
                            <li>
                                <i class="fas fa-file-word"></i>
                                <div>
                                    <h4>Life Skills Training Manual</h4>
                                    <p>Comprehensive guide for life skills trainers</p>
                                    <a href="#" class="resource-link">Download <i class="fas fa-download"></i></a>
                                </div>
                            </li>
                            <li>
                                <i class="fas fa-file-powerpoint"></i>
                                <div>
                                    <h4>Sports Coaching Guide</h4>
                                    <p>Basic football coaching techniques for children</p>
                                    <a href="#" class="resource-link">Download <i class="fas fa-download"></i></a>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="category">
                        <h3>Educational Guides</h3>
                        <ul class="resource-list">
                            <li>
                                <i class="fas fa-file-pdf"></i>
                                <div>
                                    <h4>Parenting Skills Handbook</h4>
                                    <p>For teen mothers and caregivers</p>
                                    <a href="#" class="resource-link">Download <i class="fas fa-download"></i></a>
                                </div>
                            </li>
                            <li>
                                <i class="fas fa-file-alt"></i>
                                <div>
                                    <h4>Digital Literacy Basics</h4>
                                    <p>Introduction to computers and internet</p>
                                    <a href="#" class="resource-link">Download <i class="fas fa-download"></i></a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Gallery Section -->
            <div class="media-section gallery-section <?php echo $mediaSections['gallery']['active'] ? 'active' : ''; ?>">
                <?php if ($activeSection === 'gallery'): ?>
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-images"></i>
                    </div>
                    <div class="section-title">
                        <h1>Gallery</h1>
                        <p>Visual moments from our programs and events</p>
                    </div>
                </div>

                <!-- Gallery Tabs -->
                <div class="gallery-tabs">
                    <button class="gallery-tab active" data-tab="photos">
                        <i class="fas fa-camera"></i> Photo Media
                    </button>
                    <button class="gallery-tab" data-tab="videos">
                        <i class="fas fa-video"></i> Video Media
                    </button>
                </div>

                <!-- Photo Media Section -->
                <div class="gallery-content photos-content active">
                    <?php foreach ($galleryPhotos as $category => $data): ?>
                    <div class="gallery-category">
                        <h2 class="category-title"><?php echo htmlspecialchars($data['title']); ?></h2>
                        <div class="photo-gallery">
                            <?php foreach ($data['photos'] as $photo): ?>
                            <div class="photo-card">
                                <img src="<?php echo htmlspecialchars($photo['image']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>">
                                <div class="photo-date"><?php echo htmlspecialchars($photo['date']); ?></div>
                                <div class="photo-caption">
                                    <h4><?php echo htmlspecialchars($photo['title']); ?></h4>
                                    <p><?php echo htmlspecialchars($photo['description']); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Video Media Section -->
                <div class="gallery-content videos-content">
                    <?php foreach ($galleryVideos as $category => $data): ?>
                    <div class="gallery-category">
                        <h2 class="category-title"><?php echo htmlspecialchars($data['title']); ?></h2>
                        <div class="video-gallery">
                            <?php foreach ($data['videos'] as $video): ?>
                            <div class="video-card">
                                <div class="video-player">
                                    <video poster="<?php echo htmlspecialchars($video['poster']); ?>">
                                        <source src="<?php echo htmlspecialchars($video['video']); ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                    <button class="video-play-btn">
                                        <i class="fas fa-play"></i>
                                    </button>
                                </div>
                                <div class="video-content">
                                    <h3><?php echo htmlspecialchars($video['title']); ?></h3>
                                    <p class="video-description">
                                        <?php echo htmlspecialchars($video['description']); ?>
                                    </p>
                                    <div class="video-meta">
                                        <span><i class="far fa-clock"></i> <?php echo htmlspecialchars($video['duration']); ?></span>
                                        <span><i class="far fa-calendar"></i> <?php echo htmlspecialchars($video['date']); ?></span>
                                        <span><i class="fas fa-eye"></i> <?php echo htmlspecialchars($video['views']); ?> views</span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Reports Section -->
            <div class="media-section reports-section <?php echo $mediaSections['reports']['active'] ? 'active' : ''; ?>">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="section-title">
                        <h1>Reports</h1>
                        <p>Comprehensive documentation of our work, finances, and impact</p>
                    </div>
                </div>

                <!-- Reports Categories Tabs -->
                <div class="reports-tabs">
                    <button class="report-tab active" data-tab="annual">Annual Reports</button>
                    <button class="report-tab" data-tab="financial">Financial Reports</button>
                    <button class="report-tab" data-tab="mel">Monitoring & Evaluation Reports</button>
                </div>

                <!-- Annual Reports -->
                <div class="reports-category annual-reports active">
                    <h2 class="category-title">Annual Reports</h2>
                    <p class="category-description">Comprehensive overviews of our yearly activities, achievements, and impact.</p>
                    
                    <div class="reports-grid">
                        <div class="report-card">
                            <div class="report-header">
                                <div class="report-year">2023</div>
                                <div class="report-type">Annual Report</div>
                            </div>
                            <div class="report-content">
                                <h3>Annual Impact Report 2023</h3>
                                <p>Complete review of our programs, beneficiaries, achievements, challenges, and financial performance for the year 2023.</p>
                                <div class="report-stats">
                                    <div class="stat">
                                        <span class="number">500+</span>
                                        <span class="label">Children Reached</span>
                                    </div>
                                    <div class="stat">
                                        <span class="number">150+</span>
                                        <span class="label">Teen Mothers</span>
                                    </div>
                                </div>
                                <div class="report-files">
                                    <a href="reports/annual-report-2023-full.pdf" class="file-btn primary">
                                        <i class="fas fa-download"></i> Full Report (PDF, 5.2MB)
                                    </a>
                                    <a href="reports/annual-report-2023-summary.pdf" class="file-btn secondary">
                                        <i class="fas fa-file-pdf"></i> Executive Summary (PDF, 1.1MB)
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="report-card">
                            <div class="report-header">
                                <div class="report-year">2022</div>
                                <div class="report-type">Annual Report</div>
                            </div>
                            <div class="report-content">
                                <h3>Annual Impact Report 2022</h3>
                                <p>Detailed account of our growth, program expansions, partnerships, and impact metrics for 2022.</p>
                                <div class="report-stats">
                                    <div class="stat">
                                        <span class="number">350+</span>
                                        <span class="label">Children Reached</span>
                                    </div>
                                    <div class="stat">
                                        <span class="number">100+</span>
                                        <span class="label">Teen Mothers</span>
                                    </div>
                                </div>
                                <div class="report-files">
                                    <a href="reports/annual-report-2022-full.pdf" class="file-btn primary">
                                        <i class="fas fa-download"></i> Full Report (PDF, 4.8MB)
                                    </a>
                                    <a href="reports/annual-report-2022-summary.pdf" class="file-btn secondary">
                                        <i class="fas fa-file-pdf"></i> Executive Summary (PDF, 980KB)
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="report-card">
                            <div class="report-header">
                                <div class="report-year">2021</div>
                                <div class="report-type">Annual Report</div>
                            </div>
                            <div class="report-content">
                                <h3>Annual Impact Report 2021</h3>
                                <p>Documenting our establishment phase, initial programs, and first-year achievements since registration.</p>
                                <div class="report-stats">
                                    <div class="stat">
                                        <span class="number">200+</span>
                                        <span class="label">Children Reached</span>
                                    </div>
                                    <div class="stat">
                                        <span class="number">50+</span>
                                        <span class="label">Teen Mothers</span>
                                    </div>
                                </div>
                                <div class="report-files">
                                    <a href="reports/annual-report-2021.pdf" class="file-btn primary">
                                        <i class="fas fa-download"></i> Full Report (PDF, 3.5MB)
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Reports -->
                <div class="reports-category financial-reports">
                    <h2 class="category-title">Financial Reports</h2>
                    <p class="category-description">Transparent documentation of our financial performance, audits, and statements.</p>
                    
                    <div class="reports-grid">
                        <div class="report-card">
                            <div class="report-header">
                                <div class="report-year">2023</div>
                                <div class="report-type">Audited Financials</div>
                            </div>
                            <div class="report-content">
                                <h3>Audited Financial Statements 2023</h3>
                                <p>Complete audited financial statements including balance sheet, income statement, cash flow, and notes to accounts.</p>
                                <div class="report-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-calendar-check"></i>
                                        <span>Audit Date: March 15, 2024</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-user-tie"></i>
                                        <span>Auditor: XYZ Audit Firm</span>
                                    </div>
                                </div>
                                <div class="report-files">
                                    <a href="reports/financial-audit-2023-full.pdf" class="file-btn primary">
                                        <i class="fas fa-download"></i> Full Audit Report (PDF, 2.8MB)
                                    </a>
                                    <a href="reports/financial-summary-2023.pdf" class="file-btn secondary">
                                        <i class="fas fa-chart-pie"></i> Financial Summary (PDF, 850KB)
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="report-card">
                            <div class="report-header">
                                <div class="report-year">2022</div>
                                <div class="report-type">Financial Report</div>
                            </div>
                            <div class="report-content">
                                <h3>Financial Report 2022</h3>
                                <p>Detailed financial performance report including income sources, expenditure breakdown, and budget variances.</p>
                                <div class="report-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-calendar-check"></i>
                                        <span>Prepared: February 28, 2023</span>
                                    </div>
                                </div>
                                <div class="report-files">
                                    <a href="reports/financial-report-2022.pdf" class="file-btn primary">
                                        <i class="fas fa-download"></i> Full Report (PDF, 2.3MB)
                                    </a>
                                    <a href="reports/budget-2022-actual.pdf" class="file-btn secondary">
                                        <i class="fas fa-table"></i> Budget vs Actual (Excel, 450KB)
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="report-card">
                            <div class="report-header">
                                <div class="report-year">2024</div>
                                <div class="report-type">Quarterly Financials</div>
                            </div>
                            <div class="report-content">
                                <h3>Quarter 1 Financial Report 2024</h3>
                                <p>First quarter financial performance, budget utilization, and financial position as of March 31, 2024.</p>
                                <div class="report-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <span>Period: Jan - Mar 2024</span>
                                    </div>
                                </div>
                                <div class="report-files">
                                    <a href="reports/q1-financial-2024.pdf" class="file-btn primary">
                                        <i class="fas fa-download"></i> Q1 Report (PDF, 1.8MB)
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monitoring, Evaluation & Learning Reports -->
                <div class="reports-category mel-reports">
                    <h2 class="category-title">Monitoring, Evaluation & Learning Reports</h2>
                    <p class="category-description">Impact assessments, program evaluations, and learning documents to improve our work.</p>
                    
                    <div class="reports-grid">
                        <div class="report-card">
                            <div class="report-header">
                                <div class="report-year">2023</div>
                                <div class="report-type">Program Evaluation</div>
                            </div>
                            <div class="report-content">
                                <h3>SOKA TOTO Sports Program Evaluation 2023</h3>
                                <p>Comprehensive evaluation of our sports program impact on children's physical, social, and emotional development.</p>
                                <div class="report-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-chart-line"></i>
                                        <span>Evaluation Method: Mixed Methods</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-users"></i>
                                        <span>Sample Size: 150 children</span>
                                    </div>
                                </div>
                                <div class="report-files">
                                    <a href="reports/soka-toto-evaluation-2023.pdf" class="file-btn primary">
                                        <i class="fas fa-download"></i> Full Evaluation (PDF, 3.2MB)
                                    </a>
                                    <a href="reports/soka-toto-executive-summary-2023.pdf" class="file-btn secondary">
                                        <i class="fas fa-file-alt"></i> Key Findings (PDF, 750KB)
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="report-card">
                            <div class="report-header">
                                <div class="report-year">2023</div>
                                <div class="report-type">Impact Assessment</div>
                            </div>
                            <div class="report-content">
                                <h3>Teen Mothers Program Impact Assessment 2023</h3>
                                <p>Assessment of the socio-economic impact of our MUDA program on teen mothers and their children.</p>
                                <div class="report-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-balance-scale"></i>
                                        <span>Methodology: Pre-Post Assessment</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-user-friends"></i>
                                        <span>Participants: 75 teen mothers</span>
                                    </div>
                                </div>
                                <div class="report-files">
                                    <a href="reports/teen-mothers-impact-2023.pdf" class="file-btn primary">
                                        <i class="fas fa-download"></i> Full Assessment (PDF, 2.9MB)
                                    </a>
                                    <a href="reports/teen-mothers-case-studies-2023.pdf" class="file-btn secondary">
                                        <i class="fas fa-book-open"></i> Case Studies (PDF, 1.2MB)
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="report-card">
                            <div class="report-header">
                                <div class="report-year">2024</div>
                                <div class="report-type">Quarterly M&E</div>
                            </div>
                            <div class="report-content">
                                <h3>Quarterly Monitoring Report Q1 2024</h3>
                                <p>Progress tracking against targets, indicators, and milestones for all programs during first quarter 2024.</p>
                                <div class="report-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-tasks"></i>
                                        <span>Target Achievement: 85%</span>
                                    </div>
                                </div>
                                <div class="report-files">
                                    <a href="reports/q1-me-report-2024.pdf" class="file-btn primary">
                                        <i class="fas fa-download"></i> Full Report (PDF, 2.1MB)
                                    </a>
                                    <a href="reports/q1-indicators-2024.xlsx" class="file-btn secondary">
                                        <i class="fas fa-table"></i> Indicators Data (Excel, 680KB)
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reports Archive -->
                <div class="reports-archive">
                    <h3>Reports Archive</h3>
                    <div class="archive-list">
                        <a href="reports/2020-annual-report.pdf" class="archive-item">
                            <i class="fas fa-archive"></i>
                            <div class="archive-info">
                                <h4>Annual Report 2020</h4>
                                <p>Our first year of operations - Foundation year</p>
                            </div>
                            <span class="archive-size">2.1MB PDF</span>
                        </a>
                        <a href="reports/quarterly-reports-2022.zip" class="archive-item">
                            <i class="fas fa-folder"></i>
                            <div class="archive-info">
                                <h4>Quarterly Reports 2022</h4>
                                <p>Complete set of quarterly reports for 2022</p>
                            </div>
                            <span class="archive-size">4.5MB ZIP</span>
                        </a>
                        <a href="reports/digital-literacy-evaluation-2022.pdf" class="archive-item">
                            <i class="fas fa-laptop"></i>
                            <div class="archive-info">
                                <h4>Digital Literacy Evaluation 2022</h4>
                                <p>Impact evaluation of digital literacy pilot program</p>
                            </div>
                            <span class="archive-size">1.8MB PDF</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Reports Tabs Functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Get all tab buttons
            const tabButtons = document.querySelectorAll('.report-tab');
            const reportCategories = document.querySelectorAll('.reports-category');
            
            // Add click event to each tab
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Remove active class from all tabs and categories
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    reportCategories.forEach(cat => cat.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Show corresponding category
                    const categoryElement = document.querySelector(`.${tabId}-reports`);
                    if (categoryElement) {
                        categoryElement.classList.add('active');
                    }
                });
            });
            
            // Initialize first tab as active
            if (tabButtons.length > 0) {
                tabButtons[0].classList.add('active');
            }
            if (reportCategories.length > 0) {
                reportCategories[0].classList.add('active');
            }
        });
    </script>
    
    <?php if ($activeSection === 'gallery'): ?>
    <script src="gallery.js"></script>
    <?php endif; ?>
    
    <?php include 'footer.php'; ?>
</body>
</html>