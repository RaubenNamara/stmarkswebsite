<?= partial('partials/page-header', [
    'title' => 'Campus Voices',
    'subtitle' => 'Stories, reflections and voices from our student community.',
]) ?>

<section class="mx-auto max-w-6xl px-6 py-14">
    <?php if (empty($articles)): ?>
    <?= partial('partials/empty-state', ['message' => 'No articles yet.']) ?>
    <?php else: ?>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($articles as $article): ?>
        <?= partial('partials/listing-card', [
            'url' => '/campus-voices/' . $article['slug'],
            'image' => $article['featured_image_url'] ?: null,
            'title' => $article['title'],
            'excerpt' => $article['summary'] ? 'by ' . $article['student_name'] . ' — ' . $article['summary'] : 'by ' . $article['student_name'],
            'meta' => ((int) $article['reading_time']) . ' min read',
            'badge' => $article['featured'] ? 'Featured' : null,
        ]) ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
