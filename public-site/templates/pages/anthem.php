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
<section class="page-header">
    <h1>School Anthem</h1>
    <p>Our anthem — a pledge of faith, discipline, and excellence. Chorus repeats after every stanza.</p>
</section>

<section class="section">
    <div class="card-grid">
        <?php foreach ($stanzas as $i => $lines): ?>
        <div class="card accent-blue">
            <h2><?= $i + 1 ?>. Stanza</h2>
            <?php foreach ($lines as $line): ?>
                <p style="margin:0 0 0.4rem;"><?= e($line) ?></p>
            <?php endforeach; ?>
            <hr style="margin:1.25rem 0; border:none; border-top:1px solid #e5e7eb;">
            <p style="font-weight:600; color:#172554; margin-bottom:0.5rem;">Chorus (repeat after every stanza)</p>
            <?php foreach ($chorus as $line): ?>
                <p style="margin:0 0 0.2rem; font-size:0.9rem;"><?= e($line) ?></p>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>
