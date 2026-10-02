<?php
session_start();
require_once 'db.php';

// Redirect non-admin users to the login page.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

// --- 1. DELETE MEMBER POST (AND ITS ASSOCIATED LIKES & COMMENTS) ---
if (isset($_GET['delete_post_id'])) {
    $delete_post_id = intval($_GET['delete_post_id']);

    // If the post has an image, find and delete it from the server.
    $stmt = $conn->prepare("SELECT image FROM community_posts WHERE id = ?");
    $stmt->bind_param("i", $delete_post_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res && !empty($res['image']) && file_exists("uploads/" . $res['image'])) {
        unlink("uploads/" . $res['image']);
    }

    // Delete related data from community_likes, community_comments, and community_posts.
    $conn->query("DELETE FROM community_likes WHERE post_id = $delete_post_id");
    $conn->query("DELETE FROM community_comments WHERE post_id = $delete_post_id");

    $del_stmt = $conn->prepare("DELETE FROM community_posts WHERE id = ?");
    $del_stmt->bind_param("i", $delete_post_id);
    if ($del_stmt->execute()) {
        header("Location: manage_community.php?msg=" . urlencode("Member post and all associated comments/likes deleted successfully!"));
        exit();
    } else {
        $error = "Failed to delete post.";
    }
}

// --- 2. DELETE INDIVIDUAL COMMENT ---
if (isset($_GET['delete_comment_id'])) {
    $comment_id = intval($_GET['delete_comment_id']);
    $del_comm = $conn->prepare("DELETE FROM community_comments WHERE id = ?");
    $del_comm->bind_param("i", $comment_id);
    if ($del_comm->execute()) {
        header("Location: manage_community.php?msg=" . urlencode("Comment deleted successfully!"));
        exit();
    } else {
        $error = "Failed to delete comment.";
    }
}

// --- 3. CLEAR ALL LIKES FOR A POST ---
if (isset($_GET['clear_likes_id'])) {
    $post_id = intval($_GET['clear_likes_id']);
    $conn->query("DELETE FROM community_likes WHERE post_id = $post_id");
    header("Location: manage_community.php?msg=" . urlencode("All likes cleared for this post!"));
    exit();
}

// --- 4. FETCH MEMBER POSTS & SEARCH ---
$search = trim($_GET['search'] ?? '');
$sql = "SELECT p.*, u.first_name, u.last_name, u.role 
        FROM community_posts p 
        LEFT JOIN users u ON p.user_id = u.id 
        WHERE 1=1";

if (!empty($search)) {
    $search_clean = $conn->real_escape_string($search);
    $sql .= " AND (p.title LIKE '%$search_clean%' OR p.content LIKE '%$search_clean%' OR u.first_name LIKE '%$search_clean%')";
}

