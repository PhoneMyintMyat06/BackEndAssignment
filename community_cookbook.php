<?php
session_start();
include "db.php";

// Check cookie acceptance on the PHP side as well.
$cookie_accepted = isset($_COOKIE['foodfusion_cookie_accepted']) ? true : false;

/* Only logged-in users can view, like, comment */
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* LIKE POST */
if (isset($_GET['like'])) {

    $post_id = $_GET['like'];

    $sql = "INSERT IGNORE INTO community_likes 
            (post_id, user_id)
            VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();

    header("Location: community_cookbook.php");
    exit();
}

/* ADD COMMENT */
if (isset($_POST['add_comment'])) {

    $post_id = $_POST['post_id'];
    $comment = $_POST['comment'];

    $sql = "INSERT INTO community_comments 
            (post_id, user_id, comment)
            VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $post_id, $user_id, $comment);
    $stmt->execute();

    header("Location: community_cookbook.php");
    exit();
}

/* GET ALL POSTS */
$posts = $conn->query("
    SELECT community_posts.*, users.first_name
    FROM community_posts
    JOIN users ON community_posts.user_id = users.id
    ORDER BY community_posts.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Cookbook - FoodFusion</title>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Your Main Stylesheet -->
    <link rel="stylesheet" href="style.css">
</head>

<body id="communityPageBody" class="communityCookbookPage">

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

    <!-- Hero Section -->
    <section class="hero community-hero-override">
        <h1>Community Cookbook</h1>
        <p>View recipes, cooking tips, and culinary experiences shared by FoodFusion members. Members can like and comment on community posts.</p>
        
        <!-- Enhanced Tab Navigation Container -->
        <div class="communityTabNav">
            <a href="community_cookbook.php" class="communityTabLink communityTabActive">
                <i class="fa-solid fa-users"></i>
                <span>Community</span>
            </a>
            <a href="my_wall.php" class="communityTabLink">
                <i class="fa-solid fa-user"></i>
                <span>My Wall</span>
            </a>
        </div>
    </section>

    <!-- Main Container -->
    <main class="communityMainWrapper">
        
        <h2 class="section-title">Community Posts</h2>

        <div class="communityPostGrid">
        <?php if ($posts->num_rows === 0): ?>
            <div class="communityEmptyState">
                <span><i class="fa-solid fa-book-open"></i></span>
                <h3>No community posts yet</h3>
                <p>Be the first to share a recipe, cooking tip, or kitchen story.</p>
            </div>
        <?php else: ?>
        <?php while ($row = $posts->fetch_assoc()): ?>

            <article class="communityPostCard">

                <div class="communityPostHeader">
                    <h3 class="communityPostTitle"><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p class="communityPostMeta">
                        <i class="fa-solid fa-circle-user"></i>
                        <span>Posted by <strong><?php echo htmlspecialchars($row['first_name']); ?></strong></span>
                    </p>
                </div>

                <?php if (!empty($row['image'])): ?>
                    <div class="communityPostImageContainer">
                        <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" class="communityPostImg" alt="<?php echo htmlspecialchars($row['title']); ?>" loading="lazy">
                    </div>
                <?php endif; ?>

                <div class="communityPostBody">
                    <p class="communityPostText"><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                </div>

                <?php
                $post_id = $row['id'];

                $likeStmt = $conn->prepare("
                    SELECT COUNT(*) AS total_likes
                    FROM community_likes
                    WHERE post_id = ?
                ");
                $likeStmt->bind_param("i", $post_id);
                $likeStmt->execute();
                $likeData = $likeStmt->get_result()->fetch_assoc();

                $commentStmt = $conn->prepare("
                    SELECT community_comments.*, users.first_name
                    FROM community_comments
                    JOIN users ON community_comments.user_id = users.id
                    WHERE community_comments.post_id = ?
                    ORDER BY community_comments.id DESC
                ");
                $commentStmt->bind_param("i", $post_id);
                $commentStmt->execute();
                $commentResult = $commentStmt->get_result();
                ?>

                <div class="communityActionBar">
                    <a href="community_cookbook.php?like=<?php echo $post_id; ?>" class="communityLikeButton">
                        <i class="fa-regular fa-thumbs-up"></i> Like
                    </a>
                    <span class="communityLikeCounter">
                        <i class="fa-solid fa-heart" style="color: var(--accent-color);"></i>
                        <?php echo $likeData['total_likes']; ?> likes
                    </span>
                </div>

                <div class="communityCommentSection">
                    <h4 class="communityCommentSectionTitle">Comments</h4>

                    <div class="communityCommentBoxList">
                        <?php while ($comment = $commentResult->fetch_assoc()): ?>
                            <div class="communityCommentBubble">
                                <strong class="communityCommentUser"><?php echo htmlspecialchars($comment['first_name']); ?></strong>
                                <span class="communityCommentBody"><?php echo htmlspecialchars($comment['comment']); ?></span>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <form method="POST" class="communityCommentInputForm">
                        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                        <input type="text" name="comment" class="communityCommentInputField" placeholder="Write a comment..." required>
                        <button type="submit" name="add_comment" class="communityCommentSubmitBtn">Comment</button>
                    </form>
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
