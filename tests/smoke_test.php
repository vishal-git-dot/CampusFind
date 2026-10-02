<?php
// CLI-only static smoke test. Run from the project root with: php tests/smoke_test.php
// Full browser/database testing must still be performed in XAMPP because this environment
// does not provide a MySQL PDO driver.

declare(strict_types=1);

$root = dirname(__DIR__);
$phpFiles = glob($root . '/*.php') ?: [];
$phpFiles = array_merge($phpFiles, glob($root . '/partials/*.php') ?: []);

$failed = [];
$passed = [];

foreach ($phpFiles as $file) {
    $output = [];
    $code = 0;
    exec('php -l ' . escapeshellarg($file), $output, $code);
    if ($code === 0) {
        $passed[] = 'PHP syntax: ' . str_replace($root . DIRECTORY_SEPARATOR, '', $file);
    } else {
        $failed[] = 'PHP syntax: ' . str_replace($root . DIRECTORY_SEPARATOR, '', $file);
    }
}

$required = [
    'index.php', 'login.php', 'register.php', 'logout.php', 'dashboard.php',
    'post_item.php', 'item.php', 'search.php', 'my_items.php', 'config.php',
    'database.sql', 'assets/style.css', 'assets/app.js', 'uploads/.htaccess'
];
foreach ($required as $relative) {
    if (is_file($root . '/' . $relative)) {
        $passed[] = 'Required file: ' . $relative;
    } else {
        $failed[] = 'Missing file: ' . $relative;
    }
}

$checks = [
    'logout.php' => ['header(\'Location: index.php?logged_out=1\')', 'session_destroy'],
    'post_item.php' => ["\$_GET['type'] ?? 'lost'", "\$_POST['type'] ?? 'lost'", 'verify_csrf'],
    'login.php' => ['password_verify', 'session_regenerate_id', 'safe_local_redirect'],
    'register.php' => ['password_hash', 'session_regenerate_id', 'confirm_password'],
    'item.php' => ['status=\'returned\'', 'verify_csrf', 'Contact poster'],
];
foreach ($checks as $file => $needles) {
    $contents = file_get_contents($root . '/' . $file);
    foreach ($needles as $needle) {
        if ($contents !== false && strpos($contents, $needle) !== false) {
            $passed[] = "Feature check: {$file} contains {$needle}";
        } else {
            $failed[] = "Feature check failed: {$file} missing {$needle}";
        }
    }
}

foreach ($passed as $line) echo "[PASS] {$line}\n";
foreach ($failed as $line) echo "[FAIL] {$line}\n";

echo "\nSummary: " . count($passed) . ' passed, ' . count($failed) . " failed.\n";
exit($failed ? 1 : 0);
