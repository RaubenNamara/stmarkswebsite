<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class SecurityEvent extends Model
{
    protected string $table = 'security_events';
    protected array $fillable = ['event_type', 'ip_address', 'user_agent', 'identifier', 'request_path', 'details', 'event_date'];
    protected array $searchable = ['ip_address', 'identifier', 'request_path'];
    protected bool $timestamps = true;

    /** One row per day across [from, to] (inclusive), zero-filled so charts don't have gaps. */
    public function dailyTotalsBetween(string $from, string $to, ?string $eventType = null): array
    {
        $params = ['from' => $from, 'to' => $to];
        $typeSql = '';
        if ($eventType) {
            $typeSql = ' AND event_type = :event_type';
            $params['event_type'] = $eventType;
        }
        $stmt = $this->query(
            "SELECT event_date, COUNT(*) as total FROM {$this->qi($this->table)}
             WHERE event_date BETWEEN :from AND :to{$typeSql}
             GROUP BY event_date ORDER BY event_date ASC",
            $params
        );
        $byDate = [];
        foreach ($stmt->fetchAll() as $row) {
            $byDate[$row['event_date']] = (int) $row['total'];
        }

        $result = [];
        for ($cursor = strtotime($from); $cursor <= strtotime($to); $cursor = strtotime('+1 day', $cursor)) {
            $date = date('Y-m-d', $cursor);
            $result[] = ['date' => $date, 'total' => $byDate[$date] ?? 0];
        }
        return $result;
    }

    /** Event counts grouped by type across [from, to], most frequent first. */
    public function totalsByTypeBetween(string $from, string $to): array
    {
        $stmt = $this->query(
            "SELECT event_type, COUNT(*) as total FROM {$this->qi($this->table)}
             WHERE event_date BETWEEN :from AND :to
             GROUP BY event_type ORDER BY total DESC",
            ['from' => $from, 'to' => $to]
        );
        return array_map(
            fn ($row) => ['event_type' => $row['event_type'], 'total' => (int) $row['total']],
            $stmt->fetchAll()
        );
    }

    public function countBetween(string $from, string $to, ?string $eventType = null): int
    {
        $where = ['event_date BETWEEN :from AND :to'];
        $params = ['from' => $from, 'to' => $to];
        if ($eventType) {
            $where[] = 'event_type = :event_type';
            $params['event_type'] = $eventType;
        }
        $stmt = $this->query(
            'SELECT COUNT(*) as total FROM ' . $this->qi($this->table) . ' WHERE ' . implode(' AND ', $where),
            $params
        );
        return (int) $stmt->fetch()['total'];
    }

    /** Paginated listing across [from, to], optionally filtered by event_type, newest first. */
    public function paginateBetween(string $from, string $to, ?string $eventType, int $page, int $limit): array
    {
        $page = max(1, $page);
        $limit = min(200, max(1, $limit));
        $offset = ($page - 1) * $limit;

        $where = ['event_date BETWEEN :from AND :to'];
        $params = ['from' => $from, 'to' => $to];
        if ($eventType) {
            $where[] = 'event_type = :event_type';
            $params['event_type'] = $eventType;
        }
        $whereSql = ' WHERE ' . implode(' AND ', $where);

        $countStmt = $this->query("SELECT COUNT(*) as total FROM {$this->qi($this->table)}{$whereSql}", $params);
        $total = (int) $countStmt->fetch()['total'];

        $stmt = $this->query(
            "SELECT * FROM {$this->qi($this->table)}{$whereSql} ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        return [
            'data' => $stmt->fetchAll(),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => (int) ceil($total / max($limit, 1)),
            ],
        ];
    }
}
