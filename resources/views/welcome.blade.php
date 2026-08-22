{{-- resources/views/welcome.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LumiNUs - NU Lipa Alumni Management System</title>

    <link rel="stylesheet" href="/css/landing_modern.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- =========================================
     NAVIGATION
     ========================================= -->
<nav class="navbar">
    <div class="nav-container">
        <div class="nav-logo">
            <img src="/assets/logos/NULIPA_AAO_White.png" alt="LumiNUs Logo" class="nav-logo-img">
        </div>
        <div class="nav-links">
            <a href="#features">Features</a>
            <a href="#about">About</a>
            <a href="#download">Download</a>
            <a href="#contact">Contact</a>
            <a href="{{ route('admin.login') }}" class="btn-nav-login">
                <i class="fas fa-sign-in-alt"></i> Admin Login
            </a>
        </div>
        <button class="nav-toggle" aria-label="Toggle navigation menu">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</nav>

<!-- =========================================
     HERO SECTION
     ========================================= -->
<section class="hero">
    <div class="hero-overlay"></div>
    <img src="/assets/NULipa_Building.jpg" alt="NU Lipa Building" class="hero-bg">
    
    <div class="hero-content">
        <img src="/assets/logos/LumiNUs_Logo_Landscape_White.png" alt="LumiNUs Logo" class="hero-logo">
        <h1 class="hero-title">Stay Connected,<br>Wherever You Are</h1>
        <p class="hero-subtitle">
            The official mobile app for <strong>NU Lipa Alumni</strong>. 
            Stay updated, network with fellow alumni, and unlock exclusive perks 
            — all from the palm of your hand.
        </p>
        <div class="hero-buttons">
            <a href="#download" class="btn-primary">
                <i class="fas fa-download"></i> Download App
            </a>
            <a href="#features" class="btn-secondary">
                <i class="fas fa-chevron-down"></i> Explore Features
            </a>
        </div>
        <div class="hero-stats">
            <div class="stat-item">
                <span class="stat-number" data-count="5000">0</span>
                <span class="stat-label">Alumni</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-number" data-count="50">0</span>
                <span class="stat-label">Events</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-number" data-count="25">0</span>
                <span class="stat-label">Years</span>
            </div>
        </div>
    </div>
</section>

<!-- =========================================
     FEATURES SECTION - MOBILE APP
     ========================================= -->
<section id="features" class="features">
    <div class="section-header">
        <span class="section-tag">Mobile App Features</span>
        <h2 class="section-title">Everything You Need in One App</h2>
        <p class="section-subtitle">Designed exclusively for NU Lipa alumni to stay connected and engaged</p>
    </div>

    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon" style="background: rgba(50, 65, 140, 0.1); color: var(--primary-blue);">
                <i class="fas fa-newspaper"></i>
            </div>
            <h3>Announcements</h3>
            <p>Stay updated with the latest news, events, and important announcements from the Alumni Affairs Office.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon" style="background: rgba(251, 209, 23, 0.15); color: var(--primary-yellow);">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <h3>Events</h3>
            <p>Discover and register for alumni events, reunions, and university activities right from your phone.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon" style="background: rgba(50, 65, 140, 0.1); color: var(--primary-blue);">
                <i class="fas fa-gifts"></i>
            </div>
            <h3>Perks & Discounts</h3>
            <p>Unlock exclusive perks, discounts, and benefits from partner establishments as a NU Lipa alumni.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon" style="background: rgba(251, 209, 23, 0.15); color: var(--primary-yellow);">
                <i class="fas fa-comments"></i>
            </div>
            <h3>Messaging</h3>
            <p>Connect with fellow alumni and the Alumni Affairs Office through secure in-app messaging.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon" style="background: rgba(50, 65, 140, 0.1); color: var(--primary-blue);">
                <i class="fas fa-users"></i>
            </div>
            <h3>Community Feed</h3>
            <p>Share updates, photos, and memories with the NU Lipa alumni community in a vibrant social feed.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon" style="background: rgba(251, 209, 23, 0.15); color: var(--primary-yellow);">
                <i class="fas fa-user-friends"></i>
            </div>
            <h3>Alumni Networking</h3>
            <p>Build your professional network by connecting with alumni across different industries and batches.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon" style="background: rgba(50, 65, 140, 0.1); color: var(--primary-blue);">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <h3>Mentorship Opportunities</h3>
            <p>Find mentors, offer guidance, and participate in programs that foster professional growth.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon" style="background: rgba(251, 209, 23, 0.2); color: var(--primary-yellow);">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <h3>Alumni Tracer</h3>
            <p>Complete your alumni profile and help us track the success of NU Lipa graduates through our tracer system.</p>
            {{-- <span class="feature-badge">Most Important</span> --}}
        </div>
    </div>
</section>

<!-- =========================================
     DOWNLOAD SECTION - APP PROMO WITH SCREENSHOT
     ========================================= -->
<section id="download" class="download">
    <!-- Interactive Background -->
    <div class="download-bg">
        <div class="bg-orbs">
            <div class="orb orb-1"></div>
            <div class="orb orb-2"></div>
            <div class="orb orb-3"></div>
            <div class="orb orb-4"></div>
        </div>
        <div class="bg-particles" id="particles"></div>
    </div>
    
    <div class="download-container">
        <div class="download-content">
            <span class="section-tag">Download Now</span>
            <h2>Get the Mobile App!</h2>
            <p class="download-description">
                Available for both iOS and Android devices. Stay connected with your alma mater wherever life takes you.
            </p>
            
            <div class="download-buttons">
                <a href="#" class="download-btn" style="pointer-events: none; opacity: 0.7;">
                    <i class="fab fa-apple"></i>
                    <div class="download-btn-text">
                        <span>Coming Soon</span>
                        <strong>App Store</strong>
                    </div>
                </a>
                <a href="#" class="download-btn" style="pointer-events: none; opacity: 0.7;">
                    <i class="fab fa-google-play"></i>
                    <div class="download-btn-text">
                        <span>Coming Soon</span>
                        <strong>Google Play</strong>
                    </div>
                </a>
            </div>
            
            <div class="download-note">
                <i class="fas fa-info-circle"></i>
                <p>Stay tuned! The LumiNUs mobile app is currently in development and will be available soon.</p>
            </div>
        </div>
        
        <div class="download-mockup">
            <div class="mockup-phone">
                <!-- Phone Frame with Screenshot -->
                <div class="mockup-screen">
                    <img src="/assets/screenshot.png" alt="LumiNUs App Screenshot" class="mockup-screenshot">
                </div>
                <!-- Phone Details -->
                <div class="mockup-notch"></div>
                <div class="mockup-buttons">
                    <div class="mockup-side-btn"></div>
                    <div class="mockup-side-btn volume-up"></div>
                    <div class="mockup-side-btn volume-down"></div>
                </div>
                <div class="mockup-home-indicator"></div>
            </div>
            
            <!-- Floating Elements around the phone -->
            <div class="floating-float float-1">
                <i class="fas fa-star"></i>
            </div>
            <div class="floating-float float-2">
                <i class="fas fa-heart"></i>
            </div>
            <div class="floating-float float-3">
                <i class="fas fa-bolt"></i>
            </div>
            <div class="floating-float float-4">
                <i class="fas fa-circle-check"></i>
            </div>
        </div>
    </div>
</section>

<!-- =========================================
     ABOUT / MISSION SECTION
     ========================================= -->
<section id="about" class="about">
    <div class="about-container">
        <div class="about-image">
            <img src="/assets/NULipa_Building.jpg" alt="NU Lipa Campus">
            <div class="about-image-overlay">
                <span>NU Lipa</span>
            </div>
        </div>
        <div class="about-content">
            <span class="section-tag">About LumiNUs</span>
            <h2>Empowering Alumni Connections</h2>
            <p>
                <strong>LumiNUs</strong> is the official alumni mobile application of <strong>National University - Lipa</strong>, 
                developed to strengthen the bond between the university and its graduates.
            </p>
            <p>
                Managed by the <strong>NU Lipa Alumni Affairs Office (AAO)</strong>, this platform serves as a central hub 
                for alumni engagement, event coordination, and community building — accessible anytime, anywhere.
            </p>
            <div class="about-mission">
                <div class="mission-item">
                    <i class="fas fa-bullseye" style="color: var(--primary-yellow);"></i>
                    <div>
                        <h4>Our Mission</h4>
                        <p>To foster lifelong connections between NU Lipa and its alumni through meaningful engagement.</p>
                    </div>
                </div>
                <div class="mission-item">
                    <i class="fas fa-eye" style="color: var(--primary-yellow);"></i>
                    <div>
                        <h4>Our Vision</h4>
                        <p>A vibrant, globally-connected alumni community that supports the university's growth and excellence.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =========================================
     CONTACT SECTION
     ========================================= -->
<section id="contact" class="contact">
    <div class="section-header">
        <span class="section-tag">Contact</span>
        <h2 class="section-title">Get in Touch</h2>
        <p class="section-subtitle">Reach out to the NU Lipa Alumni Affairs Office</p>
    </div>

    <div class="contact-grid">
        <div class="contact-card">
            <div class="contact-icon">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <h4>Visit Us</h4>
            <p>National University - Lipa<br>J.P. Laurel Highway,<br>Mataas Na Lupa, Lipa City, Batangas</p>
        </div>

        <div class="contact-card">
            <div class="contact-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <h4>Email Us</h4>
            <p><a href="mailto:alumniaffairs@nu-lipa.edu.ph">alumniaffairs@nu-lipa.edu.ph</a></p>
        </div>

        <div class="contact-card">
            <div class="contact-icon">
                <i class="fas fa-phone-alt"></i>
            </div>
            <h4>Call Us</h4>
            <p>(043) 756-1234</p>
        </div>
    </div>
</section>

<!-- =========================================
     FOOTER
     ========================================= -->
<footer class="footer">
    <div class="footer-container">
        <div class="footer-brand">
            <img src="/assets/logos/Footer_LumiNUsLipa.png" alt="LumiNUs Logo" class="footer-logo">
            <p>NU Lipa Alumni Management System</p>
        </div>
        <div class="footer-links">
            <a href="#features">Features</a>
            <a href="#about">About</a>
            <a href="#download">Download</a>
            <a href="#contact">Contact</a>
            <a href="{{ route('admin.login') }}">Admin Login</a>
        </div>
        <div class="footer-social">
            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; {{ date('Y') }} LumiNUs - National University Lipa. All rights reserved.</p>
    </div>
</footer>

<div id="luminus-scrollbar"></div>

<!-- =========================================
     JAVASCRIPT
     ========================================= -->
<script>
// =========================================
// MAIN INITIALIZATION
// =========================================
document.addEventListener('DOMContentLoaded', function() {
    
    // =========================================
    // MOBILE NAVIGATION TOGGLE
    // =========================================
    const toggleBtn = document.querySelector('.nav-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (toggleBtn && navLinks) {
        toggleBtn.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-bars');
                icon.classList.toggle('fa-times');
            }
        });
    }

    // Close menu when clicking a link
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            navLinks.classList.remove('active');
            const icon = toggleBtn?.querySelector('i');
            if (icon) {
                icon.classList.add('fa-bars');
                icon.classList.remove('fa-times');
            }
        });
    });

    // =========================================
    // ANIMATED COUNTER
    // =========================================
    const animateCounter = (element) => {
        const target = parseInt(element.getAttribute('data-count'));
        const duration = 2000;
        const startTime = performance.now();
        
        const updateCounter = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(eased * target);
            element.textContent = current + '+';
            
            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            } else {
                element.textContent = target + '+';
            }
        };
        
        requestAnimationFrame(updateCounter);
    };

    // Intersection Observer for stats
    const stats = document.querySelectorAll('.stat-number');
    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                statsObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    stats.forEach(stat => statsObserver.observe(stat));

    // =========================================
    // SMOOTH SCROLL FOR ANCHOR LINKS
    // =========================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // =========================================
    // NAVBAR SCROLL EFFECT
    // =========================================
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

