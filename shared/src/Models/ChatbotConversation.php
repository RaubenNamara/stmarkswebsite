<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

/** One chatbot conversation (a visitor can have several). See the chatbot tables migration for the privacy notes. */
class ChatbotConversation extends Model
{
    protected string $table = 'chatbot_conversations';
    protected array $fillable = [
        'conversation_uid', 'visitor_uid', 'device', 'page_path', 'ip_hash', 'started_date',
        'message_count', 'unanswered_count', 'last_message_at',
    ];
    protected bool $timestamps = true;

    public function findByUid(string $uid): ?array
    {
        $stmt = $this->query("SELECT * FROM {$this->qi($this->table)} WHERE conversation_uid = :uid LIMIT 1", ['uid' => $uid]);
        return $stmt->fetch() ?: null;
    }

    /** Adds one exchange to the conversation's running counters. */
    public function bump(int $id, bool $unanswered): void
    {
        $this->query(
            "UPDATE {$this->qi($this->table)}
             SET message_count = message_count + 1,
                 unanswered_count = unanswered_count + :un,
                 last_message_at = :now, updated_at = :now_again
             WHERE id = :id",
            ['un' => $unanswered ? 1 : 0, 'now' => date('Y-m-d H:i:s'), 'now_again' => date('Y-m-d H:i:s'), 'id' => $id]
        );
    }

    /** Visitors / conversations / new-vs-returning for [from, to]. */
    public function peopleBetween(string $from, string $to): array
    {
        $range = ['from' => $from, 'to' => $to];
        $row = $this->query(
            "SELECT COUNT(*) AS conversations, COUNT(DISTINCT visitor_uid) AS visitors
             FROM {$this->qi($this->table)} WHERE started_date BETWEEN :from AND :to",
            $range
        )->fetch();

        // "New" visitors: their very first conversation ever falls inside the range.
        $new = $this->query(
            "SELECT COUNT(*) AS n FROM (
                SELECT visitor_uid, MIN(started_date) AS first_seen FROM {$this->qi($this->table)} GROUP BY visitor_uid
             ) t WHERE first_seen BETWEEN :from AND :to",
            $range
        )->fetch();

        $devices = $this->query(
            "SELECT device, COUNT(*) AS total FROM {$this->qi($this->table)}
             WHERE started_date BETWEEN :from AND :to GROUP BY device ORDER BY total DESC",
            $range
        )->fetchAll();

        $visitors = (int) $row['visitors'];
        $newVisitors = min($visitors, (int) $new['n']);

        return [
            'conversations' => (int) $row['conversations'],
            'visitors' => $visitors,
            'new_visitors' => $newVisitors,
            'returning_visitors' => $visitors - $newVisitors,
            'devices' => array_map(fn ($d) => ['device' => $d['device'], 'total' => (int) $d['total']], $devices),
        ];
    }

    /** Paginated conversation list, newest activity first, each with the visitor's first question. */
    public function paginateBetween(string $from, string $to, int $page, int $limit): array
    {
        $page = max(1, $page);
        $limit = min(100, max(1, $limit));
        $offset = ($page - 1) * $limit;
        $range = ['from' => $from, 'to' => $to];

        $total = (int) $this->query(
            "SELECT COUNT(*) AS total FROM {$this->qi($this->table)} WHERE started_date BETWEEN :from AND :to",
            $range
        )->fetch()['total'];

        $rows = $this->query(
            "SELECT c.id, c.conversation_uid, c.visitor_uid, c.device, c.page_path, c.started_date,
                    c.message_count, c.unanswered_count, c.last_message_at,
                    (SELECT m.question FROM chatbot_messages m WHERE m.conversation_id = c.id ORDER BY m.id ASC LIMIT 1) AS first_question
             FROM {$this->qi($this->table)} c
             WHERE c.started_date BETWEEN :from AND :to
             ORDER BY COALESCE(c.last_message_at, c.created_at) DESC
             LIMIT {$limit} OFFSET {$offset}",
            $range
        )->fetchAll();

        return [
            'data' => $rows,
            'pagination' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'pages' => max(1, (int) ceil($total / $limit))],
        ];
    }

    /** Deletes conversations (and, via the foreign key, their messages) last active before `$cutoff`. */
    public function deleteOlderThan(string $cutoffDate): int
    {
        $stmt = $this->query(
            "DELETE FROM {$this->qi($this->table)} WHERE COALESCE(DATE(last_message_at), started_date) < :cutoff",
            ['cutoff' => $cutoffDate]
        );
        return $stmt->rowCount();
    }
}
