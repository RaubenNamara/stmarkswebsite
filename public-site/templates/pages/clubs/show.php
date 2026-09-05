<article class="mx-auto max-w-3xl px-6 py-14">
    <h1 class="text-3xl font-bold tracking-tight text-gray-900"><?= e($club['title']) ?></h1>
    <div class="prose prose-slate mt-6 max-w-none"><?= $club['content'] ?></div>

    <?php if (!empty($club['images'])): ?>
    <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3">
        <?php foreach ($club['images'] as $img): ?>
        <img src="<?= e($img['image_url']) ?>" alt="" class="h-40 w-full rounded-lg object-cover">
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <p class="mt-8"><a href="/clubs" class="font-semibold text-brand-navy hover:underline">&larr; Back to Clubs</a></p>
</article>
