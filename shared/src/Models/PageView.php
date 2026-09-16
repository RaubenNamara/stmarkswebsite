<?php

declare(strict_types=1);

namespace StMarks\Shared\Models;

class PageView extends Model
{
    protected string $table = 'page_views';
    protected array $fillable = ['page_url', 'page_type', 'page_id', 'ip_address', 'user_agent', 'view_date'];
    protected bool $timestamps = true;

    /** One row per day across [from, to] (inclusive), zero-filled so charts don't have gaps. */
    public function dailyTotalsBetween(string $from, string $to): array
    {
        $stmt = $this->query(
            "SELECT view_date, COUNT(*) as views FROM {$this->qi($this->table)}
             WHERE view_date BETWEEN :from AND :to
             GROUP BY view_date ORDER BY view_date ASC",
            ['from' => $from, 'to' => $to]
        );
        $byDate = [];
        foreach ($stmt->fetchAll() as $row) {
            $byDate[$row['view_date']] = (int) $row['views'];
        }

        $result = [];
        for ($cursor = strtotime($from); $cursor <= strtotime($to); $cursor = strtotime('+1 day', $cursor)) {
            $date = date('Y-m-d', $cursor);
            $result[] = ['date' => $date, 'views' => $byDate[$date] ?? 0];
        }
        return $result;
    }

    /** Views grouped by top-level page (page_type), most-visited first, across [from, to]. */
    public function totalsByTypeBetween(string $from, string $to): array
    {
        $stmt = $this->query(
            "SELECT page_type, COUNT(*) as views FROM {$this->qi($this->table)}
             WHERE view_date BETWEEN :from AND :to
             GROUP BY page_type ORDER BY views DESC",
            ['from' => $from, 'to' => $to]
        );
        return array_map(
            fn ($row) => ['page_type' => $row['page_type'] ?? 'other', 'views' => (int) $row['views']],
            $stmt->fetchAll()
        );
    }
}
