<?php
session_start();
include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied.");
}

$id = $_GET['id'];

$stmt = $conn->prepare(
    "SELECT file_name FROM resources WHERE id=?"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

if($row) {

    $file = "uploads/resources/" . $row['file_name'];

    if(file_exists($file)) {
        unlink($file);
    }

    $stmt = $conn->prepare(
        "DELETE FROM resources WHERE id=?"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: admin_resources.php");
exit();
?>