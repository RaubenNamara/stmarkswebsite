<?= partial('partials/page-header', ['title' => 'Our Staff']) ?>

<section class="mx-auto max-w-6xl px-6 py-14">
    <?php foreach ($groups as $category => $members): ?>
    <div class="mb-12">
        <h2 class="border-b-2 border-brand-gold pb-2 text-xl font-bold text-brand-navy"><?= e($category) ?></h2>
        <div class="mt-6 grid grid-cols-2 gap-5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
            <?php foreach ($members as $member): ?>
            <div class="text-center">
                <?php if (!empty($member['photo_url'])): ?>
                <img src="<?= e($member['photo_url']) ?>" alt="<?= e($member['name']) ?>" class="mx-auto mb-2 h-24 w-24 rounded-full object-cover">
                <?php else: ?>
                <div class="mx-auto mb-2 h-24 w-24 rounded-full bg-gray-200"></div>
                <?php endif; ?>
                <div class="text-sm font-semibold text-gray-900"><?= e($member['name']) ?></div>
                <div class="text-xs text-gray-500"><?= e($member['department']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($groups)): ?>
    <?= partial('partials/empty-state', ['message' => 'Staff directory coming soon.']) ?>
    <?php endif; ?>
</section>
