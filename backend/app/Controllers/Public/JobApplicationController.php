<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\JobApplicationService;

class JobApplicationController extends Controller
{
    private JobApplicationService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new JobApplicationService();
    }

    public function store(): void
    {
        $result = $this->service->submit($this->input(), $_FILES['cv'] ?? null);
        $result['ok'] ? $this->success([], 'Thank you — your application has been received.') : $this->validationError($result['errors']);
    }
}
