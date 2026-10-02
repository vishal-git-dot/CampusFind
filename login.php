<?php
require_once 'config.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$redirect = safe_local_redirect($_GET['redirect'] ?? '', 'dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirect = safe_local_redirect($_POST['redirect'] ?? $redirect, 'dashboard.php');

    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Security check failed. Please refresh and try again.';
    }

    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($password === '') {
        $errors[] = 'Please enter your password.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Email or password is incorrect.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            flash('success', 'Welcome back!');
            header('Location: ' . $redirect);
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head><?php include 'partials/head.php'; ?><title>Log in — CampusFind</title></head>
<body class="auth-page">
<div class="auth-shell">
    <div class="auth-topbar">
        <a href="index.php" class="brand auth-brand"><span class="brand-mark">⌁</span><span>Campus<span>Find</span></span></a>
        <button class="theme-toggle" type="button" id="themeToggle" aria-label="Switch theme"><span id="themeIcon">☾</span><span id="themeLabel">Dark Mode</span></button>
    </div>
    <div class="auth-card">
        <div class="auth-heading"><span class="eyebrow">WELCOME BACK</span><h1>Log in to CampusFind</h1><p>Check your posts or help reunite an item.</p></div>
        <?php include 'partials/form_errors.php'; ?>
        <form method="post" class="form-grid" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
            <label>Email address<input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required autocomplete="email"></label>
            <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
            <button class="btn btn-primary btn-wide" type="submit">Log in →</button>
        </form>
        <p class="auth-switch">New here? <a href="register.php">Create an account</a></p>
    </div>
</div>
<script src="assets/app.js"></script>
</body>
</html>
