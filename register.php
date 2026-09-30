<?php 
include "db.php";

$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$sql = "INSERT INTO users (first_name, last_name, email, password)
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $first_name, $last_name, $email, $password);

if ($stmt->execute()) {
    // On success, redirect to index.php with a success message.
    header("Location: index.php?success=" . urlencode("Registration successful! You can now login."));
    exit();
} else {
    // On failure (for example, if the email is already registered), redirect to index.php with an error message.
    header("Location: index.php?error=" . urlencode("Email already exists. Please try again."));
    exit();
}
?>
