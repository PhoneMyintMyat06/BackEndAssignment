<?php
session_start();
include "db.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied.");
}

$id = $_GET['id'];

$sql = "DELETE FROM recipes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: admin_recipes.php");
exit();
// use exit() for safety + best practice
?>

<!-- PHP header() function က browser သို့ special instruction ပို့ -->