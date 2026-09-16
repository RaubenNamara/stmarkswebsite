<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\SecurityEventService;

class SecurityController extends Controller
{
    private SecurityEventService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new SecurityEventService();
    }

    public function index(): void
    {
        [$from, $to] = $this->dateRange();
        $page = max(1, (int) $this->query('page', 1));
        $limit = min(100, max(1, (int) $this->query('limit', 25)));
        $eventType = $this->query('event_type') ?: null;

        $this->success($this->service->paginate($from, $to, $eventType, $page, $limit));
    }

    public function summary(): void
    {
        [$from, $to] = $this->dateRange();
        $this->success($this->service->summary($from, $to));
    }

    /** Reads from/to query params (default: last 30 days), clamped to a sane YYYY-MM-DD range of at most a year. */
    private function dateRange(): array
    {
        $to = (string) $this->query('to', date('Y-m-d'));
        $from = (string) $this->query('from', date('Y-m-d', strtotime('-29 days')));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $from = date('Y-m-d', strtotime('-29 days'));
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $to = date('Y-m-d');
        }
        if (strtotime($from) > strtotime($to)) {
            [$from, $to] = [$to, $from];
        }
        if (strtotime($to) - strtotime($from) > 366 * 86400) {
            $from = date('Y-m-d', strtotime($to . ' -366 days'));
        }

        return [$from, $to];
    }
}
