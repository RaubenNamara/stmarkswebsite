<?= partial('partials/page-header', [
    'title' => "Apply — Careers",
    'subtitle' => "Join our team and help shape the future with St Mark's College.",
]) ?>

<section class="mx-auto max-w-xl px-6 py-14">
    <div class="card">
        <?php if ($success): ?>
        <div class="alert alert-success">Thank you — your application has been received. We'll be in touch if there's a match.</div>
        <?php else: ?>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error mb-6">Please correct the errors below.</div>
        <?php endif; ?>

        <form method="post" action="/apply" enctype="multipart/form-data">
            <label for="full_name" class="field-label">Full Name</label>
            <input type="text" id="full_name" name="full_name" value="<?= e($old['full_name'] ?? '') ?>" required class="field-input">
            <?php if (!empty($errors['full_name'])): ?><p class="field-error"><?= e($errors['full_name']) ?></p><?php endif; ?>

            <label for="contact" class="field-label">Contact (phone)</label>
            <input type="text" id="contact" name="contact" value="<?= e($old['contact'] ?? '') ?>" required class="field-input">

            <label for="email" class="field-label">Email</label>
            <input type="email" id="email" name="email" value="<?= e($old['email'] ?? '') ?>" required class="field-input">

            <label for="address" class="field-label">Address</label>
            <textarea id="address" name="address" rows="3" required class="field-input"><?= e($old['address'] ?? '') ?></textarea>

            <label for="position" class="field-label">Position Applying For</label>
            <input type="text" id="position" name="position" value="<?= e($old['position'] ?? '') ?>" required class="field-input">

            <label for="cv" class="field-label">CV (PDF, max 5MB)</label>
            <input type="file" id="cv" name="cv" accept="application/pdf" required class="field-input">
            <?php if (!empty($errors['file'])): ?><p class="field-error -mt-3"><?= e($errors['file']) ?></p><?php endif; ?>

            <button type="submit" class="btn btn-gold w-full">Submit Application</button>
        </form>
        <?php endif; ?>
    </div>
</section>
