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
<section class="page-header">
    <h1>SMOSA Feedback</h1>
    <p>Help us improve future SMOSA events by sharing your experience.</p>
</section>

<section class="section">
    <div class="card form-card" style="max-width:700px;margin:0 auto;">
        <?php if ($success): ?>
        <div class="alert alert-success">Thank you for your feedback!</div>
        <?php elseif ($alreadySubmitted): ?>
        <div class="alert alert-success">You've already submitted feedback for this event. Thank you!</div>
        <?php else: ?>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">Please correct the errors below.</div>
        <?php endif; ?>

        <form method="post" action="/smosa-feedback">
            <label for="overall_experience">Overall Experience</label>
            <select id="overall_experience" name="overall_experience" required>
                <option value="">Select...</option>
                <?php foreach ($scale as $s): ?><option value="<?= e($s) ?>"><?= e($s) ?></option><?php endforeach; ?>
            </select>

            <?php foreach ($ratingFields as $field => $label): ?>
            <label><?= e($label) ?></label>
            <select name="<?= e($field) ?>" <?= $field === 'rating_photography_video' ? '' : 'required' ?>>
                <option value="">Select...</option>
                <?php foreach ($scale as $s): ?><option value="<?= e($s) ?>"><?= e($s) ?></option><?php endforeach; ?>
            </select>
            <?php endforeach; ?>

            <label>Activities you're interested in for future events</label>
            <div style="margin-bottom:1rem;">
                <?php foreach ($activities as $activity): ?>
                <label style="font-weight:400;display:block;"><input type="checkbox" name="activities_interest[]" value="<?= e($activity) ?>" style="width:auto;display:inline;margin-right:0.5rem;"><?= e($activity) ?></label>
                <?php endforeach; ?>
            </div>

            <label for="best_part">What was the best part of the event?</label>
            <textarea id="best_part" name="best_part" rows="3"></textarea>

            <label for="improvements">What could be improved?</label>
            <textarea id="improvements" name="improvements" rows="3"></textarea>

            <label for="future_suggestions">Suggestions for future events</label>
            <textarea id="future_suggestions" name="future_suggestions" rows="3"></textarea>

            <label for="future_participation">Will you participate in future SMOSA events?</label>
            <select id="future_participation" name="future_participation" required>
                <option value="">Select...</option>
                <option value="Definitely">Definitely</option>
                <option value="Probably">Probably</option>
                <option value="Not sure">Not sure</option>
                <option value="Unlikely">Unlikely</option>
            </select>

            <label for="other_comments">Other comments</label>
            <textarea id="other_comments" name="other_comments" rows="3"></textarea>

            <button type="submit" class="btn">Submit Feedback</button>
        </form>
        <?php endif; ?>
    </div>
</section>
