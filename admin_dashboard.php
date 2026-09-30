<?php
session_start();
require_once 'db.php';

// Redirect non-admin users to the login page.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Retrieve analytics and statistics.
$total_users = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'] ?? 0;
$total_posts = $conn->query("SELECT COUNT(*) AS count FROM community_posts")->fetch_assoc()['count'] ?? 0;

// Retrieve recipe and resource counts.
$total_recipes = $conn->query("SELECT COUNT(*) AS count FROM recipes")->fetch_assoc()['count'] ?? 0;
$total_resources = $conn->query("SELECT COUNT(*) AS count FROM resources")->fetch_assoc()['count'] ?? 0;

// Retrieve the registered users list.
$users = $conn->query("SELECT id, first_name, last_name, email, role FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Link the CSS file. -->
    <link rel="stylesheet" href="style.css">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="adminDashboardPage">

<?php if (isset($_GET['success']) || isset($_GET['error'])): ?>
    <?php
        $is_success = isset($_GET['success']);
        $notification_message = $is_success ? $_GET['success'] : $_GET['error'];
        $notification_color = $is_success ? '#38A169' : '#E53E3E';
        $notification_background = $is_success ? '#C6F6D5' : '#FFF5F5';
        $notification_icon = $is_success ? 'fa-solid fa-circle-check' : 'fa-solid fa-triangle-exclamation';
    ?>
    <div id="tgNotification" class="tg-notification" role="status" aria-live="polite" style="background-color: <?= $notification_background ?>; border-left-color: <?= $notification_color ?>;">
        <div class="tg-notification-icon" style="color: <?= $notification_color ?>;"><i class="<?= $notification_icon ?>"></i></div>
        <div class="tg-notification-content">
            <div class="tg-notification-title" style="color: <?= $notification_color ?>;"><?= $is_success ? 'FoodFusion Success' : 'FoodFusion Alert' ?></div>
            <div><?= htmlspecialchars($notification_message, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <button class="tg-notification-close" type="button" aria-label="Close notification" onclick="document.getElementById('tgNotification').classList.remove('show')">&times;</button>
    </div>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const notification = document.getElementById('tgNotification');
            window.setTimeout(() => notification.classList.add('show'), 100);
            window.setTimeout(() => notification.classList.remove('show'), 5000);
        });
    </script>
<?php endif; ?>

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
            <a href="admin_dashboard.php" class="active-nav">Dashboard</a>
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
    <section class="about-hero adminDashboardHero">
        <h1>Admin Dashboard</h1>
        <p>Keep track of your members, recipes, resources, and community activity.</p>
    </section>

    <!-- Main Admin Content -->
    <main class="admin-container adminDashboardMain">

        <!-- Dashboard Stats Cards (4 Columns) -->
        <div class="grid-4col adminDashboardStats">
            <div class="value-box adminDashboardStat">
                <i class="fa-solid fa-users"></i>
                <h2 style="font-size: 32px; margin-top: 5px; color: var(--text-dark);"><?= $total_users ?></h2>
                <p class="value-desc">Total Users</p>
            </div>
            <div class="value-box adminDashboardStat">
                <i class="fa-solid fa-newspaper"></i>
                <h2 style="font-size: 32px; margin-top: 5px; color: var(--text-dark);"><?= $total_posts ?></h2>
                <p class="value-desc">Community Posts</p>
            </div>
            <div class="value-box adminDashboardStat">
                <i class="fa-solid fa-utensils"></i>
                <h2 style="font-size: 32px; margin-top: 5px; color: var(--text-dark);"><?= $total_recipes ?></h2>
                <p class="value-desc">Total Recipes</p>
            </div>
            <div class="value-box adminDashboardStat">
                <i class="fa-solid fa-book"></i>
                <h2 style="font-size: 32px; margin-top: 5px; color: var(--text-dark);"><?= $total_resources ?></h2>
                <p class="value-desc">Total Resources</p>
            </div>
        </div>

        <!-- Registered Users List Table -->
        <div class="adminDashboardSectionHeading">
            <div><h2>Registered Users</h2></div>
            <p>Recently joined FoodFusion members</p>
        </div>
        <div class="admin-table-wrapper adminDashboardTable">
            <table class="admin-messages-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users && $users->num_rows > 0): ?>
                        <?php while($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td class="admin-fw-bold"><?= htmlspecialchars($user['first_name'] . ' ' . ($user['last_name'] ?? '')) ?></td>
                            <td class="admin-email"><?= htmlspecialchars($user['email']) ?></td>
                            <td><span class="admin-subject-badge"><?= htmlspecialchars($user['role']) ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="admin-no-data">No registered users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>
