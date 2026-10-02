<?php
require_once 'config.php';
require_login();

$userId = current_user_id();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM items WHERE user_id=?"); $stmt->execute([$userId]); $myTotal=(int)$stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM items WHERE user_id=? AND type='lost' AND status='active'"); $stmt->execute([$userId]); $myLost=(int)$stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM items WHERE user_id=? AND status='returned'"); $stmt->execute([$userId]); $returned=(int)$stmt->fetchColumn();

$stmt=$pdo->prepare("SELECT i.*,c.name category_name FROM items i JOIN categories c ON c.id=i.category_id WHERE i.user_id=? ORDER BY i.created_at DESC LIMIT 5");
$stmt->execute([$userId]); $items=$stmt->fetchAll();
?>
<!doctype html><html lang="en"><head><?php include 'partials/head.php'; ?><title>Dashboard — CampusFind</title></head><body>
<?php include 'partials/header.php'; ?>
<main class="dashboard section"><div class="container">
    <div class="dashboard-head"><div><span class="eyebrow">YOUR CAMPUS SPACE</span><h1>Good to see you, <?= e($_SESSION['user_name']) ?>.</h1><p>Keep track of everything you've reported.</p></div><a class="btn btn-primary" href="post_item.php">+ Post item</a></div>
    <div class="metric-grid">
        <div class="metric-card"><span>All my posts</span><strong><?= $myTotal ?></strong><small>Across lost & found</small></div>
        <div class="metric-card"><span>Currently lost</span><strong><?= $myLost ?></strong><small>Active lost reports</small></div>
        <div class="metric-card"><span>Returned</span><strong><?= $returned ?></strong><small>Successfully closed</small></div>
    </div>
    <div class="section-heading"><div><span class="eyebrow">RECENT ACTIVITY</span><h2>Your posts</h2></div><a class="text-link" href="my_items.php">Manage all →</a></div>
    <?php if ($items): ?><div class="item-grid"><?php foreach($items as $item): include 'partials/item_card.php'; endforeach; ?></div>
    <?php else: ?><div class="empty-state"><div class="empty-icon">+</div><h3>Your first post starts here</h3><p>Tell the campus what you've lost or found.</p><a class="btn btn-primary" href="post_item.php">Create a post</a></div><?php endif; ?>
</div></main>
<?php include 'partials/footer.php'; ?><script src="assets/app.js"></script></body></html>
