<?= partial('partials/page-header', ['title' => 'Gallery']) ?>

<section class="mx-auto max-w-6xl px-6 py-14">
    <?php if (empty($events)): ?>
    <?= partial('partials/empty-state', ['message' => 'No gallery events yet.']) ?>
    <?php else: ?>
    <div class="space-y-8">
        <?php foreach ($events as $event): ?>
        <div class="card">
            <h2 class="text-lg font-semibold text-gray-900"><?= e($event['title']) ?></h2>
            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                <?php foreach (array_slice($event['images'], 0, 12) as $img): ?>
                <img src="<?= e($img['image_url']) ?>" alt="" class="h-36 w-full rounded-lg object-cover">
                <?php endforeach; ?>
            </div>
            <?php if (count($event['images']) > 12): ?>
            <p class="mt-3 text-sm text-gray-500">+<?= count($event['images']) - 12 ?> more photos</p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
