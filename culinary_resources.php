<?php
session_start();
include "db.php";

// Check cookie acceptance on the PHP side as well.
$cookie_accepted = isset($_COOKIE['foodfusion_cookie_accepted']) ? true : false;

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT * FROM resources
    WHERE resource_category = 'Culinary'
    ORDER BY id DESC
");
$stmt->execute();
$resources = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Culinary Resources</title>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="style.css">
</head>

<body id="communityPageBody" class="publicResourcesPage">

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
            <a href="culinary_resources.php" class="active-nav">Culinary Resources</a>
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
    <!-- Hero Section -->
    <section class="hero community-hero-override">
        <h1>Culinary Resources</h1>
        <p>Download recipe cards, watch cooking tutorials, and learn cooking techniques and kitchen hacks.</p>
        
        <!-- Tab Navigation -->
        <div class="communityTabNav">
            <a href="culinary_resources.php" class="communityTabLink communityTabActive">
                <i class="fa-solid fa-utensils"></i> Culinary
            </a>
            <a href="educational_resources.php" class="communityTabLink">
                <i class="fa-solid fa-graduation-cap"></i> Educational
            </a>
        </div>
    </section>

    <!-- Main Container -->
    <main class="communityMainWrapper publicResourcesMain">

        <div class="publicResourceGrid">
            <?php if ($resources->num_rows === 0): ?>
                <div class="publicResourceEmpty">
                    <span><i class="fa-solid fa-utensils"></i></span>
                    <h2>No culinary resources yet</h2>
                    <p>Cooking guides, recipes, and videos will appear here when they are added.</p>
                </div>
            <?php else: ?>
                <?php while ($row = $resources->fetch_assoc()): ?>
                    <?php
                        $fileExtension = strtolower($row['file_type'] ?? pathinfo($row['file_name'], PATHINFO_EXTENSION));
                        $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true);
                    ?>
                    <article class="publicResourceCard">
                        <div class="publicResourceCardHeader">
                            <span class="publicResourceIcon"><i class="fa-solid fa-utensils"></i></span>
                            <span class="publicResourceCategory">Culinary</span>
                        </div>
                        <div class="publicResourceContent">
                            <h2><?php echo htmlspecialchars($row['title']); ?></h2>
                            <p class="publicResourceDescription"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                        </div>

                        <?php if ($fileExtension === 'mp4'): ?>
                            <div class="publicResourceVideo">
                                <video controls preload="metadata" playsinline>
                                    <source src="uploads/resources/<?php echo rawurlencode($row['file_name']); ?>" type="video/mp4">
                                    Your browser does not support HTML video.
                                </video>
                            </div>
                        <?php elseif ($isImage): ?>
                            <div class="publicResourceImage">
                                <img src="uploads/resources/<?php echo rawurlencode($row['file_name']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" loading="lazy">
                            </div>
                        <?php else: ?>
                            <div class="publicResourceFile">
                                <span class="publicResourceFileIcon"><i class="fa-solid <?php echo $fileExtension === 'pdf' ? 'fa-file-pdf' : 'fa-file-lines'; ?>"></i></span>
                                <div><small>RESOURCE FILE</small><strong><?php echo htmlspecialchars(strtoupper($fileExtension ?: 'FILE')); ?></strong></div>
                            </div>
                        <?php endif; ?>

                        <div class="publicResourceCardFooter">
                            <span class="publicResourceFilename"><i class="fa-solid fa-paperclip"></i><?php echo htmlspecialchars($row['file_name']); ?></span>
                            <a href="uploads/resources/<?php echo rawurlencode($row['file_name']); ?>" download class="publicResourceDownload">
                                <i class="fa-solid fa-download"></i> Download
                            </a>
                        </div>
                    </article>

                <?php endwhile; ?>
            <?php endif; ?>
        </div>

    </main>

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
function acceptCookies() {
    document.cookie = "foodfusion_cookie_accepted=true; max-age=" + 60*60*24*30 + "; path=/";
    document.getElementById('cookieBanner').style.display = 'none';
}
</script>

</body>
</html>
