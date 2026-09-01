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
<body id="communityPageBody">

    <!-- Hero Section -->
    <section class="hero community-hero-override">
        <h1>Admin - Manage Recipes</h1>
        <p>Add new recipes to the collection, manage existing entries, and keep the menu up to date.</p>
    </section>

    <!-- Main Container -->
    <main class="communityMainWrapper">

        <!-- Add Recipe Card -->
        <section class="communityPostCard communityCreateCard">
            <h2 class="communityFormTitle">
                <i class="fa-solid fa-utensils"></i> Add New Recipe
            </h2>

            <form method="POST" enctype="multipart/form-data" class="communityCreateForm">
                <div class="communityFormGroup">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px;">Recipe Title</label>
                    <input type="text" name="title" class="communityInput" placeholder="Recipe Title" required>
                </div>

                <!-- Cuisine, Dietary & Difficulty Fields with Labels -->
                <div class="communityFormGroup" style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 150px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px;">Cuisine Type</label>
                        <input type="text" name="cuisine_type" class="communityInput" placeholder="e.g. Italian, Asian" required>
                    </div>
                    
                    <div style="flex: 1; min-width: 150px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px;">Dietary Preference</label>
                        <select name="dietary_preference" class="communitySelect" style="width: 100%;">
                            <option value="">Select Diet (Optional)</option>
                            <option>Vegetarian</option>
                            <option>Vegan</option>
                            <option>Halal</option>
                            <option>Gluten-Free</option>
                            <option>Keto</option>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 150px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px;">Difficulty Level</label>
                        <select name="difficulty" class="communitySelect" style="width: 100%;" required>
                            <option value="">Select Difficulty</option>
                            <option>Easy</option>
                            <option>Medium</option>
                            <option>Hard</option>
                        </select>
                    </div>
                </div>

                <div class="communityFormGroup">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px;">Description</label>
                    <textarea name="description" class="communityTextarea" placeholder="Description..." rows="4" required></textarea>
                </div>

                <div class="communityFormGroup communityFileGroup">
                    <label for="recipeImageUpload" class="communityFileLabel">
                        <i class="fa-solid fa-image"></i> Choose Photo
                    </label>
                    <input type="file" name="image" id="recipeImageUpload" class="communityFileInput" required>
                </div>

                <button type="submit" name="add" class="communitySubmitBtn">
                    <i class="fa-solid fa-plus"></i> Add Recipe
                </button>
            </form>
        </section>

        <!-- Recipe List -->
        <h2 class="section-title">Recipe List</h2>

        <div class="communityFeedList">
            <?php if ($recipes->num_rows === 0): ?>
                <div class="communityPostCard" style="text-align: center;">
                    <p class="communityPostText">No recipes added yet.</p>
                </div>
            <?php else: ?>
                <?php while ($row = $recipes->fetch_assoc()): ?>

                    <article class="communityPostCard">

                        <div class="communityPostHeader">
                            <h3 class="communityPostTitle"><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p class="communityPostMeta">
                                <span><i class="fa-solid fa-bowl-food"></i> <?php echo htmlspecialchars($row['cuisine_type']); ?></span>
                                <?php if (!empty($row['dietary_preference'])): ?>
                                    &nbsp;|&nbsp; <span><i class="fa-solid fa-leaf"></i> <?php echo htmlspecialchars($row['dietary_preference']); ?></span>
                                <?php endif; ?>
                                &nbsp;|&nbsp; <span><i class="fa-solid fa-gauge"></i> <?php echo htmlspecialchars($row['difficulty']); ?></span>
                            </p>
                        </div>

                        <?php if (!empty($row['image'])): ?>
                            <div class="communityPostImageContainer">
                                <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" class="communityPostImg" alt="Recipe Image">
                            </div>
                        <?php endif; ?>

                        <div class="communityPostBody">
                            <p class="communityPostText"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                        </div>

                        <!-- Management Bar (Edit/Delete Links) -->
                        <div class="communityPostManageBar">
                            <a href="edit_recipe.php?id=<?php echo $row['id']; ?>" class="communityEditBtn">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </a>
                            <a href="delete_recipe.php?id=<?php echo $row['id']; ?>" 
                               onclick="return confirm('Delete this recipe?')" 
                               class="communityDeleteBtn">
                               <i class="fa-solid fa-trash-can"></i> Delete
                            </a>
                        </div>

                    </article>

                <?php endwhile; ?>
            <?php endif; ?>
        </div>

    </main>

</body>
</html>