<article class="news-article">
    <h1><?= e($news['title']) ?></h1>
    <?php if ($news['image_path']): ?>
    <img src="<?= e(asset_url($news['image_path'])) ?>" alt="<?= e($news['title']) ?>">
    <?php endif; ?>
    <div class="news-article__content">
        <?= $news['content'] ?>
    </div>
    <p><a href="/news">&larr; Back to News</a></p>
</article>
