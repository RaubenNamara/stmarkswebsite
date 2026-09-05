<section class="page-header">
    <h1>Posts</h1>
</section>

<section class="section">
    <?php if (empty($posts)): ?>
    <div class="empty-state">No posts yet.</div>
    <?php else: ?>
    <div class="listing-grid">
        <?php foreach ($posts as $post): ?>
        <div class="listing-card">
            <div class="body">
                <h3><a href="/posts/<?= e($post['slug']) ?>"><?= e($post['title']) ?></a></h3>
                <p><?= e(mb_strimwidth(strip_tags($post['content']), 0, 160, '...')) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
