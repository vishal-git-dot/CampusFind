<?php
require_once 'config.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Security check failed. Please refresh and try again.';
    }

    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        $errors[] = 'Please enter a name between 2 and 100 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = 'An account with this email already exists.';
        }
    }

    if (!$errors) {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);

            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            flash('success', 'Welcome to CampusFind! Your account is ready.');
            header('Location: dashboard.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Could not create the account. Please try again.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head><?php include 'partials/head.php'; ?><title>Create account — CampusFind</title></head>
<body class="auth-page">
<div class="auth-shell">
    <div class="auth-topbar">
        <a href="index.php" class="brand auth-brand"><span class="brand-mark">⌁</span><span>Campus<span>Find</span></span></a>
        <button class="theme-toggle" type="button" id="themeToggle" aria-label="Switch theme"><span id="themeIcon">☾</span><span id="themeLabel">Dark Mode</span></button>
    </div>
    <div class="auth-card">
        <div class="auth-heading"><span class="eyebrow">JOIN YOUR CAMPUS</span><h1>Create your account</h1><p>Post, search and reconnect lost belongings.</p></div>
        <?php include 'partials/form_errors.php'; ?>
        <form method="post" class="form-grid" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label>Full name<input type="text" name="name" maxlength="100" value="<?= e($_POST['name'] ?? '') ?>" required autocomplete="name"></label>
            <label>Email address<input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required autocomplete="email"></label>
            <label>Password<input type="password" name="password" required minlength="8" autocomplete="new-password"><small>At least 8 characters.</small></label>
            <label>Confirm password<input type="password" name="confirm_password" required minlength="8" autocomplete="new-password"></label>
            <button class="btn btn-primary btn-wide" type="submit">Create account →</button>
        </form>
        <p class="auth-switch">Already have an account? <a href="login.php">Log in</a></p>
    </div>
</div>
<script src="assets/app.js"></script>
</body>
</html>
