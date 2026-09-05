<article class="section">
    <h1><?= e($article['title']) ?></h1>
    <p style="color:#6b7280;">
        by <?= e($article['student_name']) ?>
        <?php if ($article['category']): ?> · <?= e($article['category']) ?><?php endif; ?>
        · <?= (int) $article['reading_time'] ?> min read · <?= (int) $article['views'] ?> views
    </p>

    <?php if ($article['featured_image_url']): ?>
    <img src="<?= e($article['featured_image_url']) ?>" alt="<?= e($article['title']) ?>" style="border-radius:0.75rem;margin-bottom:1.5rem;">
    <?php endif; ?>

    <div class="card"><?= $article['content'] ?></div>

    <?php if ($article['author_bio']): ?>
    <div class="card" style="background:#f9fafb;">
        <strong>About the author</strong>
        <p><?= e($article['author_bio']) ?></p>
    </div>
    <?php endif; ?>

    <p><a href="/campus-voices">&larr; Back to Campus Voices</a></p>
</article>
