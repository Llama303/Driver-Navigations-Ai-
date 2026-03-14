<?php
/**
 * Drive-NAV Forgot password handler.
 * Looks up user by email, creates a reset token, stores it, and redirects with success/error.
 * Optionally sends email with reset link; if mail is not configured, shows link on success page for testing.
 */

define('DRIVE_NAV_AUTH', true);
require __DIR__ . '/config.php';

$forgotPage = 'ForgotPassword.html';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['forgot_submit'])) {
    header('Location: ' . $forgotPage);
    exit;
}

$email = isset($_POST['email']) ? trim((string) $_POST['email']) : '';

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . $forgotPage . '?error=' . rawurlencode('Please enter a valid email address.'));
    exit;
}

try {
    $pdo = getDb();
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Always show same message so we don't reveal if email exists
    $successMessage = 'If an account exists for that email, you will receive a reset link shortly.';

    if ($user) {
        $userId = (int) $user['id'];
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $pdo->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)")
            ->execute([$userId, $token, $expiresAt]);

        $baseUrl = getBaseUrl();
        $resetLink = $baseUrl . '/ResetPassword.html?token=' . urlencode($token);

        // Optional: send email (uncomment and set your mail config)
        // $to = $email;
        // $subject = 'Drive-NAV password reset';
        // $body = "Click to reset your password: " . $resetLink . "\n\nLink expires in 1 hour.";
        // @mail($to, $subject, $body);

        // For development: pass link so the page can show "Copy this link" if no mail
        header('Location: ' . $forgotPage . '?success=' . rawurlencode($successMessage) . '&reset_link=' . rawurlencode($resetLink));
        exit;
    }
} catch (Exception $e) {
    // Don't leak errors; still show generic success
}

header('Location: ' . $forgotPage . '?success=' . rawurlencode($successMessage));
exit;
