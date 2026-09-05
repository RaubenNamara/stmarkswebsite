<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\SmosaFeedbackService;

/** Cookie-gated single-submission form, matching the old app's behavior. */
class SmosaFeedbackController extends Controller
{
    private const COOKIE_NAME = 'smosa_feedback_submitted';

    private SmosaFeedbackService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new SmosaFeedbackService();
    }

    public function status(): void
    {
        $this->success(['already_submitted' => isset($_COOKIE[self::COOKIE_NAME])]);
    }

    public function store(): void
    {
        if (isset($_COOKIE[self::COOKIE_NAME])) {
            $this->error('You\'ve already submitted feedback for this event.', 409);
            return;
        }

        $result = $this->service->submit($this->input());
        if (!$result['ok']) {
            $this->validationError($result['errors']);
            return;
        }

        setcookie(self::COOKIE_NAME, '1', time() + 60 * 60 * 24 * 365, '/');
        $this->success([], 'Thank you for your feedback!');
    }
}
