<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT * FROM resources
    WHERE resource_category = 'Educational'
    ORDER BY id DESC
");
$stmt->execute();
$resources = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educational Resources</title>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="style.css">
</head>
<body id="communityPageBody">

    <!-- Hero Section -->
    <section class="hero community-hero-override">
        <h1>Educational Resources</h1>
        <p>Download educational resources, infographics, and watch instructional videos.</p>
        
        <!-- Tab Navigation -->
        <div class="communityTabNav">
            <a href="culinary_resources.php" class="communityTabLink">
                <i class="fa-solid fa-utensils"></i> Culinary
            </a>
            <a href="educational_resources.php" class="communityTabLink communityTabActive">
                <i class="fa-solid fa-graduation-cap"></i> Educational
            </a>
        </div>
    </section>

    <!-- Main Container -->
    <main class="communityMainWrapper">

        <div class="communityFeedList">
            <?php if ($resources->num_rows === 0): ?>
                <div class="communityPostCard" style="text-align: center;">
                    <p class="communityPostText">No educational resources available yet.</p>
                </div>
            <?php else: ?>
                <?php while ($row = $resources->fetch_assoc()): ?>

                    <article class="communityPostCard">

                        <!-- Title & File Meta -->
                        <div class="communityPostHeader">
                            <h3 class="communityPostTitle"><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p class="communityPostMeta">
                                <span><i class="fa-solid fa-paperclip"></i> <?php echo htmlspecialchars($row['file_name']); ?></span>
                            </p>
                        </div>

                        <!-- Description Body -->
                        <div class="communityPostBody">
                            <p class="communityPostText"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                        </div>

                        <!-- Video Player Preview (If file is MP4) -->
                        <?php if (strtolower($row['file_type']) === 'mp4'): ?>
                            <div class="communityPostImageContainer" style="max-height: none; padding: 15px 0;">
                                <video width="100%" controls style="border-radius: 8px; max-height: 400px; background: #000;">
                                    <source src="uploads/resources/<?php echo htmlspecialchars($row['file_name']); ?>" type="video/mp4">
                                    Your browser does not support HTML video.
                                </video>
                            </div>
                        <?php endif; ?>

                        <!-- Action Bar / Download Button -->
                        <div class="communityPostManageBar">
                            <a href="uploads/resources/<?php echo htmlspecialchars($row['file_name']); ?>" download class="communityEditBtn" style="color: #FF4742;">
                                <i class="fa-solid fa-download"></i> Download Resource
                            </a>
                        </div>

                    </article>

                <?php endwhile; ?>
            <?php endif; ?>
        </div>

    </main>

</body>
</html>