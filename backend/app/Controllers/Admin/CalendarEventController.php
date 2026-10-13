<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\CalendarEventService;

class CalendarEventController extends Controller
{
    private CalendarEventService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new CalendarEventService();
    }

    public function index(): void
    {
        $this->success(['calendar_events' => $this->service->all()]);
    }

    public function store(): void
    {
        $result = $this->service->create($this->input());
        $result['ok'] ? $this->success(['calendar_event' => $this->service->find($result['id'])], 'Event created') : $this->validationError($result['errors']);
    }

    public function update(string $id): void
    {
        $result = $this->service->update((int) $id, $this->input());
        if (!$result['ok']) {
            $this->error($result['errors']['general'] ?? 'Validation failed', isset($result['errors']['general']) ? 404 : 422, $result['errors']);
            return;
        }
        $this->success(['calendar_event' => $this->service->find($result['id'])], 'Event updated');
    }

    public function toggleCompleted(string $id): void
    {
        $result = $this->service->toggleCompleted((int) $id);
        $result['ok'] ? $this->success(['calendar_event' => $this->service->find((int) $id)], 'Updated') : $this->notFound();
    }

    public function destroy(string $id): void
    {
        $this->service->delete((int) $id) ? $this->success([], 'Event deleted') : $this->notFound();
    }
}
