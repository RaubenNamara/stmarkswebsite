<article class="section">
    <h1><?= e($club['title']) ?></h1>
    <div class="card"><?= $club['content'] ?></div>

    <?php if (!empty($club['images'])): ?>
    <div class="listing-grid">
        <?php foreach ($club['images'] as $img): ?>
        <img src="<?= e($img['image_url']) ?>" alt="" style="border-radius:0.5rem;">
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <p style="margin-top:1.5rem;"><a href="/clubs">&larr; Back to Clubs</a></p>
</article>
