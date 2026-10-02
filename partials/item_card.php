<a class="item-card reveal" href="item.php?id=<?= (int)$item['id'] ?>">
    <div class="item-image <?= empty($item['image']) ? 'placeholder' : '' ?>">
        <?php if (!empty($item['image'])): ?><img src="uploads/<?= e($item['image']) ?>" alt="<?= e($item['title']) ?>"><?php else: ?><span>⌁</span><?php endif; ?>
        <span class="type-badge <?= e($item['type']) ?>"><?= ucfirst(e($item['type'])) ?></span>
    </div>
    <div class="item-body">
        <div class="item-meta"><?= e($item['category_name']) ?> · <?= e(date('d M Y', strtotime($item['item_date']))) ?></div>
        <h3><?= e($item['title']) ?></h3>
        <p><?= e(mb_strimwidth($item['description'], 0, 90, '…')) ?></p>
        <div class="item-location">⌖ <?= e($item['location']) ?><span>↗</span></div>
    </div>
</a>
