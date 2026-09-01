<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied.");
}

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM resources WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resource = $stmt->get_result()->fetch_assoc();

if(isset($_POST['update'])) {

    $category = $_POST['resource_category'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    $fileName = $resource['file_name'];

    if(!empty($_FILES['resource_file']['name'])) {

        $fileName = time() . "_" .
                    basename($_FILES['resource_file']['name']);

        move_uploaded_file(
            $_FILES['resource_file']['tmp_name'],
            "uploads/resources/" . $fileName
        );
    }

    $sql = "UPDATE resources
            SET resource_category=?,
                title=?,
                description=?,
                file_name=?
            WHERE id=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssi",
        $category,
        $title,
        $description,
        $fileName,
        $id
    );

    $stmt->execute();

    header("Location: admin_resources.php");
    exit();
}
?>

<h1>Edit Resource</h1>

<form method="POST" enctype="multipart/form-data">

<select name="resource_category" required>
    <option value="Culinary"
        <?php if($resource['resource_category']=="Culinary") echo "selected"; ?>>
        Culinary
    </option>

    <option value="Educational"
        <?php if($resource['resource_category']=="Educational") echo "selected"; ?>>
        Educational
    </option>
</select>

<br><br>

<input type="text"
       name="title"
       value="<?php echo htmlspecialchars($resource['title']); ?>"
       required>

<br><br>

<textarea name="description" required><?php
echo htmlspecialchars($resource['description']);
?></textarea>

<br><br>

<input type="file" name="resource_file">

<br><br>

<button type="submit" name="update">
    Update Resource
</button>

</form>