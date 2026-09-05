<?php
$chorus = [
    "WE shall be not to seem,",
    "Examples of the World,",
    "On this gentle foundation,",
    "St. Mark's College Staff,",
    "Will guide us with the pillar",
    "of light forever more…",
];
$stanzas = [
    ["St. Mark's College striving for excellence,", "May God give us strength to perform!", "With discipline, smartness, and the development of talents,", "Our future is safe and shinning blue."],
    ["St. Mark's College, the high Achiever's pride,", "We toil to meet and live our pledge,", "Of quality education, devotion, and teamwork,", "We shall live, Achievers we shall be."],
    ["St. Mark's College so competent and strong,", "The staff is qualified and bold.", "Let us be not to seem, to develop our motherland,", "Uganda long, long live,", "Long, our founders long live."],
];
?>
<?= partial('partials/page-header', [
    'title' => 'School Anthem',
    'subtitle' => 'Our anthem — a pledge of faith, discipline, and excellence. Chorus repeats after every stanza.',
]) ?>

<section class="mx-auto max-w-6xl px-6 py-14">
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($stanzas as $i => $lines): ?>
        <div class="card border-t-4 border-brand-navy">
            <h2 class="font-semibold text-gray-900"><?= $i + 1 ?>. Stanza</h2>
            <?php foreach ($lines as $line): ?>
                <p class="mt-1 text-sm text-gray-600"><?= e($line) ?></p>
            <?php endforeach; ?>
            <hr class="my-5 border-gray-200">
            <p class="mb-2 font-semibold text-brand-navy">Chorus (repeat after every stanza)</p>
            <?php foreach ($chorus as $line): ?>
                <p class="text-sm text-gray-500"><?= e($line) ?></p>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>
