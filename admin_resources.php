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
<body id="communityPageBody" class="adminResourcesPage">

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
            <a href="manage_cookbook.php">Manage Cookbook</a>

            <div class="dropdown">
                <a href="#" class="active-nav">Manage Resources ▼</a>
                <div class="dropdown-content">
                    <a href="admin_recipes.php">Recipes Collection</a>
                    <a href="admin_resources.php" class="active-nav">Resources</a>
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
        <h1>Admin - Manage Resources</h1>
        <p>Upload new educational or culinary files, organize resource categories, and manage downloads.</p>
    </section>

    <!-- Main Container -->
    <main class="communityMainWrapper adminResourcesMain">

        <!-- Upload Form Card -->
        <section class="communityPostCard communityCreateCard adminResourcesFormCard">
            <h2 class="communityFormTitle">
                <i class="fa-solid fa-folder-plus"></i> Upload New Resource
            </h2>

            <form method="POST" enctype="multipart/form-data" class="communityCreateForm">
                
                <div class="communityFormGroup">
                    <label class="adminResourceLabel" for="resourceCategory">Resource category</label>
                    <select id="resourceCategory" name="resource_category" class="communitySelect" required>
                        <option value="">Select Resource Category</option>
                        <option value="Culinary">Culinary Resources</option>
                        <option value="Educational">Educational Resources</option>
                    </select>
                </div>

                <div class="communityFormGroup">
                    <label class="adminResourceLabel" for="resourceTitle">Resource title</label>
                    <input id="resourceTitle" type="text" name="title" class="communityInput" placeholder="Give this resource a clear title" required>
                </div>

                <div class="communityFormGroup">
                    <label class="adminResourceLabel" for="resourceDescription">Description</label>
                    <textarea id="resourceDescription" name="description" class="communityTextarea" placeholder="What will people learn or find in this file?" rows="4" required></textarea>
                </div>

                <div class="communityFormGroup communityFileGroup" data-file-field data-empty-text="No file selected">
                    <label for="resourceFileUpload" class="communityFileLabel">
                        <i class="fa-solid fa-paperclip"></i> Choose File
                    </label>
                    <input type="file" name="resource_file" id="resourceFileUpload" class="communityFileInput" data-file-input required>
                    <div class="fileSelectionPreview" data-file-preview aria-live="polite">
                        <span class="fileSelectionIcon"><i class="fa-regular fa-file"></i></span>
                        <span class="fileSelectionText"><strong data-file-name>No file selected</strong><small data-file-size>Choose a file to see its name and size</small></span>
                    </div>
                </div>

                <button type="submit" name="add_resource" class="communitySubmitBtn">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Upload Resource
                </button>

            </form>
        </section>

        <!-- Resources Feed List -->
        <div class="adminResourceListHeading">
            <div>
                <span class="adminResourceEyebrow">LIBRARY</span>
                <h2 class="section-title">Uploaded Resources</h2>
            </div>
            <span class="adminResourceListHint"><i class="fa-solid fa-folder-open"></i> Organize and update your files</span>
        </div>

        <div class="adminResourceGrid">
            <?php if ($resources->num_rows === 0): ?>
                <div class="communityPostCard" style="text-align: center;">
                    <p class="communityPostText">No resources uploaded yet.</p>
                </div>
            <?php else: ?>
                <?php while ($row = $resources->fetch_assoc()): ?>

                    <article class="adminResourceCard">
                        <div class="adminResourceCardTop">
                            <span class="adminResourceFileIcon"><i class="fa-solid fa-file-lines"></i></span>
                            <span class="adminResourceCategory"><?php echo htmlspecialchars($row['resource_category']); ?></span>
                        </div>
                        <div class="adminResourceCardContent">
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                            <div class="adminResourceFileName"><i class="fa-solid fa-paperclip"></i><span><?php echo htmlspecialchars($row['file_name']); ?></span></div>
                        </div>
                        <div class="adminResourceActions">
                            <a href="uploads/resources/<?php echo htmlspecialchars($row['file_name']); ?>" download class="adminResourceDownloadBtn">
                                <i class="fa-solid fa-download"></i> Download
                            </a>
                            <a href="edit_resource.php?id=<?php echo (int)$row['id']; ?>" class="adminResourceEditBtn" aria-label="Edit <?php echo htmlspecialchars($row['title']); ?>">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="delete_resource.php?id=<?php echo (int)$row['id']; ?>"
                               onclick="return confirm('Delete this resource?')"
                               class="adminResourceDeleteBtn" aria-label="Delete <?php echo htmlspecialchars($row['title']); ?>">
                               <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </div>
                    </article>

                <?php endwhile; ?>
            <?php endif; ?>
        </div>

    </main>

<script src="file-upload.js"></script>
</body>
</html>
