<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\ChatbotAnalyticsService;

/**
 * Receives one anonymous chatbot exchange from the public website. The service redacts and validates
 * everything; this endpoint never reveals why a request was rejected and never fails loudly.
 */
class ChatbotLogController extends Controller
{
    public function store(): void
    {
        (new ChatbotAnalyticsService())->record(
            [
                'visitor' => $this->input('visitor'),
                'conversation' => $this->input('conversation'),
                'question' => $this->input('question'),
                'kind' => $this->input('kind'),
                'ref' => $this->input('ref'),
                'fixed' => $this->input('fixed'),
                'page' => $this->input('page'),
                'preview' => $this->input('preview'),
            ],
            $this->getClientIp(),
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        );

        $this->success();
    }
}
