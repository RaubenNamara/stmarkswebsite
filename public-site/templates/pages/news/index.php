<section class="news-index">
    <h1>News</h1>
    <ul class="news-list">
        <?php foreach ($newsItems as $item): ?>
        <li class="news-list__item">
            <?php if ($item['image_path']): ?>
            <img src="<?= e(asset_url($item['image_path'])) ?>" alt="<?= e($item['title']) ?>">
            <?php endif; ?>
            <h2><a href="/news/<?= e($item['slug']) ?>"><?= e($item['title']) ?></a></h2>
            <?php if ($item['excerpt']): ?>
            <p><?= e($item['excerpt']) ?></p>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
</section>
