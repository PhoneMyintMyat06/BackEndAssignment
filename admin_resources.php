<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied. Admin only.");
}

/* ADD RESOURCE */
if (isset($_POST['add_resource'])) {

    $resource_category = $_POST['resource_category'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    $fileName = time() . "_" . basename($_FILES['resource_file']['name']);
    $tmpName = $_FILES['resource_file']['tmp_name'];
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    move_uploaded_file($tmpName, "uploads/resources/" . $fileName);

    $sql = "INSERT INTO resources 
            (resource_category, title, description, file_name, file_type)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $resource_category, $title, $description, $fileName, $fileType);
    $stmt->execute();

    header("Location: admin_resources.php");
    exit();
}

$resources = $conn->query("SELECT * FROM resources ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Resources</title>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="style.css">
</head>
<body id="communityPageBody">

    <!-- Hero Section -->
    <section class="hero community-hero-override">
        <h1>Admin - Manage Resources</h1>
        <p>Upload new educational or culinary files, organize resource categories, and manage downloads.</p>
    </section>

    <!-- Main Container -->
    <main class="communityMainWrapper">

        <!-- Upload Form Card -->
        <section class="communityPostCard communityCreateCard">
            <h2 class="communityFormTitle">
                <i class="fa-solid fa-folder-plus"></i> Upload New Resource
            </h2>

            <form method="POST" enctype="multipart/form-data" class="communityCreateForm">
                
                <div class="communityFormGroup">
                    <select name="resource_category" class="communitySelect" required>
                        <option value="">Select Resource Category</option>
                        <option value="Culinary">Culinary Resources</option>
                        <option value="Educational">Educational Resources</option>
                    </select>
                </div>

                <div class="communityFormGroup">
                    <input type="text" name="title" class="communityInput" placeholder="Resource Title" required>
                </div>

                <div class="communityFormGroup">
                    <textarea name="description" class="communityTextarea" placeholder="Description..." rows="4" required></textarea>
                </div>

                <div class="communityFormGroup communityFileGroup">
                    <label for="resourceFileUpload" class="communityFileLabel">
                        <i class="fa-solid fa-paperclip"></i> Choose File
                    </label>
                    <input type="file" name="resource_file" id="resourceFileUpload" class="communityFileInput" required>
                </div>

                <button type="submit" name="add_resource" class="communitySubmitBtn">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Upload Resource
                </button>

            </form>
        </section>

        <!-- Resources Feed List -->
        <h2 class="section-title">Uploaded Resources</h2>

        <div class="communityFeedList">
            <?php if ($resources->num_rows === 0): ?>
                <div class="communityPostCard" style="text-align: center;">
                    <p class="communityPostText">No resources uploaded yet.</p>
                </div>
            <?php else: ?>
                <?php while ($row = $resources->fetch_assoc()): ?>

                    <article class="communityPostCard">

                        <div class="communityPostHeader">
                            <h3 class="communityPostTitle"><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p class="communityPostMeta">
                                <span><i class="fa-solid fa-layer-group"></i> <strong>Category:</strong> <?php echo htmlspecialchars($row['resource_category']); ?></span>
                                &nbsp;|&nbsp;
                                <span><i class="fa-solid fa-file"></i> <strong>File:</strong> <?php echo htmlspecialchars($row['file_name']); ?></span>
                            </p>
                        </div>

                        <div class="communityPostBody">
                            <p class="communityPostText"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                        </div>

                        <!-- Management & Action Links -->
                        <div class="communityPostManageBar">
                            <a href="uploads/resources/<?php echo htmlspecialchars($row['file_name']); ?>" download class="communityEditBtn" style="color: #2B6CB0;">
                                <i class="fa-solid fa-download"></i> Download
                            </a>
                            <a href="edit_resource.php?id=<?php echo $row['id']; ?>" class="communityEditBtn">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </a>
                            <a href="delete_resource.php?id=<?php echo $row['id']; ?>" 
                               onclick="return confirm('Delete this resource?')" 
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