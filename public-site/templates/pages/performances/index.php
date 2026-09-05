<section class="page-header">
    <h1>Performance &amp; Circulars</h1>
</section>

<section class="section">
    <?php if (empty($items)): ?>
    <div class="empty-state">No documents available yet.</div>
    <?php else: ?>
    <div class="card">
        <?php foreach ($items as $item): ?>
        <p>
            📄 <?= e($item['title']) ?> —
            <?php if (str_starts_with($item['pdf'], '/uploads/')): ?>
                <a href="/performance/<?= (int) $item['id'] ?>/pdf" target="_blank">View PDF</a>
            <?php else: ?>
                <a href="<?= e($item['file_url']) ?>" target="_blank">View PDF</a>
            <?php endif; ?>
        </p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
