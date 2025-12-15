<footer class="admin-footer">
    <div class="footer-container">
        <div class="footer-content">
            <div class="footer-section">
                <h4>Soka Toto Muda Initiative Trust</h4>
                <p>Christ-centered, non-profit making organization</p>
                <p>Empowering children and young mothers through sports, creative arts, and psychosocial support.</p>
            </div>
            
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="events.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
                    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li><a href="donations.php"><i class="fas fa-donate"></i> Donations</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>System Info</h4>
                <ul>
                    <li><i class="fas fa-server"></i> Server: <?php echo $_SERVER['SERVER_NAME']; ?></li>
                    <li><i class="fas fa-database"></i> MySQL: <?php echo DB_NAME; ?></li>
                    <li><i class="fas fa-code"></i> PHP: <?php echo phpversion(); ?></li>
                    <li><i class="fas fa-clock"></i> Time: <?php echo date('H:i:s'); ?></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Support</h4>
                <p>For technical support or questions:</p>
                <p><i class="fas fa-envelope"></i> stmitrust@gmail.com</p>
                <p><i class="fas fa-phone"></i> +254 728 274304</p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="copyright">
                &copy; <?php echo date('Y'); ?> Soka Toto Muda Initiative Trust. All rights reserved.
            </div>
            
            <div class="footer-links">
                <a href="../index.php" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Visit Website
                </a>
                <a href="settings.php">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</footer>

<style>
    .admin-footer {
        background: linear-gradient(135deg, #1a1a2e 0%, #0e0c5e 100%);
        color: white;
        padding: 30px 20px;
        margin-top: auto;
        border-top: 1px solid rgba(255,255,255,0.1);
    }
    
    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .footer-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        margin-bottom: 30px;
    }
    
    .footer-section h4 {
        color: #ff9d0b;
        margin-bottom: 15px;
        font-size: 1.1rem;
    }
    
    .footer-section p {
        color: rgba(255,255,255,0.7);
        line-height: 1.6;
        margin-bottom: 10px;
        font-size: 0.9rem;
    }
    
    .footer-section ul {
        list-style: none;
        padding: 0;
    }
    
    .footer-section ul li {
        margin-bottom: 8px;
        color: rgba(255,255,255,0.7);
        font-size: 0.9rem;
    }
    
    .footer-section ul li i {
        width: 20px;
        color: #57cc99;
    }
    
    .footer-section a {
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        transition: color 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .footer-section a:hover {
        color: white;
    }
    
    .footer-bottom {
        border-top: 1px solid rgba(255,255,255,0.1);
        padding-top: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .copyright {
        color: rgba(255,255,255,0.5);
        font-size: 0.9rem;
    }
    
    .footer-links {
        display: flex;
        gap: 20px;
    }
    
    .footer-links a {
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 5px;
        transition: color 0.3s;
    }
    
    .footer-links a:hover {
        color: #ff9d0b;
    }
    
    /* Main content wrapper to push footer down */
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    
    .main-content {
        flex: 1;
        margin-left: 250px;
        padding: 20px;
        transition: margin-left 0.3s;
    }
    
    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
        }
        
        .footer-content {
            grid-template-columns: 1fr;
        }
        
        .footer-bottom {
            flex-direction: column;
            text-align: center;
        }
        
        .footer-links {
            justify-content: center;
        }
    }
</style>

<script>
    // Update current time in footer
    function updateTime() {
        const now = new Date();
        const timeElements = document.querySelectorAll('.current-time');
        timeElements.forEach(el => {
            el.textContent = now.toLocaleTimeString();
        });
    }
    
    // Update time every second
    setInterval(updateTime, 1000);
    updateTime(); // Initial call
    
    // Smooth scroll to top
    document.querySelectorAll('.footer-links a[href="#"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    });
    
    // Toggle dark/light mode (if implemented)
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
        });
        
        // Load saved theme
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
        }
    }
</script>
</body>
</html>