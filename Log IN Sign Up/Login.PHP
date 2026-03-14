<?php
/**
 * Drive-NAV Login handler.
 * Verifies credentials against the database. Redirects with ?error= on failure.
 */

define('DRIVE_NAV_AUTH', true);
require __DIR__ . '/config.php';

session_start();
$loginPage = 'LogInPage.html';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['login'])) {
    header('Location: ' . $loginPage);
    exit;
}

$username = isset($_POST['username']) ? trim((string) $_POST['username']) : '';
$password = isset($_POST['password']) ? (string) $_POST['password'] : '';

if ($username === '' || $password === '') {
    header('Location: ' . $loginPage . '?error=' . rawurlencode('Please enter both username and password.'));
    exit;
}

try {
    $pdo = getDb();
    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        header('Location: ' . $loginPage . '?error=' . rawurlencode('Invalid username or password.'));
        exit;
    }

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user'] = $user['username'];
    $_SESSION['logged_in_at'] = time();

    header('Location: ../index.html');
    exit;
} catch (PDOException $e) {
    header('Location: ' . $loginPage . '?error=' . rawurlencode('Something went wrong. Please try again.'));
    exit;
}
