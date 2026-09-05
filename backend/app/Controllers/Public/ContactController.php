<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\ContactService;

class ContactController extends Controller
{
    private ContactService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ContactService();
    }

    public function store(): void
    {
        $result = $this->service->submit($this->input());
        $result['ok'] ? $this->success([], 'Thank you — your message has been sent. We\'ll get back to you soon.') : $this->validationError($result['errors']);
    }
}
