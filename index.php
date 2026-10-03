<?php 
session_start(); 

// Check cookie acceptance on the PHP side as well.
$cookie_accepted = isset($_COOKIE['foodfusion_cookie_accepted']) ? true : false;
?>

<!DOCTYPE html>
<html>
<head>
    <title>FoodFusion</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

<?php if(isset($_GET['error']) || isset($_GET['success'])): ?>
    <?php 
        $is_success = isset($_GET['success']);
        $msg_title = $is_success ? "FoodFusion Success" : "FoodFusion Alert";
        $msg_content = $is_success ? $_GET['success'] : $_GET['error'];
        $icon_class = $is_success ? "fa-solid fa-circle-check" : "fa-solid fa-triangle-exclamation";
        $bg_color = $is_success ? "#C6F6D5" : "#FFF5F5"; 
        $border_color = $is_success ? "#38A169" : "#E53E3E";
    ?>
    <div id="tgNotification" class="tg-notification" style="background-color: <?php echo $bg_color; ?>; border-left: 5px solid <?php echo $border_color; ?>;">
        <div class="tg-notification-icon" style="color: <?php echo $border_color; ?>;">
            <i class="<?php echo $icon_class; ?>"></i>
        </div>
        <div class="tg-notification-content">
            <div class="tg-notification-title" style="color: <?php echo $border_color; ?>;"><?php echo $msg_title; ?></div>
            <div><?php echo htmlspecialchars($msg_content); ?></div>
        </div>
        <button class="tg-notification-close" onclick="closeTgNotification()">&times;</button>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const notif = document.getElementById('tgNotification');
            
            setTimeout(() => {
                notif.classList.add('show');
            }, 100);

            setTimeout(() => {
                notif.classList.remove('show');
            }, 5000);
        });

        function closeTgNotification() {
            document.getElementById('tgNotification').classList.remove('show');
        }
    </script>
<?php endif; ?>

<nav class="home-nav">
    <a href="index.php" class="brand-logo">FoodFusion</a>

    <button class="mobile-nav-toggle" type="button" aria-expanded="false" aria-controls="homeNavLinks" aria-label="Open navigation menu">
        <i class="fa-solid fa-bars" aria-hidden="true"></i>
    </button>
    
    <div class="nav-links" id="homeNavLinks">
        <!-- Guest Users (Not Logged In) -->
        <?php if(!isset($_SESSION['user_id'])): ?>
            <a href="index.php" class="active-nav">Home</a>
            <a href="about.php">About Us</a>
            <a href="recipes.php">Recipe Collection</a>
            <a href="contact.php">Contact Us</a>
        <?php endif; ?>

        <!-- Member Role -->
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'member'): ?>
            <a href="index.php" class="active-nav">Home</a>
            <a href="about.php">About Us</a>
            <a href="recipes.php">Recipe Collection</a>
            <a href="community_cookbook.php">Community Cookbook</a>
            <a href="culinary_resources.php">Culinary Resources</a>
            <a href="educational_resources.php">Educational Resources</a>
            <a href="contact.php">Contact Us</a>
        <?php endif; ?>

        <!-- Admin Role -->
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="manage_community.php">Manage Community</a>

            <div class="dropdown">
                <a href="#">Manage Resources ▼</a>
                <div class="dropdown-content">
                    <a href="admin_recipes.php">Recipes Collection</a>
                    <a href="admin_resources.php">Resources</a>
                </div>
            </div>

            <a href="admin_contact.php">Contact Us</a>
        <?php endif; ?>
    </div>

    <!-- User Auth Area -->
    <span class="user-area">
        <?php if(isset($_SESSION['user_id'])): ?>
            Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? $_SESSION['user']); ?>
            <a href="logout.php" class="cta-btn">Logout</a>
        <?php else: ?>
            <button onclick="showLogin()">Login</button>
            <button onclick="showRegister()">Join Us</button>
        <?php endif; ?>
    </span>
</nav>

