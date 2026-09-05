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
<?= partial('partials/page-header', ['title' => $pageTitle]) ?>

<section class="mx-auto max-w-6xl px-6 py-14">
    <?php if (empty($items)): ?>
    <?= partial('partials/empty-state', ['message' => 'Nothing here yet.']) ?>
    <?php else: ?>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($items as $item): ?>
        <?php
        $excerpt = null;
        foreach ($textFields as $column => $label) {
            if (!empty($item[$column])) {
                $excerpt = mb_strimwidth(strip_tags((string) $item[$column]), 0, 180, '...');
                break;
            }
        }
        ?>
        <?= partial('partials/listing-card', [
            'image' => $item[$imageField] ?? null,
            'video' => $item[$videoField] ?? null,
            'title' => $item[$titleField],
            'excerpt' => $excerpt,
        ]) ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
