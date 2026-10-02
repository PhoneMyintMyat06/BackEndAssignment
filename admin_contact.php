<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php?error=" . urlencode("Access denied. Admin only."));
    exit();
}

$sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Contact Messages - FoodFusion Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .status-pending { background: #FEFCBF; color: #B7791F; }
        .status-replied { background: #C6F6D5; color: #22543D; }
        .reply-box { margin-top: 8px; padding: 8px; background: #EDF2F7; border-radius: 6px; font-size: 13px; }
    </style>
</head>
<body class="adminContactPage">

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
            <a href="manage_community.php">Manage Community</a>

            <div class="dropdown">
                <a href="#">Manage Resources ▼</a>
                <div class="dropdown-content">
                    <a href="admin_recipes.php">Recipes Collection</a>
                    <a href="admin_resources.php">Resources</a>
                </div>
            </div>

            <a href="admin_contact.php" class="active-nav">Contact Us</a>
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
<main class="admin-container adminContactMain">
    <header class="adminContactHeading">
        <div>
            <p class="adminContactEyebrow">INBOX</p>
            <h1>Contact Messages</h1>
            <p class="admin-subtitle">Choose a message to read it and reply.</p>
        </div>
        <span class="adminContactCount"><i class="fa-regular fa-envelope"></i> <?= (int)($result ? $result->num_rows : 0) ?> messages</span>
    </header>

    <section class="adminContactList" aria-label="Contact messages">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <details class="adminContactItem">
                    <summary class="adminContactSummary">
                        <div class="adminContactSummaryMain">
                            <div class="adminContactTopline">
                                <strong class="adminContactSender"><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <span class="adminContactEmail"><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <div class="adminContactPreviewLine">
                                <span class="admin-subject-badge"><?= htmlspecialchars($row['subject'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="adminContactPreview"><?= htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        </div>
                        <div class="adminContactSummaryMeta">
                            <?php if (($row['status'] ?? '') === 'Replied'): ?>
                                <span class="status-badge status-replied">Replied</span>
                            <?php else: ?>
                                <span class="status-badge status-pending">Pending</span>
                            <?php endif; ?>
                            <time datetime="<?= htmlspecialchars(date('c', strtotime($row['created_at'])), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(date('M j, Y', strtotime($row['created_at'])), ENT_QUOTES, 'UTF-8') ?></time>
                            <i class="fa-solid fa-chevron-down adminContactChevron" aria-hidden="true"></i>
                        </div>
                    </summary>

                    <div class="adminContactDetails">
                        <div class="adminContactFullMessage">
                            <h2>Message</h2>
                            <p><?= nl2br(htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8')) ?></p>
                        </div>

                        <?php if (!empty($row['reply'])): ?>
                            <div class="adminContactPreviousReply">
                                <h3><i class="fa-solid fa-reply"></i> Previous reply</h3>
                                <p><?= nl2br(htmlspecialchars($row['reply'], ENT_QUOTES, 'UTF-8')) ?></p>
                                <?php if (!empty($row['replied_at'])): ?>
                                    <time datetime="<?= htmlspecialchars(date('c', strtotime($row['replied_at'])), ENT_QUOTES, 'UTF-8') ?>">Sent <?= htmlspecialchars(date('M j, Y g:i A', strtotime($row['replied_at'])), ENT_QUOTES, 'UTF-8') ?></time>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <form action="send_reply.php" method="POST" class="adminContactReplyForm">
                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                            <label for="reply-<?= (int)$row['id'] ?>"><?= empty($row['reply']) ? 'Write a reply' : 'Edit your reply' ?></label>
                            <textarea id="reply-<?= (int)$row['id'] ?>" name="reply" placeholder="Type your reply..." rows="4" required><?= htmlspecialchars($row['reply'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                            <button type="submit"><i class="fa-solid fa-paper-plane"></i> <?= empty($row['reply']) ? 'Send Reply' : 'Update Reply' ?></button>
                        </form>
                    </div>
                </details>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="adminContactEmptyState">
                <i class="fa-regular fa-envelope-open"></i>
                <h2>No messages yet</h2>
                <p>Contact messages from your members will appear here.</p>
            </div>
        <?php endif; ?>
    </section>
</main>

</body>
</html>
