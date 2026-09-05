<section class="page-header">
    <h1>Apply — Careers</h1>
    <p>Join our team and help shape the future with St Mark's College.</p>
</section>

<section class="section">
    <div class="card form-card" style="max-width:600px;margin:0 auto;">
        <?php if ($success): ?>
        <div class="alert alert-success">Thank you — your application has been received. We'll be in touch if there's a match.</div>
        <?php else: ?>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">Please correct the errors below.</div>
        <?php endif; ?>

        <form method="post" action="/apply" enctype="multipart/form-data">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" value="<?= e($old['full_name'] ?? '') ?>" required>
            <?php if (!empty($errors['full_name'])): ?><p style="color:#991b1b;font-size:0.85rem;margin:-0.75rem 0 1rem;"><?= e($errors['full_name']) ?></p><?php endif; ?>

            <label for="contact">Contact (phone)</label>
            <input type="text" id="contact" name="contact" value="<?= e($old['contact'] ?? '') ?>" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= e($old['email'] ?? '') ?>" required>

            <label for="address">Address</label>
            <textarea id="address" name="address" rows="3" required><?= e($old['address'] ?? '') ?></textarea>

            <label for="position">Position Applying For</label>
            <input type="text" id="position" name="position" value="<?= e($old['position'] ?? '') ?>" required>

            <label for="cv">CV (PDF, max 5MB)</label>
            <input type="file" id="cv" name="cv" accept="application/pdf" required>
            <?php if (!empty($errors['file'])): ?><p style="color:#991b1b;font-size:0.85rem;margin:0.25rem 0 1rem;"><?= e($errors['file']) ?></p><?php endif; ?>

            <button type="submit" class="btn btn-gold">Submit Application</button>
        </form>
        <?php endif; ?>
    </div>
</section>
