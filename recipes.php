<?php
session_start();
include "db.php";

$search = $_GET['search'] ?? "";

$sql = "SELECT * FROM recipes
        WHERE title LIKE ?
        OR cuisine_type LIKE ?
        OR dietary_preference LIKE ?
        OR difficulty LIKE ?
        ORDER BY id DESC";

$stmt = $conn->prepare($sql);

$keyword = "%$search%";
$stmt->bind_param("ssss", $keyword, $keyword, $keyword, $keyword);
$stmt->execute();

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Collection - FoodFusion</title>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="style.css">
</head>
<body id="communityPageBody">

    <!-- Hero Section -->
    <section class="hero community-hero-override">
        <h1>Recipe Collection</h1>
        <p>Explore our curated recipes, search by cuisine, dietary needs, or preparation difficulty to find your next meal.</p>
    </section>

    <!-- Main Container -->
    <main class="communityMainWrapper">

        <!-- Search Bar Card -->
        <section class="communityPostCard communityCreateCard" style="padding: 24px 32px;">
            <form method="GET" class="communitySearchForm">
                <div class="communitySearchInputGroup">
                    <i class="fa-solid fa-magnifying-glass communitySearchIcon"></i>
                    <input type="text" name="search" class="communityInput communitySearchInput" placeholder="Search recipe, cuisine, diet, difficulty..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="communitySubmitBtn communitySearchBtn">Search</button>
                </div>
            </form>
        </section>

        <!-- Recipe List -->
        <h2 class="section-title">
            <?php echo !empty($search) ? 'Search Results for "' . htmlspecialchars($search) . '"' : 'All Recipes'; ?>
        </h2>

        <div class="communityFeedList">
            <?php if ($result->num_rows === 0): ?>
                <div class="communityPostCard" style="text-align: center;">
                    <p class="communityPostText">No recipes found matching your search criteria.</p>
                </div>
            <?php else: ?>
                <?php while ($row = $result->fetch_assoc()): ?>

                    <article class="communityPostCard">

                        <div class="communityPostHeader">
                            <h3 class="communityPostTitle"><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p class="communityPostMeta">
                                <span><i class="fa-solid fa-bowl-food"></i> <strong>Cuisine:</strong> <?php echo htmlspecialchars($row['cuisine_type']); ?></span>
                                <?php if (!empty($row['dietary_preference'])): ?>
                                    &nbsp;|&nbsp; <span><i class="fa-solid fa-leaf"></i> <strong>Diet:</strong> <?php echo htmlspecialchars($row['dietary_preference']); ?></span>
                                <?php endif; ?>
                                &nbsp;|&nbsp; <span><i class="fa-solid fa-gauge"></i> <strong>Difficulty:</strong> <?php echo htmlspecialchars($row['difficulty']); ?></span>
                            </p>
                        </div>

                        <?php if (!empty($row['image'])): ?>
                            <div class="communityPostImageContainer">
                                <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" class="communityPostImg" alt="<?php echo htmlspecialchars($row['title']); ?>">
                            </div>
                        <?php endif; ?>

                        <div class="communityPostBody">
                            <p class="communityPostText"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                        </div>

                    </article>

                <?php endwhile; ?>
            <?php endif; ?>
        </div>

    </main>

</body>
</html>