<?php

declare(strict_types=1);

namespace StMarks\PublicSite\Controllers;

use StMarks\PublicSite\Support\View;
use StMarks\Shared\Services\SmosaFeedbackService;

/** Cookie-gated single-submission form, matching the old app's behavior. */
class SmosaFeedbackController
{
    private const COOKIE_NAME = 'smosa_feedback_submitted';

    private SmosaFeedbackService $service;

    public function __construct()
    {
        $this->service = new SmosaFeedbackService();
    }

    public function show(): void
    {
        View::render('forms/smosa-feedback', [
            'errors' => [],
            'old' => [],
            'success' => false,
            'alreadySubmitted' => isset($_COOKIE[self::COOKIE_NAME]),
        ], meta: ['title' => 'SMOSA Feedback']);
    }

    public function store(): void
    {
        if (isset($_COOKIE[self::COOKIE_NAME])) {
            View::render('forms/smosa-feedback', ['errors' => [], 'old' => [], 'success' => false, 'alreadySubmitted' => true], meta: ['title' => 'SMOSA Feedback']);
            return;
        }

        $result = $this->service->submit($_POST);

        if ($result['ok']) {
            setcookie(self::COOKIE_NAME, '1', time() + 60 * 60 * 24 * 365, '/');
        }

        View::render('forms/smosa-feedback', [
            'errors' => $result['ok'] ? [] : $result['errors'],
            'old' => $result['ok'] ? [] : $_POST,
            'success' => $result['ok'],
            'alreadySubmitted' => false,
        ], meta: ['title' => 'SMOSA Feedback']);
    }
}
