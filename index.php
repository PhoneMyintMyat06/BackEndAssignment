<?php 
session_start(); 

// Cookie လက်ခံမှုကို PHP ဘက်မှပါ စစ်ဆေးခြင်း
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

<nav>
    <a href="index.php" class="brand-logo">FoodFusion</a>
    
    <div class="nav-links">
        <!-- Guest Users (Not Logged In) -->
        <?php if(!isset($_SESSION['user_id'])): ?>
            <a href="index.php">Home</a>
            <a href="about.php">About Us</a>
            <a href="recipes.php">Recipe Collection</a>
            <a href="contact.php">Contact Us</a>
        <?php endif; ?>

        <!-- Member Role -->
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'member'): ?>
            <a href="index.php">Home</a>
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
            <a href="manage_cookbook.php">Community Cookbook</a>

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

    <aside class="sidebar-box">
        <h4 class="section-title">Upcoming Events</h4>
        <div class="event-item">
            <h5>Summer Pastry Workshop</h5>
            <p style="font-size: 12px; color: var(--text-muted);"><i class="fa-regular fa-calendar"></i> July 25, 2026</p>
        </div>
        <div class="event-item">
            <h5>Artisan Pasta Making</h5>
            <p style="font-size: 12px; color: var(--text-muted);"><i class="fa-regular fa-calendar"></i> August 02, 2026</p>
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
</script>

</body>
</html>
