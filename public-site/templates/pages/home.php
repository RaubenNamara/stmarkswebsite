<section class="bg-gradient-to-b from-brand-navy to-brand-navy-dark px-6 py-20 text-center text-white sm:py-28">
    <h1 class="mx-auto max-w-3xl text-4xl font-bold tracking-tight sm:text-5xl">Welcome to St Mark's College Namagoma</h1>
    <p class="mx-auto mt-4 max-w-2xl text-lg text-blue-100">The Higher Achiever's College — building disciplined, confident and competent learners since our founding.</p>
    <a href="/admissions" class="btn btn-gold mt-8">Apply Now</a>
</section>

<section class="mx-auto max-w-6xl px-6 py-14 sm:py-16">
    <div class="grid gap-6 sm:grid-cols-3">
        <div class="card border-t-4 border-brand-navy">
            <h2 class="text-lg font-semibold">Our Motto</h2>
            <p class="mt-2 text-sm text-gray-600">"To Be Not To Seem" — reflecting our founders' desire to train students with strong values that guide them through life.</p>
        </div>
        <div class="card border-t-4 border-brand-gold">
            <h2 class="text-lg font-semibold">Core Values — GREET</h2>
            <p class="mt-2 text-sm text-gray-600">Godliness, Reliability, Ethics, Excellence, Team Work.</p>
        </div>
        <div class="card border-t-4 border-brand-navy">
            <h2 class="text-lg font-semibold">Academic Excellence</h2>
            <p class="mt-2 text-sm text-gray-600">Stimulating, rewarding and forward-looking programs that build the whole person.</p>
        </div>
    </div>
</section>

<?php if (!empty($latestNews)): ?>
<section class="mx-auto max-w-6xl px-6 pb-16">
    <h2 class="text-2xl font-bold text-gray-900">Latest News</h2>
    <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($latestNews as $item): ?>
        <?= partial('partials/listing-card', [
            'url' => '/news/' . $item['slug'],
            'image' => $item['image_url'] ?: null,
            'title' => $item['title'],
            'excerpt' => $item['excerpt'] ?: null,
        ]) ?>
        <?php endforeach; ?>
    </div>
    <p class="mt-6"><a href="/news" class="font-semibold text-brand-navy hover:underline">View all news &rarr;</a></p>
</section>
<?php endif; ?>
