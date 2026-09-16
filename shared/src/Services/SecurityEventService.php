<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\SecurityEvent;
use Throwable;

/**
 * Records and reports on suspicious requests (failed admin logins, rate-limit hits). record() is
 * called directly from AuthService/RateLimitMiddleware, inline with the request it's observing -
 * a logging failure must never break that request, so failures are swallowed rather than surfaced.
 */
class SecurityEventService extends Service
{
    public function __construct(private SecurityEvent $model = new SecurityEvent())
    {
    }

    public function record(
        string $eventType,
        string $ip,
        string $userAgent,
        ?string $identifier = null,
        ?string $requestPath = null,
        ?string $details = null
    ): void {
        try {
            $this->model->create([
                'event_type' => $eventType,
                'ip_address' => $ip,
                'user_agent' => mb_substr($userAgent, 0, 255),
                'identifier' => $identifier !== null ? mb_substr($identifier, 0, 255) : null,
                'request_path' => $requestPath !== null ? mb_substr($requestPath, 0, 255) : null,
                'details' => $details !== null ? mb_substr($details, 0, 255) : null,
                'event_date' => date('Y-m-d'),
            ]);
        } catch (Throwable) {
        }
    }

    /** Admin security report: totals, a daily trend and a type breakdown for one date range. */
    public function summary(string $from, string $to): array
    {
        return [
            'total_events' => $this->model->countBetween($from, $to),
            'today_events' => $this->model->count(['event_date' => date('Y-m-d')]),
            'failed_logins' => $this->model->countBetween($from, $to, 'failed_login'),
            'rate_limit_hits' => $this->model->countBetween($from, $to, 'rate_limit_exceeded'),
            'daily_totals' => $this->model->dailyTotalsBetween($from, $to),
            'by_type' => $this->model->totalsByTypeBetween($from, $to),
        ];
    }

    public function paginate(string $from, string $to, ?string $eventType, int $page, int $limit): array
    {
        return $this->model->paginateBetween($from, $to, $eventType, $page, $limit);
    }
}
