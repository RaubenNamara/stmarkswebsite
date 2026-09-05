<?= partial('partials/page-header', ['title' => 'News']) ?>

<section class="mx-auto max-w-6xl px-6 py-14">
    <?php if (empty($newsItems)): ?>
    <?= partial('partials/empty-state', ['message' => 'No news yet.']) ?>
    <?php else: ?>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($newsItems as $item): ?>
        <?= partial('partials/listing-card', [
            'url' => '/news/' . $item['slug'],
            'image' => $item['image_path'] ? asset_url($item['image_path']) : null,
            'title' => $item['title'],
            'excerpt' => $item['excerpt'] ?: null,
        ]) ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
