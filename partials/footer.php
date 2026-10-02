<footer class="footer">
    <div class="container footer-inner">
        <div><a class="brand" href="index.php"><span class="brand-mark">⌁</span><span>Campus<span>Find</span></span></a><p>Helping campus belongings find their way home.</p></div>
        <div class="footer-links"><a href="search.php">Browse</a><a href="post_item.php">Post an item</a><?php if (is_logged_in()): ?><a href="my_items.php">My posts</a><?php endif; ?></div>
        <div class="footer-note">Built for a connected campus.</div>
    </div>
</footer>
