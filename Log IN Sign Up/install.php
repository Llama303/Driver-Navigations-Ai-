<?php
/**
 * One-time setup: creates the SQLite database and tables.
 * Run this once in the browser: http://yoursite/Log%20IN%20Sign%20Up/install.php
 * Then delete or protect this file so it cannot be run again by others.
 */

define('DRIVE_NAV_AUTH', true);
require __DIR__ . '/config.php';

header('Content-Type: text/html; charset=utf-8');

$messages = [];
$ok = true;

try {
    $pdo = getDb();

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        )
    ");
    $messages[] = 'Table "users" is ready.';

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS password_reset_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            token TEXT NOT NULL UNIQUE,
            expires_at TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");
    $messages[] = 'Table "password_reset_tokens" is ready.';

    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_tokens_token ON password_reset_tokens(token)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_tokens_expires ON password_reset_tokens(expires_at)");
    $messages[] = 'Indexes created.';

} catch (Exception $e) {
    $ok = false;
    $messages[] = 'Error: ' . htmlspecialchars($e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drive-NAV Install</title>
    <style>
        body { font-family: sans-serif; max-width: 500px; margin: 2rem auto; padding: 1rem; }
        .ok { color: #2fa12f; }
        .err { color: #c00; }
        ul { list-style: none; padding: 0; }
        li { margin: 0.25rem 0; }
    </style>
</head>
<body>
    <h1>Drive-NAV database setup</h1>
    <?php if ($ok): ?>
        <p class="ok">Setup completed successfully.</p>
        <ul><?php foreach ($messages as $m): ?><li><?php echo htmlspecialchars($m); ?></li><?php endforeach; ?></ul>
        <p><a href="LogInPage.html">Go to Log In</a></p>
        <p><strong>Security:</strong> Delete or rename <code>install.php</code> so others cannot run it again.</p>
    <?php else: ?>
        <p class="err">Setup failed.</p>
        <ul><?php foreach ($messages as $m): ?><li><?php echo $m; ?></li><?php endforeach; ?></ul>
    <?php endif; ?>
</body>
</html>
