<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: admin_resources.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM resources WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resource = $stmt->get_result()->fetch_assoc();
if (!$resource) {
    header("Location: admin_resources.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $category = $_POST['resource_category'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $fileName = $resource['file_name'];
    $fileType = $resource['file_type'] ?? strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($category, ['Culinary', 'Educational'], true) || $title === '' || $description === '') {
        $error = "Please complete all required fields.";
    } elseif (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['resource_file']['error'] !== UPLOAD_ERR_OK) {
            $error = "The replacement file could not be uploaded. Please try again.";
        } else {
            $originalName = basename($_FILES['resource_file']['name']);
            $newFileName = time() . "_" . $originalName;
            $targetDirectory = "uploads/resources/";
            if (move_uploaded_file($_FILES['resource_file']['tmp_name'], $targetDirectory . $newFileName)) {
                $fileName = $newFileName;
                $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            } else {
                $error = "The replacement file could not be saved.";
            }
        }
    }

    if ($error === '') {
        $update = $conn->prepare("UPDATE resources SET resource_category = ?, title = ?, description = ?, file_name = ?, file_type = ? WHERE id = ?");
        $update->bind_param("sssssi", $category, $title, $description, $fileName, $fileType, $id);
        if ($update->execute()) {
            header("Location: admin_resources.php");
            exit();
        }
        $error = "The resource could not be updated. Please try again.";
    }

    $resource['resource_category'] = $category;
    $resource['title'] = $title;
    $resource['description'] = $description;
    $resource['file_name'] = $fileName;
    $resource['file_type'] = $fileType;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Resource - FoodFusion</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="editEntityPage">
    <nav>
        <a href="index.php" class="brand-logo">FoodFusion</a>
        <div class="nav-links"><a href="admin_dashboard.php">Dashboard</a><a href="admin_resources.php">Manage Resources</a></div>
        <span class="user-area">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? $_SESSION['user'] ?? 'Admin'); ?><a href="logout.php" class="cta-btn">Logout</a></span>
    </nav>

    <section class="hero community-hero-override">
        <h1>Edit Resource</h1>
        <p>Update the resource details or replace the file while keeping your library organized.</p>
    </section>

    <main class="editEntityMain">
        <?php if ($error !== ''): ?>
            <div class="editEntityAlert"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <section class="communityPostCard editEntityCard">
            <div class="editEntityHeading">
                <span class="editEntityIcon"><i class="fa-solid fa-file-pen"></i></span>
                <div><span class="editEntityEyebrow">RESOURCE LIBRARY</span><h2><?php echo htmlspecialchars($resource['title']); ?></h2></div>
            </div>

            <div class="editCurrentFile">
                <span class="editCurrentFileIcon"><i class="fa-solid fa-file-lines"></i></span>
                <div><small>Current file</small><strong><?php echo htmlspecialchars($resource['file_name']); ?></strong></div>
                <a href="uploads/resources/<?php echo rawurlencode($resource['file_name']); ?>" target="_blank" rel="noopener" class="editCurrentFileLink">Preview <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
            </div>

            <form method="POST" enctype="multipart/form-data" class="communityCreateForm editEntityForm">
                <div class="editEntityField">
                    <label for="resourceCategory">Resource category</label>
                    <select id="resourceCategory" name="resource_category" class="communitySelect" required>
                        <option value="Culinary" <?php echo $resource['resource_category'] === 'Culinary' ? 'selected' : ''; ?>>Culinary Resources</option>
                        <option value="Educational" <?php echo $resource['resource_category'] === 'Educational' ? 'selected' : ''; ?>>Educational Resources</option>
                    </select>
                </div>
                <div class="editEntityField">
                    <label for="resourceTitle">Resource title</label>
                    <input id="resourceTitle" type="text" name="title" class="communityInput" value="<?php echo htmlspecialchars($resource['title']); ?>" required>
                </div>
                <div class="editEntityField">
                    <label for="resourceDescription">Description</label>
                    <textarea id="resourceDescription" name="description" class="communityTextarea" rows="5" required><?php echo htmlspecialchars($resource['description']); ?></textarea>
                </div>
                <div class="editEntityField" data-file-field data-empty-text="No replacement file selected">
                    <label>Replace file <span class="editOptionalLabel">Optional</span></label>
                    <label class="editFilePicker"><i class="fa-solid fa-cloud-arrow-up"></i><span>Choose a new file to replace the current one</span><input type="file" name="resource_file" data-file-input></label>
                    <div class="fileSelectionPreview" data-file-preview aria-live="polite">
                        <span class="fileSelectionIcon"><i class="fa-regular fa-file"></i></span>
                        <span class="fileSelectionText"><strong data-file-name>No replacement file selected</strong><small data-file-size>Your current file will be kept</small></span>
                    </div>
                </div>
                <div class="editEntityActions">
                    <a href="admin_resources.php" class="editCancelBtn">Cancel</a>
                    <button type="submit" name="update" class="communitySubmitBtn"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
                </div>
            </form>
        </section>
    </main>
<script src="file-upload.js"></script>
</body>
</html>
