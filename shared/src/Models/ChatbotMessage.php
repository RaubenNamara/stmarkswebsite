<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

/** One question a visitor asked the chatbot, and how the bot answered it. */
class ChatbotMessage extends Model
{
    protected string $table = 'chatbot_messages';
    protected array $fillable = [
        'conversation_id', 'question', 'answer_kind', 'answer_ref', 'answered', 'spelling_fixed',
        'answer_preview', 'message_date', 'message_hour',
    ];
    protected bool $timestamps = true;

    /** Replies that are small talk or privacy notes, not real questions about the school. */
    public const NOT_A_QUESTION = ['greeting', 'thanks', 'goodbye', 'ack', 'privacy', 'help', 'identity'];
    /** Replies where the bot could not give the visitor an answer. */
    public const UNANSWERED = ['fallback', 'clarify'];

    private function notQuestionSql(): string
    {
        return "'" . implode("','", self::NOT_A_QUESTION) . "'";
    }

    private function unansweredSql(): string
    {
        return "'" . implode("','", self::UNANSWERED) . "'";
    }

    public function totalsBetween(string $from, string $to): array
    {
        $r = $this->query(
            "SELECT COUNT(*) AS messages,
                    COALESCE(SUM(answer_kind IN ({$this->unansweredSql()})), 0) AS unanswered,
                    COALESCE(SUM(spelling_fixed), 0) AS spelling_fixed
             FROM {$this->qi($this->table)} WHERE message_date BETWEEN :from AND :to",
            ['from' => $from, 'to' => $to]
        )->fetch();
        return ['messages' => (int) $r['messages'], 'unanswered' => (int) $r['unanswered'], 'spelling_fixed' => (int) $r['spelling_fixed']];
    }

    /** One row per day across [from, to], zero-filled: messages and distinct conversations. */
    public function dailyBetween(string $from, string $to): array
    {
        $rows = $this->query(
            "SELECT message_date, COUNT(*) AS messages, COUNT(DISTINCT conversation_id) AS conversations
             FROM {$this->qi($this->table)} WHERE message_date BETWEEN :from AND :to GROUP BY message_date",
            ['from' => $from, 'to' => $to]
        )->fetchAll();
        $by = [];
        foreach ($rows as $r) {
            $by[$r['message_date']] = ['messages' => (int) $r['messages'], 'conversations' => (int) $r['conversations']];
        }
        $out = [];
        for ($t = strtotime($from); $t <= strtotime($to); $t = strtotime('+1 day', $t)) {
            $d = date('Y-m-d', $t);
            $out[] = ['date' => $d, 'messages' => $by[$d]['messages'] ?? 0, 'conversations' => $by[$d]['conversations'] ?? 0];
        }
        return $out;
    }

    /** The questions asked most often (small talk excluded), with how often each was answered. */
    public function topQuestions(string $from, string $to, int $limit = 15): array
    {
        $limit = min(50, max(1, $limit));
        $rows = $this->query(
            "SELECT LOWER(question) AS question, COUNT(*) AS total,
                    SUM(answer_kind NOT IN ({$this->unansweredSql()})) AS answered
             FROM {$this->qi($this->table)}
             WHERE message_date BETWEEN :from AND :to AND answer_kind NOT IN ({$this->notQuestionSql()})
             GROUP BY LOWER(question) ORDER BY total DESC, question ASC LIMIT {$limit}",
            ['from' => $from, 'to' => $to]
        )->fetchAll();
        return array_map(fn ($r) => ['question' => $r['question'], 'total' => (int) $r['total'], 'answered' => (int) $r['answered']], $rows);
    }

    /** Questions the bot could not answer - the to-do list for improving its knowledge. */
    public function unansweredQuestions(string $from, string $to, int $limit = 25): array
    {
        $limit = min(100, max(1, $limit));
        $rows = $this->query(
            "SELECT LOWER(question) AS question, COUNT(*) AS total, MAX(created_at) AS last_asked
             FROM {$this->qi($this->table)}
             WHERE message_date BETWEEN :from AND :to AND answer_kind IN ({$this->unansweredSql()})
             GROUP BY LOWER(question) ORDER BY total DESC, last_asked DESC LIMIT {$limit}",
            ['from' => $from, 'to' => $to]
        )->fetchAll();
        return array_map(fn ($r) => ['question' => $r['question'], 'total' => (int) $r['total'], 'last_asked' => $r['last_asked']], $rows);
    }

