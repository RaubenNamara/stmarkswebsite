<section class="page-header">
    <h1>Our Staff</h1>
</section>

<section class="section">
    <?php foreach ($groups as $category => $members): ?>
    <div class="staff-group">
        <h2><?= e($category) ?></h2>
        <div class="staff-grid">
            <?php foreach ($members as $member): ?>
            <div class="staff-member">
                <?php if (!empty($member['photo_url'])): ?>
                <img src="<?= e($member['photo_url']) ?>" alt="<?= e($member['name']) ?>">
                <?php else: ?>
                <div style="width:96px;height:96px;border-radius:50%;background:#f3f4f6;margin:0 auto 0.5rem;"></div>
                <?php endif; ?>
                <div class="name"><?= e($member['name']) ?></div>
                <div class="dept"><?= e($member['department']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($groups)): ?>
    <div class="empty-state">Staff directory coming soon.</div>
    <?php endif; ?>
</section>
