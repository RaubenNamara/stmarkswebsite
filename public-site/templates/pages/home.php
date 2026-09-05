<section class="hero">
    <h1>Welcome to St Mark's College Namagoma</h1>
    <p>The Higher Achiever's College — building disciplined, confident and competent learners since our founding.</p>
    <p style="margin-top:1.5rem;"><a href="/admissions" class="btn btn-gold">Apply Now</a></p>
</section>

<section class="section">
    <div class="card-grid">
        <div class="card accent-blue">
            <h2>Our Motto</h2>
            <p>"To Be Not To Seem" — reflecting our founders' desire to train students with strong values that guide them through life.</p>
        </div>
        <div class="card accent-gold">
            <h2>Core Values — GREET</h2>
            <p>Godliness, Reliability, Ethics, Excellence, Team Work.</p>
        </div>
        <div class="card accent-blue">
            <h2>Academic Excellence</h2>
            <p>Stimulating, rewarding and forward-looking programs that build the whole person.</p>
        </div>
    </div>
</section>

<?php if (!empty($latestNews)): ?>
<section class="section">
    <h2>Latest News</h2>
    <div class="listing-grid">
        <?php foreach ($latestNews as $item): ?>
        <div class="listing-card">
            <?php if ($item['image_url']): ?>
            <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['title']) ?>">
            <?php endif; ?>
            <div class="body">
                <h3><a href="/news/<?= e($item['slug']) ?>"><?= e($item['title']) ?></a></h3>
                <?php if ($item['excerpt']): ?><p><?= e($item['excerpt']) ?></p><?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <p style="margin-top:1.5rem;"><a href="/news">View all news &rarr;</a></p>
</section>
<?php endif; ?>
