<header class="site-header">
    <div class="container nav">
        <a href="index.php" class="brand" aria-label="CampusFind home">
            <span class="brand-mark">⌁</span>
            <span>Campus<span>Find</span></span>
        </a>

        <nav class="desktop-nav" aria-label="Primary navigation">
            <a href="index.php">Home</a>
            <a href="search.php">Browse</a>
            <?php if (is_logged_in()): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="my_items.php">My posts</a>
            <?php endif; ?>
        </nav>

        <div class="nav-actions">
            <button class="theme-toggle" type="button" id="themeToggle" aria-label="Switch theme">
                <span id="themeIcon">☾</span><span id="themeLabel">Dark Mode</span>
            </button>

            <?php if (is_logged_in()): ?>
                <a class="btn btn-primary nav-post" href="post_item.php">+ Post item</a>
                <a class="avatar-link" href="dashboard.php" aria-label="Open dashboard">
                    <?= e(strtoupper(mb_substr($_SESSION['user_name'] ?? 'U', 0, 1))) ?>
                </a>
                <a class="logout-link" href="logout.php">Logout</a>
            <?php else: ?>
                <a class="btn btn-secondary nav-login" href="login.php">Log in</a>
                <a class="btn btn-primary nav-post" href="register.php">Join campus</a>
            <?php endif; ?>

            <button class="mobile-menu-btn" id="mobileMenuBtn" type="button" aria-label="Open menu" aria-expanded="false">☰</button>
        </div>
    </div>

    <div class="mobile-menu" id="mobileMenu">
        <a href="index.php">Home</a>
        <a href="search.php">Browse listings</a>
        <?php if (is_logged_in()): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="my_items.php">My posts</a>
            <a href="post_item.php">+ Post item</a>
            <a class="mobile-logout" href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Log in</a>
            <a href="register.php">Create account</a>
        <?php endif; ?>
    </div>
</header>

<?php foreach (get_flashes() as $flash): ?>
<div class="toast toast-<?= e($flash['type']) ?>" data-toast role="status">
    <span><?= e($flash['message']) ?></span>
    <button type="button" aria-label="Dismiss notification" onclick="this.parentElement.remove()">×</button>
</div>
<?php endforeach; ?>

<?php if (isset($_GET['logged_out'])): ?>
<div class="toast toast-success" data-toast role="status">
    <span>You have been logged out successfully.</span>
    <button type="button" aria-label="Dismiss notification" onclick="this.parentElement.remove()">×</button>
</div>
<?php endif; ?>
