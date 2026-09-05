<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Public;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\ClubService;

class ClubController extends Controller
{
    private ClubService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ClubService();
    }

    public function index(): void
    {
        $this->success(['clubs' => $this->service->all()]);
    }

    public function show(string $slug): void
    {
        $club = $this->service->findBySlug($slug);
        if (!$club) {
            $this->notFound('Club not found');
            return;
        }

        $this->success(['club' => $club]);
    }
}
