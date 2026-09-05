<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmosaFeedback extends Model
{
    protected $table = 'smosa_feedbacks';

    protected $fillable = [
        'overall_experience',
        'rating_event_organization',
        'rating_communication',
        'rating_venue_setup',
        'rating_programme_activities',
        'rating_food_refreshments',
        'rating_entertainment',
        'rating_guest_experience',
        'rating_time_management',
        'rating_photography_video',
        'best_part',
        'improvements',
        'future_suggestions',
        'other_comments',
        'future_participation',
        'activities_interest',
        'activities_other',
    ];

    protected $casts = [
        'activities_interest' => 'array',
    ];
}
