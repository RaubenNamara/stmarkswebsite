<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\CalendarEvent;
use StMarks\Shared\Support\PublicSiteBuildService;

class CalendarEventService extends Service
{
    private const RULES = ['title' => ['required', 'max:255'], 'event_date' => ['required', 'date']];

    public function __construct(private CalendarEvent $model = new CalendarEvent())
    {
    }

    /** Chronological (soonest first) - the natural order for both the admin list and the public calendar. */
    public function all(): array
    {
        return $this->model->all([], ['event_date' => 'ASC']);
    }

    public function find(int $id): ?array
    {
        return $this->model->find($id);
    }

    public function create(array $data): array
    {
        $errors = $this->validate($data, self::RULES);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $id = $this->model->create($this->prepareRow($data));
        if ($id === false) {
            return ['ok' => false, 'errors' => ['general' => 'Failed to create']];
        }
        PublicSiteBuildService::trigger();
        return ['ok' => true, 'id' => $id];
    }

    public function update(int $id, array $data): array
    {
        if (!$this->model->find($id)) {
            return ['ok' => false, 'errors' => ['general' => 'Not found']];
        }

        $errors = $this->validate($data, self::RULES);
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $this->model->update($id, $this->prepareRow($data));
        PublicSiteBuildService::trigger();
        return ['ok' => true, 'id' => $id];
    }

    public function toggleCompleted(int $id): array
    {
        $existing = $this->model->find($id);
        if (!$existing) {
            return ['ok' => false, 'errors' => ['general' => 'Not found']];
        }
        $this->model->update($id, ['is_completed' => $existing['is_completed'] ? 0 : 1]);
        PublicSiteBuildService::trigger();
        return ['ok' => true];
    }

    public function delete(int $id): bool
    {
        $deleted = $this->model->delete($id);
        if ($deleted) {
            PublicSiteBuildService::trigger();
        }
        return $deleted;
    }

    private function prepareRow(array $data): array
    {
        // Not htmlspecialchars()'d - this API has no raw-HTML output context, only Vue's {{ }}
        // text interpolation on both the admin and public frontends, which already escapes
        // safely at render time. Escaping here too just leaves literal "&amp;" etc. on screen.
        return [
            'title' => trim((string) $data['title']),
            'description' => isset($data['description']) && trim((string) $data['description']) !== ''
                ? trim((string) $data['description'])
                : null,
            'event_date' => date('Y-m-d', strtotime((string) $data['event_date'])),
        ];
    }
}
