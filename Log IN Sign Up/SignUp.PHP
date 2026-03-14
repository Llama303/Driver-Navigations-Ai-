<?php
/**
 * Drive-NAV Sign Up handler.
 * Creates a new user in the database. Redirects with ?error= or ?success=.
 */

define('DRIVE_NAV_AUTH', true);
require __DIR__ . '/config.php';

$signUpPage = 'SignUpPage.html';
$loginPage = 'LogInPage.html';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['signup'])) {
    header('Location: ' . $signUpPage);
    exit;
}

$username = isset($_POST['username']) ? trim((string) $_POST['username']) : '';
$email = isset($_POST['email']) ? trim((string) $_POST['email']) : '';
$password = isset($_POST['psw']) ? (string) $_POST['psw'] : '';
$confirm = isset($_POST['cfmpsw']) ? (string) $_POST['cfmpsw'] : '';

// Validation
if ($username === '') {
    header('Location: ' . $signUpPage . '?error=' . rawurlencode('Please enter a username.'));
    exit;
}
if (strlen($username) < 2 || strlen($username) > 50) {
    header('Location: ' . $signUpPage . '?error=' . rawurlencode('Username must be 2–50 characters.'));
    exit;
}
if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
    header('Location: ' . $signUpPage . '?error=' . rawurlencode('Username may only contain letters, numbers, dots, underscores and hyphens.'));
    exit;
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . $signUpPage . '?error=' . rawurlencode('Please enter a valid email address.'));
    exit;
}
if (strlen($password) < 8) {
    header('Location: ' . $signUpPage . '?error=' . rawurlencode('Password must be at least 8 characters.'));
    exit;
}
if ($password !== $confirm) {
    header('Location: ' . $signUpPage . '?error=' . rawurlencode('Passwords do not match.'));
    exit;
}

try {
    $pdo = getDb();
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $hash]);
} catch (PDOException $e) {
    $code = $e->getCode();
    if (strpos($e->getMessage(), 'UNIQUE') !== false || $code == 23000) {
        if (strpos($e->getMessage(), 'username') !== false) {
            header('Location: ' . $signUpPage . '?error=' . rawurlencode('That username is already taken.'));
        } else {
            header('Location: ' . $signUpPage . '?error=' . rawurlencode('That email is already registered.'));
        }
        exit;
    }
    header('Location: ' . $signUpPage . '?error=' . rawurlencode('Something went wrong. Please try again.'));
    exit;
}

header('Location: ' . $loginPage . '?success=' . rawurlencode('Account created. You can log in now.'));
exit;