    /** Which topics (built-in entries or live-data resolvers) answered the most questions. */
    public function byTopic(string $from, string $to, int $limit = 12): array
    {
        $limit = min(40, max(1, $limit));
        $rows = $this->query(
            "SELECT answer_ref, COUNT(*) AS total FROM {$this->qi($this->table)}
             WHERE message_date BETWEEN :from AND :to AND answer_ref IS NOT NULL AND answer_kind NOT IN ({$this->notQuestionSql()})
             GROUP BY answer_ref ORDER BY total DESC LIMIT {$limit}",
            ['from' => $from, 'to' => $to]
        )->fetchAll();
        return array_map(fn ($r) => ['topic' => $r['answer_ref'], 'total' => (int) $r['total']], $rows);
    }

    /** Messages per hour of day (0-23), always 24 entries. */
    public function byHour(string $from, string $to): array
    {
        $rows = $this->query(
            "SELECT message_hour, COUNT(*) AS total FROM {$this->qi($this->table)}
             WHERE message_date BETWEEN :from AND :to GROUP BY message_hour",
            ['from' => $from, 'to' => $to]
        )->fetchAll();
        $by = array_fill(0, 24, 0);
        foreach ($rows as $r) {
            $by[(int) $r['message_hour']] = (int) $r['total'];
        }
        return array_map(fn ($h) => ['hour' => $h, 'total' => $by[$h]], range(0, 23));
    }

    /**
     * Paginated message list, newest first. $filter: 'all' | 'unanswered' | 'answered'; $q: text search.
     * @return array{data: array<int, array<string, mixed>>, pagination: array<string, int>}
     */
    public function paginateBetween(string $from, string $to, string $filter, string $q, int $page, int $limit): array
    {
        $page = max(1, $page);
        $limit = min(200, max(1, $limit));
        $offset = ($page - 1) * $limit;

        $where = ['m.message_date BETWEEN :from AND :to'];
        $params = ['from' => $from, 'to' => $to];
        if ($filter === 'unanswered') {
            $where[] = "m.answer_kind IN ({$this->unansweredSql()})";
        } elseif ($filter === 'answered') {
            $where[] = "m.answer_kind NOT IN ({$this->unansweredSql()})";
        }
        if ($q !== '') {
            $where[] = 'm.question LIKE :q';
            $params['q'] = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';
        }
        $whereSql = ' WHERE ' . implode(' AND ', $where);

        $total = (int) $this->query(
            "SELECT COUNT(*) AS total FROM {$this->qi($this->table)} m{$whereSql}",
            $params
        )->fetch()['total'];

        $rows = $this->query(
            "SELECT m.id, m.conversation_id, m.question, m.answer_kind, m.answer_ref, m.answered, m.spelling_fixed,
                    m.answer_preview, m.created_at, c.device, c.page_path
             FROM {$this->qi($this->table)} m JOIN chatbot_conversations c ON c.id = m.conversation_id
             {$whereSql} ORDER BY m.id DESC LIMIT {$limit} OFFSET {$offset}",
            $params
        )->fetchAll();

        return [
            'data' => $rows,
            'pagination' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'pages' => max(1, (int) ceil($total / $limit))],
        ];
    }

    /** Every message of one conversation, oldest first. */
    public function forConversation(int $conversationId): array
    {
        return $this->query(
            "SELECT id, question, answer_kind, answer_ref, answered, spelling_fixed, answer_preview, created_at
             FROM {$this->qi($this->table)} WHERE conversation_id = :id ORDER BY id ASC",
            ['id' => $conversationId]
        )->fetchAll();
    }

    /** Rows for the CSV export (capped). */
    public function exportBetween(string $from, string $to, int $max = 20000): array
    {
        $max = min(50000, max(1, $max));
        return $this->query(
            "SELECT m.created_at, c.conversation_uid, c.visitor_uid, c.device, c.page_path, m.question,
                    m.answer_kind, m.answer_ref, m.spelling_fixed, m.answer_preview
             FROM {$this->qi($this->table)} m JOIN chatbot_conversations c ON c.id = m.conversation_id
             WHERE m.message_date BETWEEN :from AND :to ORDER BY m.id ASC LIMIT {$max}",
            ['from' => $from, 'to' => $to]
        )->fetchAll();
    }
}
