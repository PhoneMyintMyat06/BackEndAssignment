<?php
session_start();
include "db.php";

// Check cookie acceptance.
$cookie_accepted = isset($_COOKIE['foodfusion_cookie_accepted']) ? true : false;

// Get the search and filter inputs.
$search = $_GET['search'] ?? "";
$cuisine = $_GET['cuisine'] ?? "";
$dietary = $_GET['dietary'] ?? "";
$difficulty = $_GET['difficulty'] ?? "";

// Prepare the SQL query.
$sql = "SELECT * FROM recipes WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($cuisine) && $cuisine !== 'All') {
    $sql .= " AND cuisine_type = ?";
    $params[] = $cuisine;
    $types .= "s";
}

if (!empty($dietary) && $dietary !== 'All') {
    $sql .= " AND dietary_preference = ?";
    $params[] = $dietary;
    $types .= "s";
}

if (!empty($difficulty) && $difficulty !== 'All') {
    $sql .= " AND difficulty = ?";
    $params[] = $difficulty;
    $types .= "s";
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Retrieve the unique cuisine options.
$cuisines_res = $conn->query("SELECT DISTINCT cuisine_type FROM recipes WHERE cuisine_type IS NOT NULL AND cuisine_type != ''");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Collection - FoodFusion</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body class="recipesPage">

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
            setTimeout(() => { notif.classList.add('show'); }, 100);
            setTimeout(() => { notif.classList.remove('show'); }, 5000);
        });

        function closeTgNotification() {
            document.getElementById('tgNotification').classList.remove('show');
        }
    </script>
<?php endif; ?>

<nav>
    <a href="index.php" class="brand-logo">FoodFusion</a>
    
    <div class="nav-links">
        <!-- Guest Users -->
        <?php if(!isset($_SESSION['user_id'])): ?>
            <a href="index.php">Home</a>
            <a href="about.php">About Us</a>
            <a href="recipes.php" class="active-nav">Recipe Collection</a>
            <a href="contact.php">Contact Us</a>
        <?php endif; ?>

        <!-- Member Role -->
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'member'): ?>
            <a href="index.php">Home</a>
            <a href="about.php">About Us</a>
            <a href="recipes.php" class="active-nav">Recipe Collection</a>
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
            Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? $_SESSION['user'] ?? ''); ?>
            <a href="logout.php" class="cta-btn">Logout</a>
        <?php else: ?>
            <button onclick="showLogin()">Login</button>
            <button onclick="showRegister()">Join Us</button>
        <?php endif; ?>
    </span>
</nav>

<section class="hero community-hero-override">
    <h1>Recipe Collection</h1>
    <p>Find something delicious for today. Explore recipes by cuisine, dietary preference, and cooking difficulty.</p>
</section>

