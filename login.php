<?php
session_start();
include "db.php";

// Get the email and password entered by the user.
$email = trim($_POST['email']);
$password = trim($_POST['password']);

// Initialize the failed-attempt counter if it has not been set.
if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = 0;
    $_SESSION['lock_time'] = null;
}

// Check whether the session is locked for three minutes.
if ($_SESSION['failed_attempts'] >= 3) {
    $time_passed = time() - $_SESSION['lock_time'];
    if ($time_passed < 180) {
        header("Location: index.php?error=" . urlencode("Too many failed attempts. Please try again after 3 minutes.") . "&show_login=1");
        exit();
    } else {
        $_SESSION['failed_attempts'] = 0;
        $_SESSION['lock_time'] = null;
    }
}

// Query the database to check whether the user's email exists.
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$user = null;
if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();

    // Check whether the account is locked in the database.
    if ($user['failed_attempts'] >= 3) {
        $lock_sql = "SELECT TIMESTAMPDIFF(SECOND, last_failed_login, NOW()) AS seconds_passed FROM users WHERE email = ?";
        $lock_stmt = $conn->prepare($lock_sql);
        $lock_stmt->bind_param("s", $email);
        $lock_stmt->execute();
        $lock_data = $lock_stmt->get_result()->fetch_assoc();

        if ($lock_data['seconds_passed'] < 180) {
            header("Location: index.php?error=" . urlencode("Your account is locked for 3 minutes due to multiple failed attempts.") . "&show_login=1");
            exit();
        } else {
            // Reset the failed-attempt count after three minutes.
            $reset_db = "UPDATE users SET failed_attempts = 0, last_failed_login = NULL WHERE email = ?";
            $r_stmt = $conn->prepare($reset_db);
            $r_stmt->bind_param("s", $email);
            $r_stmt->execute();
            $user['failed_attempts'] = 0;
        }
    }

    // Verify whether the password is correct.
    if (password_verify($password, $user['password'])) {
        // Clear the failed-attempt records after a successful login.
        $clear_sql = "UPDATE users SET failed_attempts = 0, last_failed_login = NULL WHERE email = ?";
        $c_stmt = $conn->prepare($clear_sql);
        $c_stmt->bind_param("s", $email);
        $c_stmt->execute();

        // Set the session values.
        $_SESSION['failed_attempts'] = 0;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = $user['first_name'];
        $_SESSION['role'] = $user['role'];

        // Redirect based on the user's role.
        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php?success=" . urlencode("Welcome Admin, " . $user['first_name']));
        } else {
            header("Location: index.php?success=" . urlencode("Login successful! Welcome back, " . $user['first_name']));
        }
        exit();
    }
}

// Increment the failed-attempt count when the login details are incorrect.
$_SESSION['failed_attempts']++;
$current_attempts = $_SESSION['failed_attempts'];

if ($user !== null) {
    $db_attempts = $user['failed_attempts'] + 1;
    $update_db = "UPDATE users SET failed_attempts = ?, last_failed_login = NOW() WHERE email = ?";
    $u_stmt = $conn->prepare($update_db);
    $u_stmt->bind_param("is", $db_attempts, $email);
    $u_stmt->execute();
    if ($db_attempts >= 3) {
        $current_attempts = $db_attempts;
    }
}

// Lock the session after three failures; otherwise, show the remaining attempts.
if ($current_attempts >= 3) {
    $_SESSION['lock_time'] = time();
    header("Location: index.php?error=" . urlencode("Invalid login details. Your account/session is locked for 3 minutes.") . "&show_login=1");
} else {
    header("Location: index.php?error=" . urlencode("Invalid email or password. Attempt $current_attempts of 3.") . "&show_login=1");
}
exit();
?>
