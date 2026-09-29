<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: admin_recipes.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM recipes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$recipe = $stmt->get_result()->fetch_assoc();
if (!$recipe) {
    header("Location: admin_recipes.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $title = trim($_POST['title'] ?? '');
    $cuisine = trim($_POST['cuisine_type'] ?? '');
    $dietary = $_POST['dietary_preference'] ?? '';
    $difficulty = $_POST['difficulty'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $imageName = $recipe['image'] ?? '';

    if ($title === '' || $cuisine === '' || $description === '' || !in_array($difficulty, ['Easy', 'Medium', 'Hard'], true)) {
        $error = "Please complete the title, cuisine, description, and difficulty fields.";
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error = "The replacement image could not be uploaded. Please try again.";
        } else {
            $newImageName = time() . "_" . basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $newImageName)) {
                $imageName = $newImageName;
            } else {
                $error = "The replacement image could not be saved.";
            }
        }
    }

    if ($error === '') {
        $update = $conn->prepare("UPDATE recipes SET title = ?, cuisine_type = ?, dietary_preference = ?, difficulty = ?, description = ?, image = ? WHERE id = ?");
        $update->bind_param("ssssssi", $title, $cuisine, $dietary, $difficulty, $description, $imageName, $id);
        if ($update->execute()) {
            header("Location: admin_recipes.php");
            exit();
        }
        $error = "The recipe could not be updated. Please try again.";
    }

    $recipe['title'] = $title;
    $recipe['cuisine_type'] = $cuisine;
    $recipe['dietary_preference'] = $dietary;
    $recipe['difficulty'] = $difficulty;
    $recipe['description'] = $description;
    $recipe['image'] = $imageName;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recipe - FoodFusion</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="editEntityPage">
    <nav>
        <a href="index.php" class="brand-logo">FoodFusion</a>
        <div class="nav-links"><a href="admin_dashboard.php">Dashboard</a><a href="admin_recipes.php">Manage Recipes</a></div>
        <span class="user-area">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? $_SESSION['user'] ?? 'Admin'); ?><a href="logout.php" class="cta-btn">Logout</a></span>
    </nav>

    <section class="hero community-hero-override">
        <h1>Edit Recipe</h1>
        <p>Refresh the recipe details and update its photo for the collection.</p>
    </section>

    <main class="editEntityMain">
        <?php if ($error !== ''): ?>
            <div class="editEntityAlert"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <section class="communityPostCard editEntityCard">
            <div class="editEntityHeading">
                <span class="editEntityIcon"><i class="fa-solid fa-utensils"></i></span>
                <div><span class="editEntityEyebrow">RECIPE COLLECTION</span><h2><?php echo htmlspecialchars($recipe['title']); ?></h2></div>
            </div>

            <div class="editRecipeCurrentImage">
                <?php if (!empty($recipe['image'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($recipe['image']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                <?php else: ?>
                    <div><i class="fa-solid fa-image"></i><span>No current image</span></div>
                <?php endif; ?>
            </div>

            <form method="POST" enctype="multipart/form-data" class="communityCreateForm editEntityForm">
                <div class="editEntityField">
                    <label for="recipeTitle">Recipe title</label>
                    <input id="recipeTitle" type="text" name="title" class="communityInput" value="<?php echo htmlspecialchars($recipe['title']); ?>" required>
                </div>
                <div class="editRecipeFields">
                    <div class="editEntityField">
                        <label for="recipeCuisine">Cuisine type</label>
                        <input id="recipeCuisine" type="text" name="cuisine_type" class="communityInput" value="<?php echo htmlspecialchars($recipe['cuisine_type']); ?>" required>
                    </div>
                    <div class="editEntityField">
                        <label for="recipeDiet">Dietary preference <span class="editOptionalLabel">Optional</span></label>
                        <select id="recipeDiet" name="dietary_preference" class="communitySelect">
                            <option value="">No preference</option>
                            <?php foreach (['Vegetarian', 'Vegan', 'Halal', 'Gluten-Free', 'Keto'] as $diet): ?>
                                <option value="<?php echo htmlspecialchars($diet); ?>" <?php echo ($recipe['dietary_preference'] ?? '') === $diet ? 'selected' : ''; ?>><?php echo htmlspecialchars($diet); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="editEntityField">
                        <label for="recipeDifficulty">Difficulty</label>
                        <select id="recipeDifficulty" name="difficulty" class="communitySelect" required>
                            <?php foreach (['Easy', 'Medium', 'Hard'] as $level): ?>
                                <option value="<?php echo $level; ?>" <?php echo ($recipe['difficulty'] ?? 'Easy') === $level ? 'selected' : ''; ?>><?php echo $level; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="editEntityField">
                    <label for="recipeDescription">Description</label>
                    <textarea id="recipeDescription" name="description" class="communityTextarea" rows="5" required><?php echo htmlspecialchars($recipe['description']); ?></textarea>
                </div>
                <div class="editEntityField" data-file-field data-empty-text="No replacement image selected">
                    <label>Replace image <span class="editOptionalLabel">Optional</span></label>
                    <label class="editFilePicker"><i class="fa-solid fa-cloud-arrow-up"></i><span>Choose a new photo to replace the current image</span><input type="file" name="image" data-file-input accept="image/*"></label>
                    <div class="fileSelectionPreview" data-file-preview aria-live="polite">
                        <span class="fileSelectionIcon"><i class="fa-regular fa-image"></i></span>
                        <span class="fileSelectionText"><strong data-file-name>No replacement image selected</strong><small data-file-size>Your current image will be kept</small></span>
                    </div>
                </div>
                <div class="editEntityActions">
                    <a href="admin_recipes.php" class="editCancelBtn">Cancel</a>
                    <button type="submit" name="update" class="communitySubmitBtn"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
                </div>
            </form>
        </section>
    </main>
<script src="file-upload.js"></script>
</body>
</html>
