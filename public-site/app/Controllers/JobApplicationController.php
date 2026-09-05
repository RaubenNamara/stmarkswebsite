<?php

declare(strict_types=1);

namespace StMarks\PublicSite\Controllers;

use StMarks\PublicSite\Support\View;
use StMarks\Shared\Services\JobApplicationService;

class JobApplicationController
{
    private JobApplicationService $service;

    public function __construct()
    {
        $this->service = new JobApplicationService();
    }

    public function show(): void
    {
        View::render('forms/apply', ['errors' => [], 'old' => [], 'success' => false], meta: ['title' => 'Apply - Careers']);
    }

    public function store(): void
    {
        $result = $this->service->submit($_POST, $_FILES['cv'] ?? null);

        View::render('forms/apply', [
            'errors' => $result['ok'] ? [] : $result['errors'],
            'old' => $result['ok'] ? [] : $_POST,
            'success' => $result['ok'],
        ], meta: ['title' => 'Apply - Careers']);
    }
}
