<section class="page-header">
    <h1>Clubs</h1>
</section>

<section class="section">
    <?php if (empty($clubs)): ?>
    <div class="empty-state">No clubs yet.</div>
    <?php else: ?>
    <div class="listing-grid">
        <?php foreach ($clubs as $club): ?>
        <div class="listing-card">
            <?php if (!empty($club['images'][0]['image_url'])): ?>
            <img src="<?= e($club['images'][0]['image_url']) ?>" alt="<?= e($club['title']) ?>">
            <?php endif; ?>
            <div class="body">
                <h3><a href="/clubs/<?= e($club['slug']) ?>"><?= e($club['title']) ?></a></h3>
                <?php if ($club['content']): ?><p><?= e(mb_strimwidth(strip_tags($club['content']), 0, 150, '...')) ?></p><?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