<div class="hero">
    <h1>Welcome to FoodFusion</h1>
    <p>
        FoodFusion is a cooking platform where users can explore recipes,
        join the community, and learn culinary skills.
    </p>
</div>

<div class="main-container">
    <main>
        <h3 class="section-title">Featured Culinary Trends</h3>
        <div class="news-feed">
            <div class="recipe-card">
                <img src="https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&w=500&q=80" alt="Pizza">
                <div class="recipe-card-content">
                    <h4>Classic Artisan Pizza</h4>
                    <p style="color: var(--text-muted); font-size: 14px;">Master the dynamic art of sourdough baking at home.</p>
                </div>
            </div>
            <div class="recipe-card">
                <img src="https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?auto=format&fit=crop&w=500&q=80" alt="Salad">
                <div class="recipe-card-content">
                    <h4>Mediterranean Avocado Bowl</h4>
                    <p style="color: var(--text-muted); font-size: 14px;">Fresh greens, crisp dressings, organic nutrition.</p>
                </div>
            </div>
        </div>
    </main>

    <aside class="sidebar-box homepage-events">
        <h4 class="section-title">Upcoming Events</h4>
        <div class="event-carousel" aria-roledescription="carousel" aria-label="Upcoming cooking events">
            <div class="event-carousel-track" aria-live="polite">
                <div class="event-item" role="group" aria-roledescription="slide" aria-label="1 of 4">
                    <h5>Autumn Harvest Cooking Class</h5>
                    <p style="font-size: 12px; color: var(--text-muted);"><i class="fa-regular fa-calendar"></i> October 18, 2026</p>
                </div>
                <div class="event-item" role="group" aria-roledescription="slide" aria-label="2 of 4">
                    <h5>International Street Food Night</h5>
                    <p style="font-size: 12px; color: var(--text-muted);"><i class="fa-regular fa-calendar"></i> October 31, 2026</p>
                </div>
                <div class="event-item" role="group" aria-roledescription="slide" aria-label="3 of 4">
                    <h5>Holiday Baking Workshop</h5>
                    <p style="font-size: 12px; color: var(--text-muted);"><i class="fa-regular fa-calendar"></i> November 14, 2026</p>
                </div>
                <div class="event-item" role="group" aria-roledescription="slide" aria-label="4 of 4">
                    <h5>Fresh Pasta Masterclass</h5>
                    <p style="font-size: 12px; color: var(--text-muted);"><i class="fa-regular fa-calendar"></i> December 05, 2026</p>
                </div>
            </div>
            <div class="event-carousel-controls">
                <button type="button" class="event-carousel-button" data-event-direction="previous" aria-label="Previous event"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
                <span class="event-carousel-status" aria-live="polite">1 / 4</span>
                <button type="button" class="event-carousel-button" data-event-direction="next" aria-label="Next event"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
            </div>
        </div>
    </aside>
</div>

<div id="registerForm" class="popup">
    <h2>Join Us</h2>
    <form action="register.php" method="POST">
        <input type="text" name="first_name" placeholder="First Name" required><br><br>
        <input type="text" name="last_name" placeholder="Last Name" required><br><br>
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit">Register</button>
    </form>
    <br>
    <button onclick="closeAll()">Close</button>
</div>

<div id="loginForm" class="popup">
    <h2>Login</h2>
    <form action="login.php" method="POST">
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit">Login</button>
    </form>
    <p class="forgotPasswordLink"><a href="forgot_password.php">Forgot your password?</a></p>
    <br>
    <button onclick="closeAll()">Close</button>
</div>

<!-- Cookie Banner -->
<div class="cookie-banner" id="cookieBanner" style="display: <?php echo $cookie_accepted ? 'none' : 'flex'; ?>;">
    <p style="font-size: 14px;">We use cookies to improve your user experience. 
        <a href="cookie_policy.php" style="color: var(--accent-color); text-decoration: none;">Cookie Policy</a>
    </p>
    <button onclick="acceptCookies()">Accept</button>
</div>

