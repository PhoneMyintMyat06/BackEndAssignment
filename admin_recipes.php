<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied. Admin only.");
}

/* ADD RECIPE */
if (isset($_POST['add'])) {

    $title = $_POST['title'];
    $cuisine = $_POST['cuisine_type'];
    $dietary = $_POST['dietary_preference'];
    $difficulty = $_POST['difficulty'];
    $description = $_POST['description'];

    $imageName = $_FILES['image']['name'];
    $tmpName = $_FILES['image']['tmp_name'];

    move_uploaded_file($tmpName, "uploads/" . $imageName);

    $sql = "INSERT INTO recipes 
            (title, cuisine_type, dietary_preference, difficulty, description, image)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $title, $cuisine, $dietary, $difficulty, $description, $imageName);
    $stmt->execute();

    header("Location: admin_recipes.php");
    exit();
}

$recipes = $conn->query("SELECT * FROM recipes ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Recipes</title>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="style.css">
</head>
<body id="communityPageBody" class="adminRecipesPage">

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
    <!-- Hero Section -->
    <section class="hero community-hero-override">
        <h1>Admin - Manage Recipes</h1>
        <p>Add new recipes to the collection, manage existing entries, and keep the menu up to date.</p>
    </section>

    <!-- Main Container -->
    <main class="communityMainWrapper adminRecipesMain">

        <!-- Add Recipe Card -->
        <section class="communityPostCard communityCreateCard adminRecipeFormCard">
            <h2 class="communityFormTitle">
                <i class="fa-solid fa-utensils"></i> Add New Recipe
            </h2>

            <form method="POST" enctype="multipart/form-data" class="communityCreateForm">
                <div class="communityFormGroup">
                    <label class="adminRecipeLabel">Recipe Title</label>
                    <input type="text" name="title" class="communityInput" placeholder="Recipe Title" required>
                </div>

                <!-- Cuisine, Dietary & Difficulty Fields with Labels -->
                <div class="communityFormGroup adminRecipeFields">
                    <div class="adminRecipeField">
                        <label class="adminRecipeLabel">Cuisine Type</label>
                        <input type="text" name="cuisine_type" class="communityInput" placeholder="e.g. Italian, Asian" required>
                    </div>
                    
                    <div class="adminRecipeField">
                        <label class="adminRecipeLabel">Dietary Preference</label>
                        <select name="dietary_preference" class="communitySelect">
                            <option value="">Select Diet (Optional)</option>
                            <option>Vegetarian</option>
                            <option>Vegan</option>
                            <option>Halal</option>
                            <option>Gluten-Free</option>
                            <option>Keto</option>
                        </select>
                    </div>

                    <div class="adminRecipeField">
                        <label class="adminRecipeLabel">Difficulty Level</label>
                        <select name="difficulty" class="communitySelect" required>
                            <option value="">Select Difficulty</option>
                            <option>Easy</option>
                            <option>Medium</option>
                            <option>Hard</option>
                        </select>
                    </div>
                </div>

                <div class="communityFormGroup">
                    <label class="adminRecipeLabel">Description</label>
                    <textarea name="description" class="communityTextarea" placeholder="Description..." rows="4" required></textarea>
                </div>

                <div class="communityFormGroup communityFileGroup" data-file-field data-empty-text="No image selected">
                    <label for="recipeImageUpload" class="communityFileLabel">
                        <i class="fa-solid fa-image"></i> Choose Photo
                    </label>
                    <input type="file" name="image" id="recipeImageUpload" class="communityFileInput" data-file-input accept="image/*" required>
                    <div class="fileSelectionPreview" data-file-preview aria-live="polite">
                        <span class="fileSelectionIcon"><i class="fa-regular fa-image"></i></span>
                        <span class="fileSelectionText"><strong data-file-name>No image selected</strong><small data-file-size>Choose a photo to see its name and size</small></span>
                    </div>
                </div>

                <button type="submit" name="add" class="communitySubmitBtn">
                    <i class="fa-solid fa-plus"></i> Add Recipe
                </button>
            </form>
        </section>

        <!-- Recipe List -->
        <div class="adminRecipeListHeading">
            <div>
                <span class="adminRecipeEyebrow">COLLECTION</span>
                <h2 class="section-title">Recipe List</h2>
            </div>
            <span class="adminRecipeListHint"><i class="fa-solid fa-layer-group"></i> Manage your recipes</span>
        </div>

        <div class="adminRecipeGrid">
            <?php if ($recipes->num_rows === 0): ?>
                <div class="communityPostCard" style="text-align: center;">
                    <p class="communityPostText">No recipes added yet.</p>
                </div>
            <?php else: ?>
                <?php while ($row = $recipes->fetch_assoc()): ?>

                    <article class="adminRecipeCard">

                        <div class="adminRecipeImage">
                            <?php if (!empty($row['image'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                            <?php else: ?>
                                <div class="adminRecipeImagePlaceholder"><i class="fa-solid fa-utensils"></i><span>No image</span></div>
                            <?php endif; ?>
                            <span class="adminRecipeDifficulty"><?php echo htmlspecialchars($row['difficulty']); ?></span>
                        </div>

                        <div class="adminRecipeCardContent">
                            <div class="adminRecipeCardHeader">
                                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                <span class="adminRecipeCuisine"><i class="fa-solid fa-bowl-food"></i> <?php echo htmlspecialchars($row['cuisine_type']); ?></span>
                            </div>
                            <?php if (!empty($row['dietary_preference'])): ?>
                                <span class="adminRecipeDiet"><i class="fa-solid fa-leaf"></i> <?php echo htmlspecialchars($row['dietary_preference']); ?></span>
                            <?php endif; ?>
                            <p class="adminRecipeDescription"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>

                            <div class="adminRecipeActions">
                                <a href="edit_recipes.php?id=<?php echo (int)$row['id']; ?>" class="adminRecipeEditBtn">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit recipe
                                </a>
                                <a href="delete_recipe.php?id=<?php echo $row['id']; ?>"
                                   onclick="return confirm('Delete this recipe?')"
                                   class="adminRecipeDeleteBtn">
                                   <i class="fa-solid fa-trash-can"></i> Delete
                                </a>
                            </div>
                        </div>

                    </article>

                <?php endwhile; ?>
            <?php endif; ?>
        </div>

    </main>

<script src="file-upload.js"></script>
</body>
</html>
