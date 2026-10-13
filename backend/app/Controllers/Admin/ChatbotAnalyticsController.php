<?php

declare(strict_types=1);

namespace StMarks\Backend\Controllers\Admin;

use StMarks\Backend\Controllers\Controller;
use StMarks\Shared\Services\ChatbotAnalyticsService;

/** Admin reports on how visitors use the website chatbot. */
class ChatbotAnalyticsController extends Controller
{
    private ChatbotAnalyticsService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ChatbotAnalyticsService();
    }

    public function summary(): void
    {
        [$from, $to] = $this->dateRange();
        $this->success($this->service->summary($from, $to));
    }

    public function messages(): void
    {
        [$from, $to] = $this->dateRange();
        $filter = in_array($this->query('filter'), ['unanswered', 'answered'], true) ? (string) $this->query('filter') : 'all';
        $q = mb_substr(trim((string) $this->query('q', '')), 0, 100);
        $page = max(1, (int) $this->query('page', 1));
        $limit = min(100, max(1, (int) $this->query('limit', 25)));

        $this->success($this->service->messagesPage($from, $to, $filter, $q, $page, $limit));
    }

    public function conversations(): void
    {
        [$from, $to] = $this->dateRange();
        $page = max(1, (int) $this->query('page', 1));
        $limit = min(100, max(1, (int) $this->query('limit', 20)));

        $this->success($this->service->conversationsPage($from, $to, $page, $limit));
    }

    public function show(string $id): void
    {
        $data = $this->service->conversation((int) $id);
        if (!$data) {
            $this->notFound('Conversation not found');
            return;
        }
        $this->success($data);
    }

    public function destroy(string $id): void
    {
        $this->service->deleteConversation((int) $id);
        $this->success([], 'Conversation deleted');
    }

    /** Deletes every conversation last active more than `days` ago. */
    public function purge(): void
    {
        $days = (int) $this->input('days', ChatbotAnalyticsService::RETENTION_DAYS);
        $this->success(['deleted' => $this->service->purgeOlderThan($days)], 'Old conversations deleted');
    }

    /** CSV download of the messages in the date range. */
    public function export(): void
    {
        [$from, $to] = $this->dateRange();
        $rows = $this->service->exportRows($from, $to);

        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"chatbot-messages_{$from}_to_{$to}.csv\"");
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel opens accents correctly
        fputcsv($out, ['When', 'Conversation', 'Visitor', 'Device', 'Page', 'Question', 'Bot reply', 'Answered by', 'Spelling corrected', 'Reply preview']);
        foreach ($rows as $r) {
            // a leading = + - @ would be run as a formula by Excel: neutralise it
            $q = preg_match('/^[=+\-@]/', (string) $r['question']) ? "'" . $r['question'] : $r['question'];
            fputcsv($out, [$r['created_at'], $r['conversation_uid'], $r['visitor_uid'], $r['device'], $r['page_path'], $q, $r['answer_kind'], $r['answer_ref'], $r['spelling_fixed'] ? 'yes' : '', $r['answer_preview']]);
        }
        fclose($out);
        exit;
    }

    /** from/to query params (default: last 30 days), clamped to a sane YYYY-MM-DD range of at most a year. */
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
