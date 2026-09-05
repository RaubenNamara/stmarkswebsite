<?= partial('partials/page-header', ['title' => 'Performance & Circulars']) ?>

<section class="mx-auto max-w-3xl px-6 py-14">
    <?php if (empty($items)): ?>
    <?= partial('partials/empty-state', ['message' => 'No documents available yet.']) ?>
    <?php else: ?>
    <div class="card divide-y divide-gray-100">
        <?php foreach ($items as $item): ?>
        <div class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
            <span class="text-gray-800">📄 <?= e($item['title']) ?></span>
            <?php if (str_starts_with($item['pdf'], '/uploads/')): ?>
            <a href="/performance/<?= (int) $item['id'] ?>/pdf" target="_blank" class="shrink-0 font-semibold text-brand-navy hover:underline">View PDF</a>
            <?php else: ?>
            <a href="<?= e($item['file_url']) ?>" target="_blank" class="shrink-0 font-semibold text-brand-navy hover:underline">View PDF</a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
