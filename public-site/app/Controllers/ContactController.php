<?php

declare(strict_types=1);

namespace StMarks\PublicSite\Controllers;

use StMarks\PublicSite\Support\View;
use StMarks\Shared\Services\ContactService;

class ContactController
{
    private ContactService $service;

    public function __construct()
    {
        $this->service = new ContactService();
    }

    public function show(): void
    {
        View::render('forms/contact', ['errors' => [], 'old' => [], 'success' => false], meta: ['title' => 'Contact Us']);
    }

    public function store(): void
    {
        $result = $this->service->submit($_POST);

        View::render('forms/contact', [
            'errors' => $result['ok'] ? [] : $result['errors'],
            'old' => $result['ok'] ? [] : $_POST,
            'success' => $result['ok'],
        ], meta: ['title' => 'Contact Us']);
    }
}
