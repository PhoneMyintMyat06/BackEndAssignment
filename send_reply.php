<?php
session_start();
include "db.php";

// Check whether the user is an admin.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $reply = trim($_POST['reply']);

    if (!empty($reply)) {
        $stmt = $conn->prepare("UPDATE contact_messages SET reply = ?, status = 'Replied', replied_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $reply, $id);
        
        if ($stmt->execute()) {
            header("Location: admin_contact.php?success=" . urlencode("Reply sent successfully."));
        } else {
            header("Location: admin_contact.php?error=" . urlencode("Failed to send reply."));
        }
        $stmt->close();
    } else {
        header("Location: admin_contact.php?error=" . urlencode("Reply cannot be empty."));
    }
}
?>
