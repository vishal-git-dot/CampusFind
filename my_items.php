<?php
require_once 'config.php';
require_login();
$status=in_array($_GET['status'] ?? '', ['active','returned'], true) ? $_GET['status'] : '';
$where="i.user_id=?"; $params=[current_user_id()];
if($status){$where.=" AND i.status=?";$params[]=$status;}
$stmt=$pdo->prepare("SELECT i.*,c.name category_name FROM items i JOIN categories c ON c.id=i.category_id WHERE $where ORDER BY i.created_at DESC");
$stmt->execute($params);$items=$stmt->fetchAll();
?>
<!doctype html><html lang="en"><head><?php include 'partials/head.php'; ?><title>My posts — CampusFind</title></head><body>
<?php include 'partials/header.php'; ?><main class="section"><div class="container">
<div class="page-heading"><span class="eyebrow">MY POSTS</span><h1>Your listings.</h1><p>Manage active reports and keep returned items organised.</p></div>
<div class="filter-tabs"><a class="<?= !$status?'active':'' ?>" href="my_items.php">All</a><a class="<?= $status==='active'?'active':'' ?>" href="?status=active">Active</a><a class="<?= $status==='returned'?'active':'' ?>" href="?status=returned">Returned</a></div>
<?php if($items): ?><div class="item-grid"><?php foreach($items as $item): include 'partials/item_card.php'; endforeach; ?></div><?php else: ?><div class="empty-state"><div class="empty-icon">⌁</div><h3>No posts here</h3><p>Nothing matches this filter yet.</p><a class="btn btn-primary" href="post_item.php">Post an item</a></div><?php endif; ?>
</div></main><?php include 'partials/footer.php'; ?><script src="assets/app.js"></script></body></html>