// =========================================
    // AUTO-HIDING SCROLLBAR (CUSTOM)
    // =========================================
    function initScrollbar() {
        let scrollTimeout;
        const body = document.body;
        const scrollThumb = document.getElementById('luminus-scrollbar');
        
        // Add class and move thumb when scrolling
        window.addEventListener('scroll', () => {
            body.classList.add('scrolling');
            
            // Move the custom thumb
            if (scrollThumb) {
                // 1. Calculate how far down the page we are (0.0 to 1.0)
                const scrollPercent = window.scrollY / (document.documentElement.scrollHeight - window.innerHeight);
                
                // 2. Calculate how tall the thumb should be based on page length (minimum 40px)
                const thumbHeight = Math.max((window.innerHeight / document.documentElement.scrollHeight) * window.innerHeight, 40);
                
                // 3. Apply the height and move it down the Y axis
                scrollThumb.style.height = `${thumbHeight}px`;
                scrollThumb.style.transform = `translateY(${scrollPercent * (window.innerHeight - thumbHeight)}px)`;
            }
            
            // Clear the timeout
            clearTimeout(scrollTimeout);
            
            // Remove class after scrolling stops
            scrollTimeout = setTimeout(() => {
                body.classList.remove('scrolling');
            }, 1500);
        });
        
        // Show scrollbar when hovering near the right edge
        document.addEventListener('mousemove', (e) => {
            const windowWidth = window.innerWidth;
            const isNearEdge = e.clientX > windowWidth - 30;
            
            if (isNearEdge) {
                body.classList.add('scrolling');
                
                clearTimeout(scrollTimeout);
                
                scrollTimeout = setTimeout(() => {
                    body.classList.remove('scrolling');
                }, 2000);
            }
        });
    }

    initScrollbar();

    // =========================================
    // INTERACTIVE PARTICLES
    // =========================================
    function createParticles() {
        const container = document.getElementById('particles');
        if (!container) return;
        
        const particleCount = 50;
        
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.classList.add('particle');
            
            const size = Math.random() * 4 + 2;
            particle.style.width = size + 'px';
            particle.style.height = size + 'px';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDuration = (Math.random() * 20 + 10) + 's';
            particle.style.animationDelay = (Math.random() * 20) + 's';
            particle.style.opacity = Math.random() * 0.5 + 0.1;
            
            container.appendChild(particle);
        }
    }

    createParticles();

    // =========================================
    // PARALLAX ON MOUSE MOVE
    // =========================================
    function initParallax() {
        const downloadSection = document.querySelector('.download');
        if (!downloadSection) return;
        
        let isTouching = false;
        
        document.addEventListener('mousemove', (e) => {
            if (isTouching) return;
            
            const rect = downloadSection.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;
            
            const orbs = document.querySelectorAll('.orb');
            orbs.forEach((orb, index) => {
                const speed = (index + 1) * 0.5;
                const moveX = x * 30 * speed;
                const moveY = y * 30 * speed;
                orb.style.transform = `translate(${moveX}px, ${moveY}px) scale(${1 + Math.abs(x) * 0.1})`;
            });
            
            const phone = document.querySelector('.mockup-phone');
            if (phone) {
                const rotateX = y * -5;
                const rotateY = x * 5;
                phone.style.transform = `perspective(800px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
            }
        });
        
        document.addEventListener('touchstart', () => {
            isTouching = true;
        });
        
        document.addEventListener('touchend', () => {
            isTouching = false;
        });
    }

    initParallax();

    // =========================================
    // SCROLL REVEAL ANIMATIONS
    // =========================================
    function initScrollReveal() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('section').forEach(section => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(40px)';
            section.style.transition = 'all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1)';
            observer.observe(section);
        });
    }

    initScrollReveal();

    // =========================================
    // FEATURE CARDS 3D HOVER EFFECT
    // =========================================
    function initFeatureCards() {
        document.querySelectorAll('.feature-card').forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width - 0.5;
                const y = (e.clientY - rect.top) / rect.height - 0.5;
                
                card.style.transform = `perspective(600px) rotateX(${y * -8}deg) rotateY(${x * 8}deg) translateY(-8px)`;
                card.style.transition = 'none';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
                card.style.transition = 'all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
            });
        });
    }

    initFeatureCards();

});


</script>

</body>
</html>