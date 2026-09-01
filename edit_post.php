<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = $_GET['id'];

$stmt = $conn->prepare("
    SELECT * FROM community_posts
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    die("Post not found or access denied.");
}

$post = $result->fetch_assoc();

/* UPDATE POST */
if (isset($_POST['update_post'])) {

    $title = $_POST['title'];
    $content = $_POST['content'];
    $imageName = $post['image'];

    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . basename($_FILES['image']['name']);
        $tmpName = $_FILES['image']['tmp_name'];
        move_uploaded_file($tmpName, "uploads/" . $imageName);
    }

    $sql = "UPDATE community_posts
            SET title = ?, content = ?, image = ?
            WHERE id = ? AND user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $title, $content, $imageName, $post_id, $user_id);
    $stmt->execute();

    header("Location: my_wall.php");
    exit();
}
?>

<h1>Edit Post</h1>

<form method="POST" enctype="multipart/form-data">

    <input type="text" name="title"
           value="<?php echo htmlspecialchars($post['title']); ?>" required>
    <br><br>

    <textarea name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>
    <br><br>

    <?php if (!empty($post['image'])): ?>
        <img src="uploads/<?php echo htmlspecialchars($post['image']); ?>" width="180">
        <br><br>
    <?php endif; ?>

    <input type="file" name="image">
    <br><br>

    <button type="submit" name="update_post">Update Post</button>

</form>