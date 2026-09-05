<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\SmosaFeedback;

/**
 * Admin-side view/delete only (feedback is submitted by the public /smosa-feedback form) plus a
 * stats dashboard - the one genuinely bespoke piece: weighted averages and percentage breakdowns
 * per rating_* column, on a standard 5-point Likert scale (Excellent..Poor, confirmed against the
 * live data's actual label values).
 */
class SmosaFeedbackService extends Service
{
    private const RATING_COLUMNS = [
        'rating_event_organization' => 'Event Organization',
        'rating_communication' => 'Communication',
        'rating_venue_setup' => 'Venue Setup',
        'rating_programme_activities' => 'Programme & Activities',
        'rating_food_refreshments' => 'Food & Refreshments',
        'rating_entertainment' => 'Entertainment',
        'rating_guest_experience' => 'Guest Experience',
        'rating_time_management' => 'Time Management',
        'rating_photography_video' => 'Photography & Video',
    ];

    private const SCALE = ['Excellent' => 5, 'Very Good' => 4, 'Good' => 3, 'Fair' => 2, 'Poor' => 1];

    public function __construct(private SmosaFeedback $model = new SmosaFeedback())
    {
    }

    public function submit(array $data): array
    {
        $rules = ['overall_experience' => ['required'], 'future_participation' => ['required']];
        foreach (array_keys(self::RATING_COLUMNS) as $column) {
            if ($column === 'rating_photography_video') {
                continue; // the only optional rating in the original form
            }
            $rules[$column] = ['required'];
        }

        $errors = $this->validate($data, $rules);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $row = $this->sanitize([
            'overall_experience' => $data['overall_experience'],
            'best_part' => $data['best_part'] ?? null,
            'improvements' => $data['improvements'] ?? null,
            'future_suggestions' => $data['future_suggestions'] ?? null,
            'other_comments' => $data['other_comments'] ?? null,
            'future_participation' => $data['future_participation'],
            'activities_other' => $data['activities_other'] ?? null,
        ]);
        foreach (self::RATING_COLUMNS as $column => $label) {
            $row[$column] = isset($data[$column]) ? htmlspecialchars(trim((string) $data[$column]), ENT_QUOTES, 'UTF-8') : null;
        }

        $activities = $data['activities_interest'] ?? [];
        $row['activities_interest'] = json_encode(is_array($activities) ? array_values($activities) : [], JSON_UNESCAPED_SLASHES);

        $id = $this->model->create($row);
        return $id === false ? ['ok' => false, 'errors' => ['general' => 'Failed to submit feedback']] : ['ok' => true, 'id' => $id];
    }

    public function paginate(int $page, int $limit): array
    {
        return $this->model->paginate($page, $limit);
    }

    public function delete(int $id): bool
    {
        return $this->model->delete($id);
    }

    public function stats(): array
    {
        $rows = $this->model->all();
        $total = count($rows);

        $categories = [];
        foreach (self::RATING_COLUMNS as $column => $label) {
            $categories[$column] = $this->columnStats($rows, $column, $label);
        }

        $activityCounts = [];
        foreach ($rows as $row) {
            $activities = json_decode($row['activities_interest'] ?? '[]', true) ?: [];
            foreach ($activities as $activity) {
                $activityCounts[$activity] = ($activityCounts[$activity] ?? 0) + 1;
            }
        }
        arsort($activityCounts);

        return [
            'total_responses' => $total,
            'categories' => array_values($categories),
            'activities_interest' => $activityCounts,
            'overall_experience_breakdown' => $this->labelBreakdown($rows, 'overall_experience', $total),
            'future_participation_breakdown' => $this->labelBreakdown($rows, 'future_participation', $total),
        ];
    }

    private function columnStats(array $rows, string $column, string $label): array
    {
        $breakdown = $this->labelBreakdown($rows, $column, count($rows));

        $sum = 0;
        $scored = 0;
        foreach ($rows as $row) {
            $value = $row[$column] ?? null;
            if ($value !== null && isset(self::SCALE[$value])) {
                $sum += self::SCALE[$value];
                $scored++;
            }
        }

        return [
            'column' => $column,
            'label' => $label,
            'average' => $scored > 0 ? round($sum / $scored, 2) : null,
            'breakdown' => $breakdown,
        ];
    }

    private function labelBreakdown(array $rows, string $column, int $total): array
    {
        $counts = [];
        foreach ($rows as $row) {
            $value = $row[$column] ?? 'Unknown';
            $counts[$value] = ($counts[$value] ?? 0) + 1;
        }

        $breakdown = [];
        foreach ($counts as $value => $count) {
            $breakdown[] = [
                'value' => $value,
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
            ];
        }

        return $breakdown;
    }
}
