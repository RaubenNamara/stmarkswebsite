<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class CalendarEvent extends Model
{
    protected string $table = 'calendar_events';
    protected array $fillable = ['title', 'description', 'event_date', 'is_completed'];
    protected array $searchable = ['title', 'description'];
}
