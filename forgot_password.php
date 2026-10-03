<?php
session_start();
require_once __DIR__ . '/db.php';

// Keep the simplified reset route available only on a local development host.
$host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
$host = preg_replace('/:\\d+$/', '', $host);
$isLocalhost = in_array($host, ['localhost', '127.0.0.1', '::1'], true);

if (empty($_SESSION['password_reset_csrf'])) {
    $_SESSION['password_reset_csrf'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';

if (!$isLocalhost) {
    $error = 'Password reset is not available from this address.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['password_reset_csrf'], $csrfToken)) {
        $error = 'Your session expired. Please refresh the page and try again.';
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'The password confirmation does not match.';
        } else {
            $userStmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $userStmt->bind_param('s', $email);
            $userStmt->execute();
            $user = $userStmt->get_result()->fetch_assoc();

            if (!$user) {
                $error = 'No account was found with that email address.';
            } else {
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare('UPDATE users SET password = ?, failed_attempts = 0, last_failed_login = NULL WHERE id = ?');
                $updateStmt->bind_param('si', $passwordHash, $user['id']);

                if ($updateStmt->execute()) {
                    $message = 'Your password has been changed. You can now log in with your new password.';
                    $_SESSION['password_reset_csrf'] = bin2hex(random_bytes(32));
                } else {
                    $error = 'The password could not be changed. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - FoodFusion</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="passwordResetPage">
    <main class="passwordResetCard">
        <a class="passwordResetBrand" href="index.php">FoodFusion</a>
        <span class="passwordResetIcon"><i class="fa-solid fa-lock"></i></span>
        <h1>Choose a new password</h1>
        <p class="passwordResetIntro">Enter the email linked to your account, then create a new password.</p>

        <?php if ($error !== ''): ?>
            <div class="passwordResetNotice error" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if ($message !== ''): ?>
            <div class="passwordResetNotice success" role="status"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
            <a class="passwordResetButton passwordResetSuccessAction" href="index.php?show_login=1">Continue to login</a>
        <?php else: ?>
            <form method="POST" action="forgot_password.php" class="passwordResetForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['password_reset_csrf'], ENT_QUOTES, 'UTF-8'); ?>">
                <label for="resetEmail">Email address</label>
                <input id="resetEmail" type="email" name="email" autocomplete="email" required maxlength="254" placeholder="you@example.com">
                <label for="newPassword">New password</label>
                <input id="newPassword" type="password" name="new_password" autocomplete="new-password" required placeholder="Enter a new password">
                <label for="confirmPassword">Confirm new password</label>
                <input id="confirmPassword" type="password" name="confirm_password" autocomplete="new-password" required placeholder="Confirm your new password">
                <button type="submit">Save new password</button>
            </form>
            <a class="passwordResetBack" href="index.php?show_login=1"><i class="fa-solid fa-arrow-left"></i> Back to login</a>
        <?php endif; ?>
    </main>
</body>
</html>