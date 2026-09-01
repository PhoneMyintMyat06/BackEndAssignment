<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ဝင်လာသော Data များကို ရယူခြင်း
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // Data များ အလွတ်ဖြစ်မနေရန် စစ်ဆေးခြင်း
    if (!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
        
        // Database ထဲသို့ ထည့်သွင်းခြင်း (created_at အတွက် NOW() ကို သုံးထားသည်)
        $sql = "INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $subject, $message);

        if ($stmt->execute()) {
            // အောင်မြင်ပါက Success မက်ဆေ့ချ်ဖြင့် ပြန်ပို့ရန်
            header("Location: contact.php?success=" . urlencode("Thank you! Your message has been sent successfully."));
        } else {
            // Error တက်ပါက ပြန်ပို့ရန်
            header("Location: contact.php?error=" . urlencode("Failed to send message. Please try again."));
        }
        exit();
    } else {
        header("Location: contact.php?error=" . urlencode("All fields are required."));
        exit();
    }
} else {
    header("Location: contact.php");
    exit();
}
?>