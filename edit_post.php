<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$post_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$post_id) {
    header("Location: my_wall.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM community_posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
if (!$post) {
    header("Location: my_wall.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_post'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $imageName = $post['image'];

    if ($title === '' || $content === '') {
        $error = "Please enter a title and post content.";
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error = "The replacement photo could not be uploaded. Please try again.";
        } else {
            $newImageName = time() . "_" . basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $newImageName)) {
                $imageName = $newImageName;
            } else {
                $error = "The replacement photo could not be saved.";
            }
        }
    }

    if ($error === '') {
        $update = $conn->prepare("UPDATE community_posts SET title = ?, content = ?, image = ? WHERE id = ? AND user_id = ?");
        $update->bind_param("sssii", $title, $content, $imageName, $post_id, $user_id);
        if ($update->execute()) {
            header("Location: my_wall.php");
            exit();
        }
        $error = "Your post could not be updated. Please try again.";
    }

    $post['title'] = $title;
    $post['content'] = $content;
    $post['image'] = $imageName;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - FoodFusion</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="editEntityPage">
    <nav>
        <a href="index.php" class="brand-logo">FoodFusion</a>
        <div class="nav-links"><a href="my_wall.php">My Wall</a><a href="community_cookbook.php">Community Cookbook</a></div>
        <span class="user-area">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? $_SESSION['user'] ?? ''); ?><a href="logout.php" class="cta-btn">Logout</a></span>
    </nav>

    <section class="hero community-hero-override">
        <h1>Edit Your Post</h1>
        <p>Update your post and replace its photo if you like.</p>
    </section>

    <main class="editEntityMain">
        <?php if ($error !== ''): ?>
            <div class="editEntityAlert"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <section class="communityPostCard editEntityCard">
            <div class="editEntityHeading">
                <span class="editEntityIcon"><i class="fa-solid fa-pen-to-square"></i></span>
                <div><span class="editEntityEyebrow">MY WALL</span><h2><?php echo htmlspecialchars($post['title']); ?></h2></div>
            </div>

            <?php if (!empty($post['image'])): ?>
                <div class="editRecipeCurrentImage"><img src="uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>"></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="communityCreateForm editEntityForm">
                <div class="editEntityField">
                    <label for="postTitle">Post title</label>
                    <input id="postTitle" type="text" name="title" class="communityInput" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                </div>
                <div class="editEntityField">
                    <label for="postContent">Post content</label>
                    <textarea id="postContent" name="content" class="communityTextarea" rows="6" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                </div>
                <div class="editEntityField" data-file-field data-empty-text="No replacement photo selected">
                    <label>Replace photo <span class="editOptionalLabel">Optional</span></label>
                    <label class="editFilePicker"><i class="fa-solid fa-cloud-arrow-up"></i><span>Choose a new photo for this post</span><input type="file" name="image" data-file-input accept="image/*"></label>
                    <div class="fileSelectionPreview" data-file-preview aria-live="polite">
                        <span class="fileSelectionIcon"><i class="fa-regular fa-image"></i></span>
                        <span class="fileSelectionText"><strong data-file-name>No replacement photo selected</strong><small data-file-size>Your current photo will be kept</small></span>
                    </div>
                </div>
                <div class="editEntityActions">
                    <a href="my_wall.php" class="editCancelBtn">Cancel</a>
                    <button type="submit" name="update_post" class="communitySubmitBtn"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
                </div>
            </form>
        </section>
    </main>
    <script src="file-upload.js"></script>
</body>
</html>
