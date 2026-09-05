<?php use StMarks\PublicSite\Support\View; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($meta['title'] ?? "St Mark's College Namagoma") ?></title>
    <?php if (!empty($meta['description'])): ?>
    <meta name="description" content="<?= e($meta['description']) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body class="flex min-h-screen flex-col bg-gray-50 text-gray-900">
<?= View::renderPartial('partials/nav') ?>
<main class="flex-1">
<?= $content ?>
</main>
<?= View::renderPartial('partials/footer') ?>
</body>
</html>
