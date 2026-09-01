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
<body>

<nav>
    <a href="index.php" class="brand-logo">FoodFusion Admin</a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="admin_recipes.php">Recipes Collection</a>
        <a href="admin_resources.php">Resources</a>
        <a href="admin_contact.php" class="active-nav">Contact Messages</a>
    </div>
    <span class="user-area">
        Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?>
        <a href="logout.php" class="cta-btn">Logout</a>
    </span>
</nav>

<div class="admin-container" style="max-width: 1200px;">
    <h2>User Contact Messages & Replies</h2>
    <p class="admin-subtitle">Review messages from users and reply directly from the dashboard.</p>

    <div class="admin-table-wrapper">
        <table class="admin-messages-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Info</th>
                    <th>Subject & Message</th>
                    <th>Status</th>
                    <th>Action / Reply</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td>
                                <strong class="admin-fw-bold"><?php echo htmlspecialchars($row['name']); ?></strong><br>
                                <span class="admin-email" style="font-size: 13px;"><?php echo htmlspecialchars($row['email']); ?></span><br>
                                <span class="admin-date"><?php echo $row['created_at']; ?></span>
                            </td>
                            <td class="admin-message-cell">
                                <span class="admin-subject-badge"><?php echo htmlspecialchars($row['subject']); ?></span>
                                <p style="margin-top: 6px;"><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
                                
                                <?php if (!empty($row['reply'])): ?>
                                    <div class="reply-box">
                                        <strong>Admin Reply:</strong> 
                                        <p><?php echo nl2br(htmlspecialchars($row['reply'])); ?></p>
                                        <span style="font-size: 11px; color: var(--text-muted);">Replied at: <?php echo $row['replied_at']; ?></span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['status'] == 'Replied'): ?>
                                    <span class="status-badge status-replied">Replied</span>
                                <?php else: ?>
                                    <span class="status-badge status-pending">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                            <form action="send_reply.php" method="POST" style="display: flex; flex-direction: column; gap: 6px; min-width: 200px;">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <textarea name="reply" placeholder="Type your reply..." rows="2" style="padding: 6px; border-radius: 4px; border: 1px solid #CBD5E0; font-size: 13px;" required><?php echo htmlspecialchars($row['reply'] ?? ''); ?></textarea>
                                <button type="submit" style="padding: 6px 10px; background: var(--accent-color); color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold;">
                                    <?php echo empty($row['reply']) ? 'Send Reply' : 'Update Reply'; ?>
                                </button>
                            </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="admin-no-data">No messages found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>