<article class="mx-auto max-w-3xl px-6 py-14">
    <h1 class="text-3xl font-bold tracking-tight text-gray-900"><?= e($news['title']) ?></h1>
    <?php if ($news['image_path']): ?>
    <img src="<?= e(asset_url($news['image_path'])) ?>" alt="<?= e($news['title']) ?>" class="mt-6 w-full rounded-xl object-cover">
    <?php endif; ?>
    <div class="prose prose-slate mt-6 max-w-none">
        <?= $news['content'] ?>
    </div>
    <p class="mt-8"><a href="/news" class="font-semibold text-brand-navy hover:underline">&larr; Back to News</a></p>
</article>
