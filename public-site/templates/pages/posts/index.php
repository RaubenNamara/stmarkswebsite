<?= partial('partials/page-header', ['title' => 'Posts']) ?>

<section class="mx-auto max-w-6xl px-6 py-14">
    <?php if (empty($posts)): ?>
    <?= partial('partials/empty-state', ['message' => 'No posts yet.']) ?>
    <?php else: ?>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($posts as $post): ?>
        <?= partial('partials/listing-card', [
            'url' => '/posts/' . $post['slug'],
            'title' => $post['title'],
            'excerpt' => mb_strimwidth(strip_tags($post['content']), 0, 160, '...'),
        ]) ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
