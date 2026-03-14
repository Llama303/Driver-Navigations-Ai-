<?php
/**
 * Drive-NAV Reset password handler.
 * Validates token, updates user password, deletes token, redirects to login.
 */

define('DRIVE_NAV_AUTH', true);
require __DIR__ . '/config.php';

$resetPage = 'ResetPassword.html';
$loginPage = 'LogInPage.html';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['reset_submit'])) {
    header('Location: ' . $resetPage);
    exit;
}

$token = isset($_POST['token']) ? trim((string) $_POST['token']) : '';
$newPassword = isset($_POST['new_password']) ? (string) $_POST['new_password'] : '';
$confirmPassword = isset($_POST['confirm_password']) ? (string) $_POST['confirm_password'] : '';

if ($token === '') {
    header('Location: ' . $resetPage . '?error=' . rawurlencode('Invalid or missing reset link. Request a new one.'));
    exit;
}
if (strlen($newPassword) < 8) {
    header('Location: ' . $resetPage . '?token=' . rawurlencode($token) . '&error=' . rawurlencode('Password must be at least 8 characters.'));
    exit;
}
if ($newPassword !== $confirmPassword) {
    header('Location: ' . $resetPage . '?token=' . rawurlencode($token) . '&error=' . rawurlencode('Passwords do not match.'));
    exit;
}

try {
    $pdo = getDb();
    $stmt = $pdo->prepare("
        SELECT prt.user_id FROM password_reset_tokens prt
        WHERE prt.token = ? AND prt.expires_at > datetime('now')
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $row = $stmt->fetch();

    if (!$row) {
        header('Location: ' . $resetPage . '?error=' . rawurlencode('This reset link is invalid or has expired. Request a new one from Forgot password.'));
        exit;
    }

    $userId = (int) $row['user_id'];
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);

    $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $userId]);
    $pdo->prepare("DELETE FROM password_reset_tokens WHERE user_id = ? AND token = ?")->execute([$userId, $token]);

    header('Location: ' . $loginPage . '?success=' . rawurlencode('Password updated. You can log in with your new password.'));
    exit;
} catch (Exception $e) {
    header('Location: ' . $resetPage . '?token=' . rawurlencode($token) . '&error=' . rawurlencode('Something went wrong. Please try again.'));
    exit;
}