<main class="recipesMain">

    <!-- Filter & Search Section -->
    <div class="filter-card-wrapper">
        <div class="recipeFilterHeading">
            <span class="recipeFilterIcon"><i class="fa-solid fa-sliders"></i></span>
            <div>
                <h2>Find your next favorite</h2>
                <p>Choose a few filters to narrow the collection.</p>
            </div>
        </div>
        <form method="GET" action="recipes.php" class="filter-grid">
            
            <div class="filter-group">
                <label for="recipeSearch">Search recipes</label>
                <div class="recipeSearchField">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input id="recipeSearch" type="text" name="search" class="filter-control" placeholder="Try pasta, soup, or salad" value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>

            <div class="filter-group">
                <label for="recipeCuisine">Cuisine</label>
                <select id="recipeCuisine" name="cuisine" class="filter-control">
                    <option value="All">All Cuisines</option>
                    <?php while($c = $cuisines_res->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($c['cuisine_type']); ?>" <?php echo ($cuisine === $c['cuisine_type']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['cuisine_type']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="recipeDiet">Dietary preference</label>
                <select id="recipeDiet" name="dietary" class="filter-control">
                    <option value="All">All Diets</option>
                    <option value="Vegetarian" <?php echo ($dietary === 'Vegetarian') ? 'selected' : ''; ?>>Vegetarian</option>
                    <option value="Vegan" <?php echo ($dietary === 'Vegan') ? 'selected' : ''; ?>>Vegan</option>
                    <option value="Halal" <?php echo ($dietary === 'Halal') ? 'selected' : ''; ?>>Halal</option>
                    <option value="Gluten-Free" <?php echo ($dietary === 'Gluten-Free') ? 'selected' : ''; ?>>Gluten-Free</option>
                    <option value="Keto" <?php echo ($dietary === 'Keto') ? 'selected' : ''; ?>>Keto</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="recipeDifficulty">Difficulty</label>
                <select id="recipeDifficulty" name="difficulty" class="filter-control">
                    <option value="All">All Levels</option>
                    <option value="Easy" <?php echo ($difficulty === 'Easy') ? 'selected' : ''; ?>>Easy</option>
                    <option value="Medium" <?php echo ($difficulty === 'Medium') ? 'selected' : ''; ?>>Medium</option>
                    <option value="Hard" <?php echo ($difficulty === 'Hard') ? 'selected' : ''; ?>>Hard</option>
                </select>
            </div>

            <div class="recipeFilterActions">
                <button type="submit" class="btn-search-filter"><i class="fa-solid fa-magnifying-glass"></i> Find recipes</button>
                <?php if (!empty($search) || !empty($cuisine) || !empty($dietary) || !empty($difficulty)): ?>
                    <a class="recipeClearFilters" href="recipes.php">Clear filters</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="recipeResultsBar">
        <div>
            <h2>Explore recipes</h2>
            <p><?php echo (int)$result->num_rows; ?> <?php echo $result->num_rows === 1 ? 'recipe' : 'recipes'; ?> to inspire your next meal</p>
        </div>
        <span class="recipeResultsIcon"><i class="fa-solid fa-book-open"></i></span>
    </div>

    <div class="recipe-grid-3col">
        <?php if ($result->num_rows === 0): ?>
            <div class="recipeEmptyState">
                <span><i class="fa-solid fa-bowl-food"></i></span>
                <h3>No recipes found</h3>
                <p>Try changing your search or clearing one or more filters.</p>
                <a href="recipes.php">View all recipes</a>
            </div>
        <?php else: ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <article class="recipe-card-box publicRecipeCard">
                    <div class="recipe-img-holder">
                        <?php if (!empty($row['image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                        <?php else: ?>
                            <div class="recipeImagePlaceholder"><i class="fa-solid fa-utensils"></i><span>Fresh inspiration</span></div>
                        <?php endif; ?>
                        <?php if (!empty($row['cuisine_type'])): ?>
                            <span class="recipeImageCuisine"><i class="fa-solid fa-tag"></i> <?php echo htmlspecialchars($row['cuisine_type']); ?></span>
                        <?php endif; ?>
                        <span class="recipeDifficultyBadge"><i class="fa-solid fa-gauge-high"></i> <?php echo htmlspecialchars($row['difficulty'] ?? 'Easy'); ?></span>
                    </div>
                    <div class="recipe-card-details">
                        <h3 class="recipe-card-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p class="recipe-card-desc"><?php echo htmlspecialchars($row['description']); ?></p>
                        
                        <div class="recipe-tags-row">
                            <?php if (!empty($row['cuisine_type'])): ?>
                                <span class="tag-pill"><?php echo htmlspecialchars($row['cuisine_type']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($row['dietary_preference'])): ?>
                                <span class="tag-pill diet-pill"><?php echo htmlspecialchars($row['dietary_preference']); ?></span>
                            <?php endif; ?>
                        </div>

                    </div>
                </article>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

</main>

<!-- Login / Register Popup Modals -->
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
