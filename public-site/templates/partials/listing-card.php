<?php
/**
 * One card in a listing grid (news, posts, campus voices, clubs, and the ~10 media-grid
 * domains) - a single shared shape so every listing page looks the same instead of four
 * bespoke near-duplicates.
 *
 * @var string|null $url Link target; card renders as plain content (no link) when omitted
 * @var string|null $image
 * @var string|null $video
 * @var string $title
 * @var string|null $excerpt
 * @var string|null $meta Small text shown at the bottom of the card (date, author, reading time...)
 * @var string|null $badge Small pill shown above the title (e.g. "Featured")
 */
$image ??= null;
$video ??= null;
$excerpt ??= null;
$meta ??= null;
$badge ??= null;
$url ??= null;
?>
<div class="group relative flex flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-black/5 transition hover:shadow-md">
    <?php if ($image): ?>
    <img src="<?= e($image) ?>" alt="<?= e($title) ?>" class="h-44 w-full bg-gray-100 object-cover">
    <?php elseif ($video): ?>
    <video src="<?= e($video) ?>" controls class="h-44 w-full bg-gray-900 object-cover"></video>
    <?php endif; ?>
    <div class="flex flex-1 flex-col p-5">
        <?php if ($badge): ?>
        <span class="mb-2 inline-block w-fit rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700"><?= e($badge) ?></span>
        <?php endif; ?>
        <h3 class="font-semibold text-gray-900">
            <?php if ($url): ?>
            <a href="<?= e($url) ?>" class="hover:text-brand-navy"><span class="absolute inset-0"></span><?= e($title) ?></a>
            <?php else: ?>
            <?= e($title) ?>
            <?php endif; ?>
        </h3>
        <?php if ($excerpt): ?>
        <p class="mt-1.5 text-sm text-gray-600"><?= e($excerpt) ?></p>
        <?php endif; ?>
        <?php if ($meta): ?>
        <div class="mt-auto pt-3 text-xs text-gray-400"><?= e($meta) ?></div>
        <?php endif; ?>
    </div>
</div>
