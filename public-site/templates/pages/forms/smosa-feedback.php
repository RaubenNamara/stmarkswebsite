<?php
$ratingFields = [
    'rating_event_organization' => 'Event Organization',
    'rating_communication' => 'Communication',
    'rating_venue_setup' => 'Venue Setup',
    'rating_programme_activities' => 'Programme & Activities',
    'rating_food_refreshments' => 'Food & Refreshments',
    'rating_entertainment' => 'Entertainment',
    'rating_guest_experience' => 'Guest Experience',
    'rating_time_management' => 'Time Management',
    'rating_photography_video' => 'Photography & Video (optional)',
];
$scale = ['Excellent', 'Very Good', 'Good', 'Fair', 'Poor'];
$activities = ['Career & Business Opportunities', 'Community/Charity Activities', 'Professional Development', 'Networking', 'Social Gatherings'];
?>
<?= partial('partials/page-header', [
    'title' => 'SMOSA Feedback',
    'subtitle' => 'Help us improve future SMOSA events by sharing your experience.',
]) ?>

<section class="mx-auto max-w-2xl px-6 py-14">
    <div class="card">
        <?php if ($success): ?>
        <div class="alert alert-success">Thank you for your feedback!</div>
        <?php elseif ($alreadySubmitted): ?>
        <div class="alert alert-success">You've already submitted feedback for this event. Thank you!</div>
        <?php else: ?>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error mb-6">Please correct the errors below.</div>
        <?php endif; ?>

        <form method="post" action="/smosa-feedback">
            <label for="overall_experience" class="field-label">Overall Experience</label>
            <select id="overall_experience" name="overall_experience" required class="field-input">
                <option value="">Select...</option>
                <?php foreach ($scale as $s): ?><option value="<?= e($s) ?>"><?= e($s) ?></option><?php endforeach; ?>
            </select>

            <?php foreach ($ratingFields as $field => $label): ?>
            <label class="field-label"><?= e($label) ?></label>
            <select name="<?= e($field) ?>" <?= $field === 'rating_photography_video' ? '' : 'required' ?> class="field-input">
                <option value="">Select...</option>
                <?php foreach ($scale as $s): ?><option value="<?= e($s) ?>"><?= e($s) ?></option><?php endforeach; ?>
            </select>
            <?php endforeach; ?>

            <label class="field-label">Activities you're interested in for future events</label>
            <div class="mb-4 space-y-1.5">
                <?php foreach ($activities as $activity): ?>
                <label class="flex items-center gap-2 text-sm font-normal text-gray-700">
                    <input type="checkbox" name="activities_interest[]" value="<?= e($activity) ?>" class="h-4 w-4 rounded border-gray-300 text-brand-navy focus:ring-brand-navy">
                    <?= e($activity) ?>
                </label>
                <?php endforeach; ?>
            </div>

            <label for="best_part" class="field-label">What was the best part of the event?</label>
            <textarea id="best_part" name="best_part" rows="3" class="field-input"></textarea>

            <label for="improvements" class="field-label">What could be improved?</label>
            <textarea id="improvements" name="improvements" rows="3" class="field-input"></textarea>

            <label for="future_suggestions" class="field-label">Suggestions for future events</label>
            <textarea id="future_suggestions" name="future_suggestions" rows="3" class="field-input"></textarea>

            <label for="future_participation" class="field-label">Will you participate in future SMOSA events?</label>
            <select id="future_participation" name="future_participation" required class="field-input">
                <option value="">Select...</option>
                <option value="Definitely">Definitely</option>
                <option value="Probably">Probably</option>
                <option value="Not sure">Not sure</option>
                <option value="Unlikely">Unlikely</option>
            </select>

            <label for="other_comments" class="field-label">Other comments</label>
            <textarea id="other_comments" name="other_comments" rows="3" class="field-input"></textarea>

            <button type="submit" class="btn w-full">Submit Feedback</button>
        </form>
        <?php endif; ?>
    </div>
</section>
