<?php
/**
 * The navy gradient banner used at the top of every inner page.
 * @var string $title
 * @var string|null $subtitle
 */
?>
<section class="bg-gradient-to-b from-brand-navy to-brand-navy-dark px-6 py-14 text-center text-white sm:py-16">
    <h1 class="text-3xl font-bold tracking-tight sm:text-4xl"><?= e($title) ?></h1>
    <?php if (!empty($subtitle)): ?>
    <p class="mx-auto mt-3 max-w-2xl text-blue-100"><?= e($subtitle) ?></p>
    <?php endif; ?>
</section>
