<?php
$navLinks = [
    '/about' => 'About',
    '/academics' => 'Academics',
    '/admissions' => 'Admissions',
    '/news' => 'News',
    '/staff' => 'Staff',
    '/explore/gallery' => 'Gallery',
    '/clubs' => 'Clubs',
    '/campus-voices' => 'Campus Voices',
    '/empowerment-programmes' => 'Empowerment',
    '/fee-structures' => 'Fees',
    '/apply' => 'Careers',
    '/contact' => 'Contact',
];
?>
<header class="sticky top-0 z-50 bg-brand-navy shadow-md">
    <nav class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-6 py-4">
        <a href="/" class="shrink-0 text-lg font-bold text-white">St Mark's College Namagoma</a>

        <ul class="hidden flex-wrap items-center gap-x-6 gap-y-2 md:flex">
            <?php foreach ($navLinks as $href => $label): ?>
            <li><a href="<?= e($href) ?>" class="text-sm font-medium text-blue-100 transition hover:text-white"><?= e($label) ?></a></li>
            <?php endforeach; ?>
        </ul>

        <button type="button" id="nav-toggle" aria-controls="nav-menu" aria-expanded="false" aria-label="Toggle menu" class="rounded-md p-2 text-blue-100 hover:bg-white/10 hover:text-white md:hidden">
            <svg id="nav-icon-open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg id="nav-icon-close" xmlns="http://www.w3.org/2000/svg" class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </nav>

    <ul id="nav-menu" class="hidden flex-col gap-1 border-t border-white/10 px-6 pb-4 md:hidden">
        <?php foreach ($navLinks as $href => $label): ?>
        <li><a href="<?= e($href) ?>" class="block rounded-md px-2 py-2 text-sm font-medium text-blue-100 hover:bg-white/10 hover:text-white"><?= e($label) ?></a></li>
        <?php endforeach; ?>
    </ul>
</header>

<script>
(function () {
    var toggle = document.getElementById('nav-toggle');
    var menu = document.getElementById('nav-menu');
    var iconOpen = document.getElementById('nav-icon-open');
    var iconClose = document.getElementById('nav-icon-close');
    if (!toggle || !menu) return;
    toggle.addEventListener('click', function () {
        var isOpen = menu.classList.toggle('hidden') === false;
        toggle.setAttribute('aria-expanded', String(isOpen));
        iconOpen.classList.toggle('hidden', isOpen);
        iconClose.classList.toggle('hidden', !isOpen);
    });
})();
</script>
