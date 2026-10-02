<?php
require_once 'config.php';

$stats = [
    'active' => (int)$pdo->query("SELECT COUNT(*) FROM items WHERE status='active'")->fetchColumn(),
    'lost' => (int)$pdo->query("SELECT COUNT(*) FROM items WHERE type='lost' AND status='active'")->fetchColumn(),
    'found' => (int)$pdo->query("SELECT COUNT(*) FROM items WHERE type='found' AND status='active'")->fetchColumn(),
];

$stmt = $pdo->query("
    SELECT i.*, c.name AS category_name
    FROM items i
    JOIN categories c ON c.id = i.category_id
    WHERE i.status='active'
    ORDER BY i.created_at DESC
    LIMIT 6
");
$recentItems = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$flashes = get_flashes();
?>
<!doctype html>
<html lang="en">
<head>
    <?php include 'partials/head.php'; ?>
    <title>CampusFind — Lost & Found</title>
</head>
<body>
<?php include 'partials/header.php'; ?>

<main>
<section class="hero section">
    <div class="container hero-grid">
        <div class="hero-copy reveal">
            <span class="eyebrow"><span class="status-dot"></span> Campus lost & found</span>
            <h1>Lost something?<br><span>Let campus help.</span></h1>
            <p class="hero-text">A simple, trusted place for students to report lost items, share found belongings, and reconnect things with their owners.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="post_item.php">Post an item <span>→</span></a>
                <a class="btn btn-secondary" href="search.php">Browse listings</a>
            </div>
            <div class="hero-stats">
                <div><strong><?= $stats['active'] ?></strong><span>Active posts</span></div>
                <div><strong><?= $stats['lost'] ?></strong><span>Items lost</span></div>
                <div><strong><?= $stats['found'] ?></strong><span>Items found</span></div>
            </div>
        </div>

        <div class="hero-visual reveal delay-1" aria-hidden="true">
            <div class="search-card">
                <div class="search-card-top">
                    <span class="mini-label">QUICK SEARCH</span>
                    <span class="spark">✦</span>
                </div>
                <div class="mock-search"><span>⌕</span> Search laptop, ID card, keys...</div>
                <div class="mock-tags">
                    <span>Electronics</span><span>Library</span><span>Today</span>
                </div>
                <div class="found-preview">
                    <div class="mock-thumb">🎧</div>
                    <div><b>Wireless headphones</b><small>Found · Main Library</small></div>
                    <span class="arrow">↗</span>
                </div>
            </div>
            <div class="floating-note note-one">✓ Returned item</div>
            <div class="floating-note note-two">📍 Library</div>
        </div>
    </div>
</section>

<section class="section soft-section">
    <div class="container">
        <div class="section-heading">
            <div><span class="eyebrow">RECENTLY POSTED</span><h2>See what's moving around campus.</h2></div>
            <a class="text-link" href="search.php">View all listings →</a>
        </div>

        <?php if ($recentItems): ?>
        <div class="item-grid">
            <?php foreach ($recentItems as $item): ?>
                <?php include 'partials/item_card.php'; ?>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <div class="empty-state"><div class="empty-icon">⌁</div><h3>No active listings yet</h3><p>Be the first to help someone find what they're missing.</p><a class="btn btn-primary" href="post_item.php">Create a listing</a></div>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-heading centered"><div><span class="eyebrow">BROWSE BY CATEGORY</span><h2>Find it faster.</h2></div></div>
        <div class="category-grid">
            <?php foreach ($categories as $category): ?>
                <a class="category-card" href="search.php?category=<?= (int)$category['id'] ?>">
                    <span class="category-icon"><?= category_icon($category['name']) ?></span>
                    <span><?= e($category['name']) ?></span><span class="category-arrow">↗</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="cta-section section">
    <div class="container cta-card">
        <div><span class="eyebrow">HELP THE NEXT PERSON</span><h2>Found something that isn't yours?</h2><p>Post it with the location and date. The right person may already be looking.</p></div>
        <a class="btn btn-light" href="post_item.php?type=found">Report a found item →</a>
    </div>
</section>
</main>

<?php include 'partials/footer.php'; ?>
<script src="assets/app.js"></script>
</body>
</html>

