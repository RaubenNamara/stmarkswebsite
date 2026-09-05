<?php

namespace App\Http\Controllers;

use App\Models\SmosaFeedback;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SmosaFeedbackController extends Controller
{
    /**
     * Cookie used to stop the same device/browser submitting more than once.
     */
    public const SUBMITTED_COOKIE = 'smosa_feedback_2026_submitted';

    /**
     * The 8 "Event Experience" areas, keyed by database column.
     */
    public const RATING_AREAS = [
        'rating_event_organization' => 'Event organization',
        'rating_communication' => 'Communication before the event',
        'rating_venue_setup' => 'Venue & setup',
        'rating_programme_activities' => 'Programme & activities',
        'rating_food_refreshments' => 'Food & refreshments',
        'rating_entertainment' => 'Entertainment',
        'rating_guest_experience' => 'Guest experience',
        'rating_time_management' => 'Time management',
        'rating_photography_video' => 'Photography & Video',
    ];

    /**
     * Store feedback submitted by an alumnus/alumna from the public form.
     */
    public function store(Request $request)
    {
        if ($request->hasCookie(self::SUBMITTED_COOKIE)) {
            return back()->withErrors([
                'duplicate' => 'This device has already submitted feedback for the SMOSA Homecoming Dinner 2026.',
            ]);
        }

        $ratingRule = 'required|string|in:Excellent,Good,Fair,Poor';

        $validated = $request->validate([
            'overall_experience' => 'required|string|in:Excellent,Very Good,Good,Fair,Poor',

            'rating_event_organization' => $ratingRule,
            'rating_communication' => $ratingRule,
            'rating_venue_setup' => $ratingRule,
            'rating_programme_activities' => $ratingRule,
            'rating_food_refreshments' => $ratingRule,
            'rating_entertainment' => $ratingRule,
            'rating_guest_experience' => $ratingRule,
            'rating_time_management' => $ratingRule,
            'rating_photography_video' => $ratingRule,

            'best_part' => 'nullable|string',
            'improvements' => 'nullable|string',
            'future_suggestions' => 'nullable|string',
            'other_comments' => 'nullable|string',

            'future_participation' => 'required|string|in:Definitely,Probably,Not sure,Probably not',
            'activities_interest' => 'nullable|array',
            'activities_interest.*' => 'string',
            'activities_other' => 'nullable|string|max:255',
        ]);

        SmosaFeedback::create($validated);

        return back()
            ->with('success', 'Thank you for your feedback!')
            ->withCookie(cookie(self::SUBMITTED_COOKIE, '1', 60 * 24 * 365));
    }

    /**
     * Admin: dashboard of all feedback, categorised and shown as percentages.
     */
    public function index()
    {
        $feedback = SmosaFeedback::latest()->get();
        $total = $feedback->count();

        return Inertia::render('Admin/SmosaFeedback/Index', [
            'pageTitle' => 'SMOSA Feedback',
            'total' => $total,
            'overallExperience' => $this->breakdown(
                $feedback->pluck('overall_experience'),
                ['Excellent', 'Very Good', 'Good', 'Fair', 'Poor']
            ),
            'eventAreas' => collect(self::RATING_AREAS)->map(function ($label, $column) use ($feedback) {
                return [
                    'label' => $label,
                    'breakdown' => $this->breakdown($feedback->pluck($column), ['Excellent', 'Good', 'Fair', 'Poor']),
                ];
            })->values(),
            'futureParticipation' => $this->breakdown(
                $feedback->pluck('future_participation'),
                ['Definitely', 'Probably', 'Not sure', 'Probably not']
            ),
            'activitiesInterest' => $this->breakdown(
                $feedback->pluck('activities_interest')->flatten()->filter(),
                ['Networking', 'Career & Business Opportunities', 'Social Gatherings', 'Sports & Recreation', 'Community/Charity Activities', 'Mentorship', 'Professional Development', 'Other']
            ),
            'entries' => $feedback->map(function ($item) {
                return [
                    'id' => $item->id,
                    'created_at' => $item->created_at,
                    'overall_experience' => $item->overall_experience,
                    'best_part' => $item->best_part,
                    'improvements' => $item->improvements,
                    'future_suggestions' => $item->future_suggestions,
                    'other_comments' => $item->other_comments,
                ];
            }),
            'overallScore' => $this->averageScore(
                $feedback->pluck('overall_experience'),
                ['Excellent' => 100, 'Very Good' => 75, 'Good' => 50, 'Fair' => 25, 'Poor' => 0]
            ),
            'areaScores' => collect(self::RATING_AREAS)->map(function ($label, $column) use ($feedback) {
                return [
                    'label' => $label,
                    'score' => $this->averageScore(
                        $feedback->pluck($column),
                        ['Excellent' => 100, 'Good' => 66.7, 'Fair' => 33.3, 'Poor' => 0]
                    ),
                ];
            })->sortByDesc('score')->values(),
        ]);
    }

    /**
     * Admin: delete a feedback entry.
     */
    public function destroy(SmosaFeedback $smosaFeedback)
    {
        $smosaFeedback->delete();

        return back()->with('success', 'Feedback entry deleted.');
    }

    /**
     * Average a 0-100 weighted score across all responses for one question.
     */
    private function averageScore($values, array $weights): float
    {
        $total = $values->count();

        if ($total === 0) {
            return 0;
        }

        $sum = $values->reduce(fn ($carry, $v) => $carry + ($weights[$v] ?? 0), 0);

        return round($sum / $total, 1);
    }

    /**
     * Build a [{label, count, percent}] breakdown for a fixed set of option labels.
     */
    private function breakdown($values, array $options): array
    {
        $total = $values->count();

        return collect($options)->map(function ($option) use ($values, $total) {
            $count = $values->filter(fn ($v) => $v === $option)->count();

            return [
                'label' => $option,
                'count' => $count,
                'percent' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
            ];
        })->values()->all();
    }
}
