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
    // အောင်မြင်ပါက index.php သို့ success မက်ဆေ့ချ်နှင့်အတူ ပို့မည်
    header("Location: index.php?success=" . urlencode("Registration successful! You can now login."));
    exit();
} else {
    // အမှားရှိပါက (ဥပမာ- Email ထပ်နေပါက) index.php သို့ error မက်ဆေ့ချ်နှင့်အတူ ပို့မည်
    header("Location: index.php?error=" . urlencode("Email already exists. Please try again."));
    exit();
}
?>