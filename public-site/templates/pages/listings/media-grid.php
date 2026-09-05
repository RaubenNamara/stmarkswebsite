<?php
/**
 * Shared listing template for the ~10 "flat table with optional image/video" public pages
 * (Board Members, Co-Curricular, High Achievers, Mentorship, Girl-Boy Talk, Inspiration Night,
 * SMOSA Alumni, Christmas Cantata, Chaplaincy, Student Leadership) - reused via View::render()
 * with different $items/$pageTitle/$textFields/$imageField per route, mirroring the admin SPA's
 * ImageVideoCrudPage/SinglePhotoCrudPage generic components for the same reason.
 *
 * @var array $items
 * @var string $pageTitle
 * @var string $titleField
 * @var array<string,string> $textFields column => label, shown in order
 * @var string $imageField
 * @var string $videoField
 */
$imageField ??= 'image_url';
$videoField ??= 'video_url';
?>
<section class="page-header">
    <h1><?= e($pageTitle) ?></h1>
</section>

<section class="section">
    <?php if (empty($items)): ?>
    <div class="empty-state">Nothing here yet.</div>
    <?php else: ?>
    <div class="listing-grid">
        <?php foreach ($items as $item): ?>
        <div class="listing-card">
            <?php if (!empty($item[$imageField])): ?>
            <img src="<?= e($item[$imageField]) ?>" alt="<?= e($item[$titleField]) ?>">
            <?php elseif (!empty($item[$videoField])): ?>
            <video src="<?= e($item[$videoField]) ?>" controls></video>
            <?php endif; ?>
            <div class="body">
                <h3><?= e($item[$titleField]) ?></h3>
                <?php foreach ($textFields as $column => $label): ?>
                    <?php if (!empty($item[$column])): ?>
                    <p><?= e(mb_strimwidth(strip_tags((string) $item[$column]), 0, 180, '...')) ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
