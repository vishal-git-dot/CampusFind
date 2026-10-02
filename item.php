<?php
require_once 'config.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
    http_response_code(404);
    exit('Item not found.');
}

$stmt = $pdo->prepare(
    'SELECT i.*, c.name AS category_name, u.name AS user_name, u.email AS user_email
     FROM items i
     JOIN categories c ON c.id = i.category_id
     JOIN users u ON u.id = i.user_id
     WHERE i.id = ?'
);
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    http_response_code(404);
    exit('Item not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!is_logged_in() || (int)$item['user_id'] !== current_user_id()) {
        http_response_code(403);
        exit('You are not allowed to change this listing.');
    }

    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        exit('Invalid request.');
    }

    if (($_POST['action'] ?? '') === 'return' && $item['status'] === 'active') {
        $update = $pdo->prepare("UPDATE items SET status='returned' WHERE id=? AND user_id=? AND status='active'");
        $update->execute([$id, current_user_id()]);
        flash('success', 'This listing has been marked as returned.');
        header('Location: item.php?id=' . $id);
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head><?php include 'partials/head.php'; ?><title><?= e($item['title']) ?> — CampusFind</title></head>
<body>
<?php include 'partials/header.php'; ?>
<main class="section">
    <div class="container">
        <a class="back-link" href="search.php">← Back to listings</a>
        <div class="detail-grid">
            <div class="detail-image <?= empty($item['image']) ? 'placeholder' : '' ?>">
                <?php if (!empty($item['image'])): ?>
                    <img src="uploads/<?= e($item['image']) ?>" alt="<?= e($item['title']) ?>">
                <?php else: ?>
                    <span aria-hidden="true">⌁</span>
                <?php endif; ?>
            </div>

            <article class="detail-card">
                <div class="detail-top">
                    <span class="type-badge <?= e($item['type']) ?>"><?= ucfirst(e($item['type'])) ?></span>
                    <span class="status-pill <?= e($item['status']) ?>"><?= ucfirst(e($item['status'])) ?></span>
                </div>
                <span class="eyebrow"><?= e($item['category_name']) ?></span>
                <h1><?= e($item['title']) ?></h1>
                <p class="detail-description"><?= nl2br(e($item['description'])) ?></p>

                <div class="detail-info">
                    <div><span>LOCATION</span><strong>⌖ <?= e($item['location']) ?></strong></div>
                    <div><span>DATE</span><strong><?= e(date('d F Y', strtotime($item['item_date']))) ?></strong></div>
                    <div><span>POSTED BY</span><strong><?= e($item['user_name']) ?></strong></div>
                </div>

                <?php if ($item['status'] === 'active'): ?>
                    <?php if (is_logged_in() && (int)$item['user_id'] === current_user_id()): ?>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="action" value="return">
                            <button class="btn btn-primary btn-wide" type="submit">✓ Mark as returned</button>
                        </form>
                    <?php elseif (is_logged_in()): ?>
                        <a class="btn btn-primary btn-wide" href="mailto:<?= e($item['user_email']) ?>?subject=<?= rawurlencode('CampusFind - ' . $item['title']) ?>">Contact poster →</a>
                    <?php else: ?>
                        <a class="btn btn-primary btn-wide" href="login.php?redirect=<?= rawurlencode('item.php?id=' . $id) ?>">Log in to contact poster →</a>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="returned-message">✓ This item has been marked as returned.</div>
                <?php endif; ?>
            </article>
        </div>
    </div>
</main>
<?php include 'partials/footer.php'; ?>
<script src="assets/app.js"></script>
</body>
</html>
