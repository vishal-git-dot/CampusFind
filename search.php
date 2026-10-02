<?php
require_once 'config.php';
$q=trim($_GET['q'] ?? '');
$type=in_array($_GET['type'] ?? '', ['lost','found'], true) ? $_GET['type'] : '';
$category=(int)($_GET['category'] ?? 0);
$location=trim($_GET['location'] ?? '');

$where=["i.status='active'"]; $params=[];
if($q!==''){ $where[]="(i.title LIKE ? OR i.description LIKE ? OR i.location LIKE ?)"; $term="%{$q}%"; array_push($params,$term,$term,$term); }
if($type){$where[]="i.type=?";$params[]=$type;}
if($category){$where[]="i.category_id=?";$params[]=$category;}
if($location!==''){$where[]="i.location LIKE ?";$params[]="%{$location}%";}

$sql="SELECT i.*,c.name category_name FROM items i JOIN categories c ON c.id=i.category_id WHERE ".implode(' AND ',$where)." ORDER BY i.created_at DESC";
$stmt=$pdo->prepare($sql);$stmt->execute($params);$items=$stmt->fetchAll();
$categories=$pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<!doctype html><html lang="en"><head><?php include 'partials/head.php'; ?><title>Browse listings — CampusFind</title></head><body>
<?php include 'partials/header.php'; ?>
<main class="section"><div class="container">
    <div class="page-heading search-heading"><span class="eyebrow">CAMPUS DIRECTORY</span><h1>Find what you're looking for.</h1><p>Search active lost and found reports by item, category or location.</p></div>
    <form class="filter-panel" method="get">
        <div class="search-input"><span>⌕</span><input name="q" value="<?= e($q) ?>" placeholder="Search items, brands, details..."></div>
        <select name="type"><option value="">All types</option><option value="lost" <?= $type==='lost'?'selected':'' ?>>Lost</option><option value="found" <?= $type==='found'?'selected':'' ?>>Found</option></select>
        <select name="category"><option value="">All categories</option><?php foreach($categories as $cat): ?><option value="<?= $cat['id'] ?>" <?= $category===$cat['id']?'selected':'' ?>><?= e($cat['name']) ?></option><?php endforeach; ?></select>
        <input name="location" value="<?= e($location) ?>" placeholder="Location">
        <button class="btn btn-primary" type="submit">Search</button>
    </form>
    <div class="results-bar"><strong><?= count($items) ?> <?= count($items)===1?'listing':'listings' ?></strong><?php if($q||$type||$category||$location): ?><a href="search.php">Clear filters ×</a><?php endif; ?></div>
    <?php if($items): ?><div class="item-grid"><?php foreach($items as $item): include 'partials/item_card.php'; endforeach; ?></div>
    <?php else: ?><div class="empty-state"><div class="empty-icon">⌕</div><h3>No matching listings</h3><p>Try another keyword, location or category.</p><a class="btn btn-secondary" href="search.php">Reset search</a></div><?php endif; ?>
</div></main>
<?php include 'partials/footer.php'; ?><script src="assets/app.js"></script></body></html>
