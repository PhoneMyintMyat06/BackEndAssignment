<?php
session_start();
include "db.php";

// Check cookie acceptance on the PHP side as well.
$cookie_accepted = isset($_COOKIE['foodfusion_cookie_accepted']) ? true : false;

/* Only logged-in users can access My Wall */
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ADD POST */
if (isset($_POST['submit_post'])) {

    $title = $_POST['title'];
    $content = $_POST['content'];
    $imageName = "";

    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . basename($_FILES['image']['name']);
        $tmpName = $_FILES['image']['tmp_name'];
        move_uploaded_file($tmpName, "uploads/" . $imageName);
    }

    $sql = "INSERT INTO community_posts 
            (user_id, title, content, image)
            VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $user_id, $title, $content, $imageName);
    $stmt->execute();

    header("Location: my_wall.php");
    exit();
}

/* GET ONLY MY POSTS */
$stmt = $conn->prepare("
    SELECT *
    FROM community_posts
    WHERE user_id = ?
    ORDER BY id DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$posts = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Cookbook - My Wall</title>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="style.css">
</head>
<body id="communityPageBody" class="myWallPage">

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
            <a href="community_cookbook.php" class="active-nav">Community Cookbook</a>
            <a href="culinary_resources.php">Culinary Resources</a>
            <a href="educational_resources.php">Educational Resources</a>
            <a href="contact.php">Contact Us</a>
        <?php endif; ?>

        <!-- Admin Role -->
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="manage_cookbook.php">Manage Cookbook</a>

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

    <!-- Hero Section with Tab Navigation -->
    <section class="hero community-hero-override">
        <h1>Community Cookbook - My Wall</h1>
        <p>Create, update, edit and delete your own recipes, cooking tips and culinary experiences.</p>
        
        <!-- Tab Navigation Container -->
        <div class="communityTabNav">
            <a href="community_cookbook.php" class="communityTabLink">
                <i class="fa-solid fa-users"></i>
                <span>Community</span>
            </a>
            <a href="my_wall.php" class="communityTabLink communityTabActive">
                <i class="fa-solid fa-user"></i>
                <span>My Wall</span>
            </a>
        </div>
    </section>

    <!-- Main Container -->
    <main class="communityMainWrapper">

        <!-- Post Creation Card -->
        <section class="communityPostCard communityCreateCard">
            <h2 class="communityFormTitle">
                <i class="fa-solid fa-pen-to-square"></i> Create New Post
            </h2>

            <form method="POST" enctype="multipart/form-data" class="communityCreateForm">
                <div class="communityFormGroup">
                    <input type="text" name="title" class="communityInput" placeholder="Post Title" required>
                </div>

                <div class="communityFormGroup">
                    <textarea name="content" class="communityTextarea" placeholder="Share your recipe, cooking tip or culinary experience..." rows="4" required></textarea>
                </div>

                <div class="communityFormGroup communityFileGroup" data-file-field data-empty-text="No photo selected">
                    <label for="postImageUpload" class="communityFileLabel">
                        <i class="fa-solid fa-image"></i> Choose Photo
                    </label>
                    <input type="file" name="image" id="postImageUpload" class="communityFileInput" data-file-input accept="image/*">
                    <div class="fileSelectionPreview" data-file-preview aria-live="polite">
                        <span class="fileSelectionIcon"><i class="fa-regular fa-image"></i></span>
                        <span class="fileSelectionText"><strong data-file-name>No photo selected</strong><small data-file-size>Choose a photo to see its name and size</small></span>
                    </div>
                </div>

                <button type="submit" name="submit_post" class="communitySubmitBtn">
                    <i class="fa-solid fa-paper-plane"></i> Submit Post
                </button>
            </form>
        </section>

        <!-- Posts Section -->
        <h2 class="section-title">My Posts</h2>

        <div class="communityFeedList">
            <?php if ($posts->num_rows === 0): ?>
                <div class="communityPostCard" style="text-align: center;">
                    <p class="communityPostText">You haven't created any posts yet.</p>
                </div>
            <?php else: ?>
                <?php while ($row = $posts->fetch_assoc()): ?>

                    <article class="communityPostCard">

                        <div class="communityPostHeader">
                            <h3 class="communityPostTitle"><?php echo htmlspecialchars($row['title']); ?></h3>
                        </div>

                        <?php if (!empty($row['image'])): ?>
                            <div class="communityPostImageContainer">
                                <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" class="communityPostImg" alt="Post Image">
                            </div>
                        <?php endif; ?>

                        <div class="communityPostBody">
                            <p class="communityPostText"><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                        </div>

                        <!-- Management Bar (Edit/Delete Links) -->
                        <div class="communityPostManageBar">
                            <a href="edit_post.php?id=<?php echo $row['id']; ?>" class="communityEditBtn">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </a>
                            <a href="delete_post.php?id=<?php echo $row['id']; ?>" 
                               onclick="return confirm('Delete this post?')" 
                               class="communityDeleteBtn">
                               <i class="fa-solid fa-trash-can"></i> Delete
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

<script src="file-upload.js"></script>
</body>
</html>