$sql .= " ORDER BY p.id DESC";
$posts = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Member Posts - Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="manageCookbookPage">

    <!-- Header Navigation -->
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
            <a href="manage_community.php" class="active-nav">Manage Community</a>

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

    <section class="about-hero manageCommunityHero">
        <h1>Manage Community</h1>
        <p>Review member posts and keep conversations welcoming, helpful, and inspiring.</p>
    </section>

    <main class="communityMainWrapper manageCommunityMain">
        
        <!-- Flash Messages -->
        <?php if (isset($_GET['msg'])): ?>
            <div style="background: #DEF7EC; color: #03543F; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div style="background: #FDE8E8; color: #9B1C1C; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Search Bar -->
        <div class="manageCommunityToolbar">
            <div class="manageCommunityTitle">
                <h2>Posts &amp; interactions</h2>
            </div>

            <form action="" method="GET" class="communitySearchForm manageCommunitySearch">
                <div class="communitySearchInputGroup">
                    <i class="fa-solid fa-magnifying-glass communitySearchIcon"></i>
                    <input type="text" name="search" class="communityInput communitySearchInput" placeholder="Search post or user..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="cta-btn communitySearchBtn">Search</button>
                </div>
            </form>
        </div>

        <!-- Feed List -->
        <div class="communityPostGrid">
            <?php if ($posts && $posts->num_rows > 0): ?>
                <?php while ($post = $posts->fetch_assoc()): 
                    $post_id = $post['id'];
                    
                    // Total Likes Count from community_likes
                    $like_res = $conn->query("SELECT COUNT(*) AS total FROM community_likes WHERE post_id = $post_id");
                    $like_count = $like_res ? $like_res->fetch_assoc()['total'] : 0;

                    // Comments List from community_comments
                    $comments = $conn->query("SELECT c.*, u.first_name, u.last_name FROM community_comments c LEFT JOIN users u ON c.user_id = u.id WHERE c.post_id = $post_id ORDER BY c.id ASC");
                ?>
                    <div class="communityPostCard">
                        <div class="communityPostHeader">
                            <h3 class="communityPostTitle"><?= htmlspecialchars($post['title']) ?></h3>
                            <div class="communityPostMeta">
                                <i class="fa-solid fa-user"></i>
                                <span>
                                    <strong><?= htmlspecialchars(($post['first_name'] ?? 'Member') . ' ' . ($post['last_name'] ?? '')) ?></strong>
                                    <span class="admin-subject-badge" style="margin-left: 5px; text-transform: uppercase;">
                                        <?= htmlspecialchars($post['role'] ?? 'member') ?>
                                    </span>
                                </span>
                                &bull;
                                <i class="fa-solid fa-calendar-days"></i>
                                <span><?= date('M d, Y', strtotime($post['created_at'])) ?></span>
                            </div>
                        </div>

                        <?php if (!empty($post['image']) && file_exists("uploads/" . $post['image'])): ?>
                            <div class="communityPostImageContainer">
                                <img src="uploads/<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="communityPostImg" loading="lazy">
                            </div>
                        <?php endif; ?>

                        <div class="communityPostBody">
                            <p class="communityPostText"><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                        </div>

                        <!-- Likes Management -->
                        <div class="communityActionBar" style="border-top: 1px solid #eee; padding-top: 10px; margin-top: 15px;">
                            <div class="communityLikeCounter">
                                <i class="fa-solid fa-heart" style="color: #e63946;"></i>
                                <span><strong><?= $like_count ?></strong> Likes</span>
                            </div>
                            <?php if ($like_count > 0): ?>
                                <a href="manage_community.php?clear_likes_id=<?= $post_id ?>" class="communityDeleteBtn" onclick="return confirm('Clear all likes for this post?');">
                                    <i class="fa-solid fa-heart-crack"></i> Clear Likes
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- Comments Moderation Section -->
                        <div style="margin-top: 15px; background: #f9f9f9; padding: 12px; border-radius: 8px;">
                            <h4 style="font-size: 14px; margin-bottom: 10px; color: var(--text-dark);">
                                Comments (<?= $comments ? $comments->num_rows : 0 ?>)
                            </h4>
                            
                            <div class="communityCommentBoxList">
                                <?php if ($comments && $comments->num_rows > 0): ?>
                                    <?php while ($comm = $comments->fetch_assoc()): ?>
                                        <div style="display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 8px 12px; border-radius: 6px; margin-bottom: 8px; border: 1px solid #eee;">
                                            <div>
                                                <strong style="font-size: 13px; color: var(--text-dark);"><?= htmlspecialchars(($comm['first_name'] ?? 'Member') . ' ' . ($comm['last_name'] ?? '')) ?>:</strong>
                                                <span style="font-size: 13px; color: #555;"><?= htmlspecialchars($comm['comment_text'] ?? $comm['comment'] ?? '') ?></span>
                                            </div>
                                            <a href="manage_community.php?delete_comment_id=<?= $comm['id'] ?>" class="communityDeleteBtn" onclick="return confirm('Delete this comment?');">
                                                <i class="fa-solid fa-trash"></i> Delete Comment
                                            </a>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p style="font-size: 12px; color: var(--text-muted); margin: 0;">No comments on this post.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Delete Whole Post Button -->
                        <div class="communityPostManageBar" style="margin-top: 15px; text-align: right;">
                            <a href="manage_community.php?delete_post_id=<?= $post_id ?>" class="communityDeleteBtn" onclick="return confirm('Are you sure you want to delete this post and all associated comments/likes?');">
                                <i class="fa-solid fa-trash"></i> Delete Post
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="communityPostCard" style="text-align: center; color: var(--text-muted);">
                    <p>No member posts found.</p>
                </div>
            <?php endif; ?>
        </div>

    </main>

</body>
</html>
