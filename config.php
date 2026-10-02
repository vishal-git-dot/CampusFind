<?php
declare(strict_types=1);

// Start a secure session suitable for local XAMPP and HTTPS deployments.
if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$dbHost = 'localhost';
$dbName = 'campusfind';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed. Start MySQL in XAMPP and check config.php.');
}

function e(mixed $value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0;
}

function current_user_id(): ?int {
    return is_logged_in() ? (int)$_SESSION['user_id'] : null;
}

function require_login(): void {
    if (!is_logged_in()) {
        $request = $_SERVER['REQUEST_URI'] ?? '/';
        header('Location: login.php?redirect=' . rawurlencode($request));
        exit;
    }
}

function flash(string $type, string $message): void {
    $allowed = ['success', 'error', 'info'];
    if (!in_array($type, $allowed, true)) {
        $type = 'info';
    }
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array {
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return is_array($items) ? $items : [];
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool {
    $stored = $_SESSION['csrf_token'] ?? '';
    return is_string($token) && is_string($stored) && $stored !== '' && hash_equals($stored, $token);
}

function safe_local_redirect(?string $redirect, string $fallback = 'dashboard.php'): string {
    $redirect = trim((string)$redirect);
    if ($redirect === '' || preg_match('/^[a-z][a-z0-9+.-]*:/i', $redirect)) {
        return $fallback;
    }
    if (str_starts_with($redirect, '//')) {
        return $fallback;
    }
    if (str_starts_with($redirect, '/')) {
        return $redirect;
    }
    return $fallback;
}

function validate_date(string $date): bool {
    $parsed = DateTime::createFromFormat('Y-m-d', $date);
    return $parsed !== false && $parsed->format('Y-m-d') === $date;
}

function upload_image(array $file): ?string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed. Please try again.');
    }

    $size = (int)($file['size'] ?? 0);
    if ($size <= 0) {
        throw new RuntimeException('The uploaded image is empty.');
    }
    if ($size > 5 * 1024 * 1024) {
        throw new RuntimeException('Image must be 5 MB or smaller.');
    }

    $tmp = (string)($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        throw new RuntimeException('Invalid image upload.');
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only JPG, PNG, and WebP images are allowed.');
    }

    if (@getimagesize($tmp) === false) {
        throw new RuntimeException('The uploaded file is not a valid image.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $destination = __DIR__ . '/uploads/' . $filename;

    if (!move_uploaded_file($tmp, $destination)) {
        throw new RuntimeException('Could not save the uploaded image.');
    }

    return $filename;
}

function category_icon(string $name): string {
    return match ($name) {
        'Electronics' => '⌁',
        'ID Cards & Documents' => '▤',
        'Books & Notes' => '▥',
        'Bags & Accessories' => '◒',
        'Clothing' => '◌',
        'Keys' => '⌗',
        'Jewellery' => '◇',
        'Stationery' => '✎',
        'Sports' => '◉',
        default => '✦',
    };
}
