<?php
session_start();
require_once __DIR__ . '/db.php';

if (empty($_SESSION['password_reset_csrf'])) {
    $_SESSION['password_reset_csrf'] = bin2hex(random_bytes(32));
}

header('Referrer-Policy: no-referrer');
$token = trim($_POST['token'] ?? $_GET['token'] ?? '');
$tokenHash = preg_match('/\A[a-f0-9]{64}\z/', $token) ? hash('sha256', $token) : '';
$message = '';
$error = '';
$resetComplete = false;
$tokenIsValid = false;
$userId = 0;

if ($tokenHash !== '') {
    $tokenStmt = $conn->prepare('SELECT id, user_id FROM password_reset_tokens WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW() LIMIT 1');
    $tokenStmt->bind_param('s', $tokenHash);
    $tokenStmt->execute();
    $tokenRecord = $tokenStmt->get_result()->fetch_assoc();
    if ($tokenRecord) {
        $tokenIsValid = true;
        $userId = (int)$tokenRecord['user_id'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!hash_equals($_SESSION['password_reset_csrf'], $csrfToken)) {
        $error = 'Your session expired. Please refresh the page and try again.';
    } elseif (!$tokenIsValid) {
        $error = 'This reset link is invalid or expired. Request a new one to continue.';
    } elseif (strlen($newPassword) < 8) {
        $error = 'Choose a password with at least 8 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'The passwords do not match.';
    } else {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $conn->begin_transaction();
        try {
            $updateStmt = $conn->prepare('UPDATE users SET password = ?, failed_attempts = 0, last_failed_login = NULL WHERE id = ?');
            $updateStmt->bind_param('si', $passwordHash, $userId);
            $updateStmt->execute();

            $deleteStmt = $conn->prepare('DELETE FROM password_reset_tokens WHERE user_id = ?');
            $deleteStmt->bind_param('i', $userId);
            $deleteStmt->execute();

            $conn->commit();
            $_SESSION['failed_attempts'] = 0;
            $_SESSION['lock_time'] = null;
            $resetComplete = true;
            $tokenIsValid = false;
            $message = 'Your password has been changed. You can now log in with your new password.';
        } catch (Throwable $exception) {
            $conn->rollback();
            error_log('FoodFusion password reset failed: ' . $exception->getMessage());
            $error = 'We could not update your password. Please request a new reset link.';
            $tokenIsValid = false;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="no-referrer">
    <title>Reset Password - FoodFusion</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="passwordResetPage">
    <main class="passwordResetCard">
        <a class="passwordResetBrand" href="index.php">FoodFusion</a>
        <span class="passwordResetIcon"><i class="fa-solid fa-lock"></i></span>
        <h1>Set a new password</h1>

        <?php if ($message !== ''): ?>
            <div class="passwordResetNotice success" role="status"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="passwordResetNotice error" role="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($resetComplete): ?>
            <a class="passwordResetButton" href="index.php?show_login=1">Continue to login</a>
        <?php elseif ($tokenIsValid): ?>
            <p class="passwordResetIntro">Choose a new password with at least 8 characters.</p>
            <form method="POST" action="reset_password.php" class="passwordResetForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['password_reset_csrf']); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <label for="newPassword">New password</label>
                <input id="newPassword" type="password" name="new_password" autocomplete="new-password" minlength="8" required>
                <label for="confirmPassword">Confirm new password</label>
                <input id="confirmPassword" type="password" name="confirm_password" autocomplete="new-password" minlength="8" required>
                <button type="submit">Update password</button>
            </form>
        <?php else: ?>
            <p class="passwordResetIntro">This reset link is invalid or expired. Request a new link to continue.</p>
            <a class="passwordResetButton" href="forgot_password.php">Request another link</a>
        <?php endif; ?>
    </main>
</body>
</html>
