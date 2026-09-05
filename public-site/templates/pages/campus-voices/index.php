<section class="page-header">
    <h1>Campus Voices</h1>
    <p>Stories, reflections and voices from our student community.</p>
</section>

<section class="section">
    <?php if (empty($articles)): ?>
    <div class="empty-state">No articles yet.</div>
    <?php else: ?>
    <div class="listing-grid">
        <?php foreach ($articles as $article): ?>
        <div class="listing-card">
            <?php if ($article['featured_image_url']): ?>
            <img src="<?= e($article['featured_image_url']) ?>" alt="<?= e($article['title']) ?>">
            <?php endif; ?>
            <div class="body">
                <?php if ($article['featured']): ?><span style="color:#b45309;font-size:0.75rem;font-weight:600;">FEATURED</span><?php endif; ?>
                <h3><a href="/campus-voices/<?= e($article['slug']) ?>"><?= e($article['title']) ?></a></h3>
                <p>by <?= e($article['student_name']) ?></p>
                <?php if ($article['summary']): ?><p><?= e($article['summary']) ?></p><?php endif; ?>
                <div class="meta"><?= (int) $article['reading_time'] ?> min read</div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
