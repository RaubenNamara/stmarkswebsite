<?= partial('partials/page-header', ['title' => 'Contact Us']) ?>

<section class="mx-auto max-w-xl px-6 py-14">
    <div class="card">
        <?php if ($success): ?>
        <div class="alert alert-success">Thank you — your message has been sent. We'll get back to you soon.</div>
        <?php else: ?>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error mb-6">Please correct the errors below.</div>
        <?php endif; ?>

        <form method="post" action="/contact">
            <label for="name" class="field-label">Name</label>
            <input type="text" id="name" name="name" value="<?= e($old['name'] ?? '') ?>" required class="field-input">
            <?php if (!empty($errors['name'])): ?><p class="field-error"><?= e($errors['name']) ?></p><?php endif; ?>

            <label for="telephone" class="field-label">Telephone</label>
            <input type="tel" id="telephone" name="telephone" value="<?= e($old['telephone'] ?? '') ?>" class="field-input">

            <label for="email" class="field-label">Email</label>
            <input type="email" id="email" name="email" value="<?= e($old['email'] ?? '') ?>" required class="field-input">
            <?php if (!empty($errors['email'])): ?><p class="field-error"><?= e($errors['email']) ?></p><?php endif; ?>

            <label for="message" class="field-label">Message</label>
            <textarea id="message" name="message" rows="5" required class="field-input"><?= e($old['message'] ?? '') ?></textarea>
            <?php if (!empty($errors['message'])): ?><p class="field-error"><?= e($errors['message']) ?></p><?php endif; ?>

            <button type="submit" class="btn w-full">Send Message</button>
        </form>
        <?php endif; ?>
    </div>
</section>
