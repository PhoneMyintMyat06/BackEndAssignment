<?php session_start(); 

// Check cookie acceptance on the PHP side as well.
$cookie_accepted = isset($_COOKIE['foodfusion_cookie_accepted']) ? true : false;
?>

<!DOCTYPE html>
<html>
<head>
    <title>About Us - FoodFusion</title>
    <!-- FontAwesome for Social Media & Modern Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- External CSS Connection -->
    <link rel="stylesheet" href="style.css">
</head>

<body>

<nav>
    <a href="index.php" class="brand-logo">FoodFusion</a>
    
    <div class="nav-links">
        <!-- Guest Users (Not Logged In) -->
        <?php if(!isset($_SESSION['user_id'])): ?>
            <a href="index.php">Home</a>
            <a href="about.php" class="active-nav">About Us</a>
            <a href="recipes.php">Recipe Collection</a>
            <a href="contact.php">Contact Us</a>
        <?php endif; ?>

        <!-- Member Role -->
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'member'): ?>
            <a href="index.php">Home</a>
            <a href="about.php" class="active-nav">About Us</a>
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
<!-- About Hero Section (Culinary Philosophy) -->
<div class="about-hero">
    <h1>Our Culinary Philosophy</h1>
    <p>
        At FoodFusion, we believe that home cooking is the ultimate expression of creativity and love. 
        Our platform is dedicated to promoting culinary creativity by turning every kitchen into a space 
        for experimentation, sharing, and cultural connection.
    </p>
</div>

<!-- Main Core Content Container -->
<div class="about-container">
    
    <!-- Core Values Section -->
    <h2 class="section-title">Our Core Values</h2>
    <div class="grid-3col">
        <div class="value-box">
            <i class="fa-solid fa-lightbulb"></i>
            <h3>Creativity</h3>
            <p class="value-desc">Encouraging home cooks to innovate, tweak recipes, and invent dynamic flavors.</p>
        </div>
        <div class="value-box">
            <i class="fa-solid fa-users"></i>
            <h3>Community</h3>
            <p class="value-desc">Fostering a warm, vibrant community where food enthusiasts can connect and share.</p>
        </div>
        <div class="value-box">
            <i class="fa-solid fa-seedling"></i>
            <h3>Sustainability</h3>
            <p class="value-desc">Promoting healthy home cooking habits alongside mindfulness of global resource usage.</p>
        </div>
    </div>

    <!-- The Team Section -->
    <h2 class="section-title">The Team Behind FoodFusion</h2>
    <div class="grid-3col">
        <div class="team-card">
            <img src="https://images.unsplash.com/photo-1577219491135-ce391730fb2c?auto=format&fit=crop&w=300&q=80" alt="Chef">
            <h3>Chef Alex Mercer</h3>
            <p class="team-role">Culinary Director</p>
            <p class="team-desc">Curates recipe selections and designs our instructional kitchen tutorials.</p>
        </div>
        <div class="team-card">
            <img src="https://images.unsplash.com/photo-1581092921461-eab62e97a780?auto=format&fit=crop&w=300&q=80" alt="Developer">
            <h3>Sarah Chen</h3>
            <p class="team-role">Lead Architect</p>
            <p class="team-desc">Manages platform architecture to ensure optimal resource sharing and connectivity.</p>
        </div>
        <div class="team-card">
            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80" alt="Community Manager">
            <h3>Emma Watson</h3>
            <p class="team-role">Community Manager</p>
            <p class="team-desc">Fosters engagement within the community cookbook and coordinates virtual kitchen events.</p>
        </div>
    </div>
</div>

<!-- Original Dialog Modals Layout (Maintained For Global Form Triggers)-->
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
        <a href="#"><i class="fa-brands fa-facebook"></i></a>
        <a href="#"><i class="fa-brands fa-instagram"></i></a>
        <a href="#"><i class="fa-brands fa-pinterest"></i></a>
        <a href="#"><i class="fa-brands fa-twitter"></i></a>
    </div>
    <div class="footer-links">
        <a href="#">Privacy Policy</a> | <a href="#">Cookie Policy</a>
    </div>
    <p style="margin-top: 15px; font-size: 12px; color: #A0AEC0;">&copy; 2026 FoodFusion. All Rights Reserved.</p>
</footer>

<!-- Original Script Blocks[cite: 1] -->
<script>
function showRegister() {
    document.getElementById("registerForm").style.display = "block";
    document.getElementById("loginForm").style.display = "none";
}

function showLogin() {
    document.getElementById("loginForm").style.display = "block";
    document.getElementById("registerForm").style.display = "none";
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
