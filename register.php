<?php
include "db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$plainPassword = $_POST['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: index.php?error=" . urlencode("Please enter a valid email address."));
    exit();
}

if ($first_name === '' || $last_name === '' || $plainPassword === '') {
    header("Location: index.php?error=" . urlencode("Please complete all required fields."));
    exit();
}

$password = password_hash($plainPassword, PASSWORD_DEFAULT);
$sql = "INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $first_name, $last_name, $email, $password);

if ($stmt->execute()) {
    header("Location: index.php?success=" . urlencode("Registration successful! You can now login."));
    exit();
}

header("Location: index.php?error=" . urlencode("This email is already registered. Please try another one."));
exit();
?>