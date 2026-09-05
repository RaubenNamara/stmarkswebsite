<article class="mx-auto max-w-3xl px-6 py-14">
    <h1 class="text-3xl font-bold tracking-tight text-gray-900"><?= e($article['title']) ?></h1>
    <p class="mt-2 text-sm text-gray-500">
        by <?= e($article['student_name']) ?>
        <?php if ($article['category']): ?> &middot; <?= e($article['category']) ?><?php endif; ?>
        &middot; <?= (int) $article['reading_time'] ?> min read &middot; <?= (int) $article['views'] ?> views
    </p>

    <?php if ($article['featured_image_url']): ?>
    <img src="<?= e($article['featured_image_url']) ?>" alt="<?= e($article['title']) ?>" class="mt-6 w-full rounded-xl object-cover">
    <?php endif; ?>

    <div class="prose prose-slate mt-6 max-w-none"><?= $article['content'] ?></div>

    <?php if ($article['author_bio']): ?>
    <div class="card mt-8 bg-gray-50">
        <strong class="text-gray-900">About the author</strong>
        <p class="mt-1 text-sm text-gray-600"><?= e($article['author_bio']) ?></p>
    </div>
    <?php endif; ?>

    <p class="mt-8"><a href="/campus-voices" class="font-semibold text-brand-navy hover:underline">&larr; Back to Campus Voices</a></p>
</article>