<footer>
    <div class="social-links" style="margin-bottom: 15px;">
        <a href="https://facebook.com" target="_blank"><i class="fa-brands fa-facebook"></i></a>
        <a href="https://instagram.com" target="_blank"><i class="fa-brands fa-instagram"></i></a>
        <a href="https://pinterest.com" target="_blank"><i class="fa-brands fa-pinterest"></i></a>
        <a href="https://twitter.com" target="_blank"><i class="fa-brands fa-twitter"></i></a>
    </div>
    <div class="footer-links">
        <a href="privacy.php">Privacy Policy</a> | <a href="cookie_policy.php">Cookie Policy</a>
    </div>
    <p style="margin-top: 15px; font-size: 12px; color: #A0AEC0;">&copy; 2026 FoodFusion. All Rights Reserved.</p>
</footer>

<script>
function showRegister() {
    document.getElementById("registerForm").style.display = "block";
    document.getElementById("loginForm").style.display = "none";
}
function showLogin() {
    document.getElementById("loginForm").style.display = "block";
    document.getElementById("registerForm").style.display = "none";
}

// Keep the login form open after a failed login redirect.
const loginUrlParams = new URLSearchParams(window.location.search);
if (loginUrlParams.get("show_login") === "1") {
    showLogin();
}

function closeAll() {
    document.getElementById("registerForm").style.display = "none";
    document.getElementById("loginForm").style.display = "none";
}

function acceptCookies() {
    document.cookie = "foodfusion_cookie_accepted=true; max-age=" + 60*60*24*30 + "; path=/";
    document.getElementById('cookieBanner').style.display = 'none';
}

const mobileNavToggle = document.querySelector('.mobile-nav-toggle');
const homeNavLinks = document.getElementById('homeNavLinks');
mobileNavToggle.addEventListener('click', () => {
    const isOpen = mobileNavToggle.getAttribute('aria-expanded') === 'true';
    mobileNavToggle.setAttribute('aria-expanded', String(!isOpen));
    mobileNavToggle.setAttribute('aria-label', isOpen ? 'Open navigation menu' : 'Close navigation menu');
    homeNavLinks.classList.toggle('is-open', !isOpen);
});
homeNavLinks.addEventListener('click', (event) => {
    if (event.target.closest('a')) {
        mobileNavToggle.setAttribute('aria-expanded', 'false');
        mobileNavToggle.setAttribute('aria-label', 'Open navigation menu');
        homeNavLinks.classList.remove('is-open');
    }
});

const eventCarousel = document.querySelector('.event-carousel');
const eventSlides = Array.from(eventCarousel.querySelectorAll('.event-item'));
const eventTrack = eventCarousel.querySelector('.event-carousel-track');
const eventStatus = eventCarousel.querySelector('.event-carousel-status');
let currentEvent = 0;
let eventTimer;

function showEvent(index) {
    currentEvent = (index + eventSlides.length) % eventSlides.length;
    eventTrack.style.transform = `translateX(-${currentEvent * 100}%)`;
    eventStatus.textContent = `${currentEvent + 1} / ${eventSlides.length}`;
    eventSlides.forEach((slide, slideIndex) => {
        slide.setAttribute('aria-hidden', String(slideIndex !== currentEvent));
    });
}

function startEventRotation() {
    window.clearInterval(eventTimer);
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        eventTimer = window.setInterval(() => showEvent(currentEvent + 1), 5000);
    }
}

eventCarousel.querySelectorAll('[data-event-direction]').forEach((button) => {
    button.addEventListener('click', () => {
        showEvent(currentEvent + (button.dataset.eventDirection === 'next' ? 1 : -1));
        startEventRotation();
    });
});
eventCarousel.addEventListener('mouseenter', () => window.clearInterval(eventTimer));
eventCarousel.addEventListener('mouseleave', startEventRotation);
eventCarousel.addEventListener('focusin', () => window.clearInterval(eventTimer));
eventCarousel.addEventListener('focusout', (event) => {
    if (!eventCarousel.contains(event.relatedTarget)) startEventRotation();
});
showEvent(0);
startEventRotation();
</script>

</body>
</html>
