<section class="page-header">
    <h1>Gallery</h1>
</section>

<section class="section">
    <?php if (empty($events)): ?>
    <div class="empty-state">No gallery events yet.</div>
    <?php else: ?>
    <?php foreach ($events as $event): ?>
    <div class="card">
        <h2><?= e($event['title']) ?></h2>
        <div class="listing-grid">
            <?php foreach (array_slice($event['images'], 0, 12) as $img): ?>
            <img src="<?= e($img['image_url']) ?>" alt="" style="height:150px;object-fit:cover;border-radius:0.5rem;">
            <?php endforeach; ?>
        </div>
        <?php if (count($event['images']) > 12): ?>
        <p style="margin-top:0.75rem;color:#6b7280;font-size:0.875rem;">+<?= count($event['images']) - 12 ?> more photos</p>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</section>
