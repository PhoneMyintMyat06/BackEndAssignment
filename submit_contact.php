<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the submitted data.
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // Check that the fields are not empty.
    if (!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
        
        // Insert the data into the database (NOW() sets created_at).
        $sql = "INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $subject, $message);

        if ($stmt->execute()) {
            // Redirect with a success message.
            header("Location: contact.php?success=" . urlencode("Thank you! Your message has been sent successfully."));
        } else {
            // Redirect with an error message.
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
