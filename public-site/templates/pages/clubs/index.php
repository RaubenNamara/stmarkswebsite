<?= partial('partials/page-header', ['title' => 'Clubs']) ?>

<section class="mx-auto max-w-6xl px-6 py-14">
    <?php if (empty($clubs)): ?>
    <?= partial('partials/empty-state', ['message' => 'No clubs yet.']) ?>
    <?php else: ?>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($clubs as $club): ?>
        <?= partial('partials/listing-card', [
            'url' => '/clubs/' . $club['slug'],
            'image' => $club['images'][0]['image_url'] ?? null,
            'title' => $club['title'],
            'excerpt' => $club['content'] ? mb_strimwidth(strip_tags($club['content']), 0, 150, '...') : null,
        ]) ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
