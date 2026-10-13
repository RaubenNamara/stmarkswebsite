<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Shared\Services\CalendarEventService;

class CalendarEventController extends AbstractPublicListController
{
    protected string $collectionKey = 'calendar_events';

    public function __construct()
    {
        parent::__construct();
        $this->service = new CalendarEventService();
    }
}
