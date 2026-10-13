<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\ChatbotConversation;
use StMarks\Shared\Models\ChatbotMessage;
use Throwable;

/**
 * Records what visitors ask the website chatbot (anonymously) and reports on it for the admin panel.
 *
 * Privacy rules enforced HERE, whatever the browser sends:
 *  - visitors are only a random id; no raw IP address is stored (a salted, monthly-rotating hash at most);
 *  - the question text is redacted before saving: emails, phone-like / long numbers and anything that
 *    looks like a password or PIN are replaced, and messages the browser flagged as private are not kept;
 *  - lengths are capped, kinds are whitelisted, and rows older than RETENTION_DAYS are deleted automatically.
 */
class ChatbotAnalyticsService extends Service
{
    /** Conversations older than this are purged automatically. */
    public const RETENTION_DAYS = 365;

    private const KINDS = ['answer', 'greeting', 'thanks', 'goodbye', 'help', 'identity', 'clarify', 'fallback', 'privacy', 'ack'];
    private const HIDDEN = '[hidden - looked like private information]';

    public function __construct(
        private ChatbotConversation $conversations = new ChatbotConversation(),
        private ChatbotMessage $messages = new ChatbotMessage()
    ) {
    }

    /** Removes anything personal-looking from free text. */
    public static function redact(string $text, bool $keepLines = false): string
    {
        if ($keepLines) {
            // keep line breaks (so a stored reply still reads as a list), but tidy spacing within and between lines
            $text = implode("\n", array_map(fn ($l) => trim(preg_replace('/[ \t]+/u', ' ', $l) ?? $l), preg_split('/\R/u', $text) ?: []));
            $text = trim(preg_replace('/\n{3,}/', "\n\n", $text) ?? $text);
        } else {
            $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
        }
        $text = preg_replace('/[\w.+-]+@[\w-]+\.[\w.-]+/u', '[email]', $text) ?? $text;
        $text = preg_replace('/\b(password|passcode|pin|cvv|otp)\b\s*(is|was|=|:)?\s*\S+/iu', '$1 [hidden]', $text) ?? $text;
        // phone-like or long numbers: 7+ digits, allowing spaces / dashes / dots / a leading +
        $text = preg_replace('/\+?\d[\d\s().-]{5,}\d/u', '[number]', $text) ?? $text;
        return mb_substr($text, 0, 300);
    }

    private static function validUid(mixed $v): ?string
    {
        return is_string($v) && preg_match('/^[a-f0-9]{16,32}$/', $v) ? $v : null;
    }

    private static function deviceFrom(string $userAgent): string
    {
        return preg_match('/Mobi|Android|iPhone|iPad/i', $userAgent) ? 'mobile' : 'desktop';
    }

