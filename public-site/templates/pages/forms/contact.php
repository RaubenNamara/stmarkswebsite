<section class="page-header">
    <h1>Contact Us</h1>
</section>

<section class="section">
    <div class="card form-card" style="max-width:600px;margin:0 auto;">
        <?php if ($success): ?>
        <div class="alert alert-success">Thank you — your message has been sent. We'll get back to you soon.</div>
        <?php else: ?>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">Please correct the errors below.</div>
        <?php endif; ?>

        <form method="post" action="/contact">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?= e($old['name'] ?? '') ?>" required>
            <?php if (!empty($errors['name'])): ?><p style="color:#991b1b;font-size:0.85rem;margin:-0.75rem 0 1rem;"><?= e($errors['name']) ?></p><?php endif; ?>

            <label for="telephone">Telephone</label>
            <input type="tel" id="telephone" name="telephone" value="<?= e($old['telephone'] ?? '') ?>">

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= e($old['email'] ?? '') ?>" required>
            <?php if (!empty($errors['email'])): ?><p style="color:#991b1b;font-size:0.85rem;margin:-0.75rem 0 1rem;"><?= e($errors['email']) ?></p><?php endif; ?>

            <label for="message">Message</label>
            <textarea id="message" name="message" rows="5" required><?= e($old['message'] ?? '') ?></textarea>
            <?php if (!empty($errors['message'])): ?><p style="color:#991b1b;font-size:0.85rem;margin:-0.75rem 0 1rem;"><?= e($errors['message']) ?></p><?php endif; ?>

            <button type="submit" class="btn">Send Message</button>
        </form>
        <?php endif; ?>
    </div>
</section>
