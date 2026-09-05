<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\SlideService;

class SlideController extends Controller
{
    private SlideService $slideService;

    public function __construct()
    {
        parent::__construct();
        $this->slideService = new SlideService();
    }

    public function index(): void
    {
        $this->success(['slides' => $this->slideService->allWithUrls()]);
    }

    public function store(): void
    {
        $result = $this->slideService->create($this->input(), $_FILES['image'] ?? null, $_FILES['video'] ?? null);

        if (!$result['ok']) {
            $this->validationError($result['errors']);
            return;
        }

        $this->success(['slides' => $this->slideService->allWithUrls()], 'Slide uploaded successfully');
    }

    public function destroy(string $id): void
    {
        if (!$this->slideService->delete((int) $id)) {
            $this->notFound('Slide not found');
            return;
        }

        $this->success([], 'Slide deleted successfully');
    }
}