    /**
     * Stores one exchange. Returns false (and stores nothing) for malformed input.
     * @param array<string, mixed> $in visitor, conversation, question, kind, ref, fixed, page, preview
     */
    public function record(array $in, string $ip, string $userAgent): bool
    {
        $visitor = self::validUid($in['visitor'] ?? null);
        $conversation = self::validUid($in['conversation'] ?? null);
        $kind = is_string($in['kind'] ?? null) ? $in['kind'] : '';
        $question = is_string($in['question'] ?? null) ? $in['question'] : '';
        if (!$visitor || !$conversation || !in_array($kind, self::KINDS, true) || trim($question) === '') {
            return false;
        }

        $question = $kind === 'privacy' ? self::HIDDEN : self::redact($question);
        if ($question === '') {
            return false;
        }
        $ref = isset($in['ref']) && is_string($in['ref']) && preg_match('/^[a-z0-9_.:-]{1,40}$/i', $in['ref']) ? $in['ref'] : null;
        $preview = isset($in['preview']) && is_string($in['preview']) && $kind !== 'privacy' ? self::redact($in['preview'], true) : null;
        $page = isset($in['page']) && is_string($in['page']) && preg_match('#^/[A-Za-z0-9/_\-.]{0,148}$#', $in['page']) ? $in['page'] : null;
        $unanswered = in_array($kind, ChatbotMessage::UNANSWERED, true);

        try {
            $conv = $this->conversations->findByUid($conversation);
            if (!$conv) {
                $id = $this->conversations->create([
                    'conversation_uid' => $conversation,
                    'visitor_uid' => $visitor,
                    'device' => self::deviceFrom($userAgent),
                    'page_path' => $page,
                    // salted, rotates monthly: good enough to spot a flood from one address, useless for tracking a person
                    'ip_hash' => substr(hash('sha256', $ip . '|' . date('Y-m') . '|chatbot'), 0, 16),
                    'started_date' => date('Y-m-d'),
                ]);
                if ($id === false) {
                    return false;
                }
                $conv = ['id' => $id, 'visitor_uid' => $visitor];
            } elseif ($conv['visitor_uid'] !== $visitor) {
                return false; // someone else's conversation id
            }

            $this->messages->create([
                'conversation_id' => (int) $conv['id'],
                'question' => $question,
                'answer_kind' => $kind,
                'answer_ref' => $ref,
                'answered' => $unanswered ? 0 : 1,
                'spelling_fixed' => !empty($in['fixed']) ? 1 : 0,
                'answer_preview' => $preview,
                'message_date' => date('Y-m-d'),
                'message_hour' => (int) date('G'),
            ]);
            $this->conversations->bump((int) $conv['id'], $unanswered);

            // housekeeping on ~1% of writes: no cron needed
            if (random_int(1, 100) === 1) {
                $this->conversations->deleteOlderThan(date('Y-m-d', strtotime('-' . self::RETENTION_DAYS . ' days')));
            }
            return true;
        } catch (Throwable) {
            return false; // analytics must never break the visitor's chat
        }
    }

    /** Everything the admin overview needs for one date range. */
    public function summary(string $from, string $to): array
    {
        $people = $this->conversations->peopleBetween($from, $to);
        $totals = $this->messages->totalsBetween($from, $to);
        $answeredPct = $totals['messages'] > 0 ? round((1 - $totals['unanswered'] / $totals['messages']) * 100, 1) : null;

        return [
            'from' => $from,
            'to' => $to,
            'visitors' => $people['visitors'],
            'new_visitors' => $people['new_visitors'],
            'returning_visitors' => $people['returning_visitors'],
            'conversations' => $people['conversations'],
            'messages' => $totals['messages'],
            'unanswered' => $totals['unanswered'],
            'answered_percent' => $answeredPct,
            'spelling_fixed' => $totals['spelling_fixed'],
            'avg_messages_per_conversation' => $people['conversations'] > 0 ? round($totals['messages'] / $people['conversations'], 1) : 0,
            'devices' => $people['devices'],
            'daily' => $this->messages->dailyBetween($from, $to),
            'top_questions' => $this->messages->topQuestions($from, $to),
            'unanswered_questions' => $this->messages->unansweredQuestions($from, $to),
            'by_topic' => $this->messages->byTopic($from, $to),
            'by_hour' => $this->messages->byHour($from, $to),
            'retention_days' => self::RETENTION_DAYS,
        ];
    }

    public function messagesPage(string $from, string $to, string $filter, string $q, int $page, int $limit): array
    {
        return $this->messages->paginateBetween($from, $to, $filter, $q, $page, $limit);
    }

    public function conversationsPage(string $from, string $to, int $page, int $limit): array
    {
        return $this->conversations->paginateBetween($from, $to, $page, $limit);
    }

    public function conversation(int $id): ?array
    {
        $c = $this->conversations->find($id);
        if (!$c) {
            return null;
        }
        unset($c['ip_hash']);
        return ['conversation' => $c, 'messages' => $this->messages->forConversation($id)];
    }

    public function deleteConversation(int $id): bool
    {
        return $this->conversations->delete($id); // messages go with it (ON DELETE CASCADE)
    }

    /** Deletes conversations last active more than $days ago; returns how many were removed. */
    public function purgeOlderThan(int $days): int
    {
        $days = max(0, min(3650, $days));
        return $this->conversations->deleteOlderThan(date('Y-m-d', strtotime("-{$days} days")));
    }

    public function exportRows(string $from, string $to): array
    {
        return $this->messages->exportBetween($from, $to);
    }
}
