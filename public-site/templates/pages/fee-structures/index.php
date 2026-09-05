<section class="page-header">
    <h1>Fee Structures, Personal Needs, School Rules &amp; Calendar</h1>
</section>

<section class="section">
    <?php if (empty($items)): ?>
    <div class="empty-state">No documents available yet.</div>
    <?php else: ?>
    <div class="card">
        <?php foreach ($items as $item): ?>
        <p>
            📄 <?= e($item['title']) ?> —
            <?php if (str_starts_with($item['file_path'], '/uploads/')): ?>
                <a href="/fee-structures/<?= (int) $item['id'] ?>/pdf" target="_blank">View PDF</a>
            <?php else: ?>
                <a href="<?= e($item['file_url']) ?>" target="_blank">View PDF</a>
            <?php endif; ?>
        </p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
