<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Config\Config;
use Throwable;

/**
 * Builds the public, read-only "knowledge feed" the website chatbot searches: a flat list of
 * sources (calendar events, news, clubs, staff roles, uploaded fee/programme PDFs ...) each with
 * plain text, a title, an optional date and the page it lives on.
 *
 * ONLY content that is already shown on the public website is included. Nothing private is read
 * (no contacts-form messages, job applications, users, page views ...). Text is stripped of HTML,
 * truncated, and one failing table never breaks the rest of the feed.
 */
class ChatbotKnowledgeService
{
    /**
     * Published results PDFs list individual students (names, index numbers, grades). The PDF is
     * public, but the chatbot only links to it by default rather than reading individual students'
     * results aloud. Set to true to let the chatbot quote them.
     */
    private const INDEX_RESULTS_PDF_TEXT = false;

    private const MAX_TEXT = 1500;

    public function __construct(private PdfTextExtractor $pdf = new PdfTextExtractor())
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function sources(): array
    {
        $out = [];

        $this->collect($out, fn () => $this->calendar());
        $this->collect($out, fn () => $this->feePdfs());
        $this->collect($out, fn () => $this->resultsPdfs());
        $this->collect($out, fn () => $this->news());
        $this->collect($out, fn () => $this->simple('post', (new PostService())->published(), 'title', ['content'], fn ($r) => '/posts/' . ($r['slug'] ?? ''), 40));
        $this->collect($out, fn () => $this->simple('voice', (new CampusVoiceService())->published(), 'title', ['excerpt', 'content'], fn ($r) => '/campus-voices/' . ($r['slug'] ?? ''), 30));
        $this->collect($out, fn () => $this->simple('club', (new ClubService())->all(), 'title', ['content'], fn ($r) => '/clubs/' . ($r['slug'] ?? '')));
        $this->collect($out, fn () => $this->simple('activity', (new CoCurricularService())->all(), 'title', ['content'], fn () => '/academics/co-curricular'));
        $this->collect($out, fn () => $this->simple('programme', (new MentorshipService())->all(), 'title', ['description'], fn () => '/empowerment/mentorship'));
        $this->collect($out, fn () => $this->simple('programme', (new ChaplaincyService())->all(), 'title', ['content'], fn () => '/empowerment/chaplaincy'));
        $this->collect($out, fn () => $this->simple('programme', (new GirlBoyTalkService())->all(), 'title', ['description'], fn () => '/empowerment/girl-boy-talk'));
        $this->collect($out, fn () => $this->simple('programme', (new InspirationNightService())->all(), 'title', ['description'], fn () => '/empowerment/inspiration-night', 40, 'date', ['speaker' => 'Speaker']));
        $this->collect($out, fn () => $this->simple('programme', (new ChristmasCantataService())->all(), 'title', ['description'], fn () => '/empowerment/christmas-cantata', 40, 'date', ['choir' => 'Choir']));
        $this->collect($out, fn () => $this->simple('programme', (new StudentLeadershipService())->all(), 'title', ['content'], fn () => '/explore/student-leadership'));
        $this->collect($out, fn () => $this->staff());
        $this->collect($out, fn () => $this->simple('board', (new BoardMemberService())->all(), 'name', ['position'], fn () => '/board-members', 60));
        $this->collect($out, fn () => $this->highAchievers());
        $this->collect($out, fn () => $this->simple('alumni', (new SmosaAlumniService())->all(), 'name', ['profession'], fn () => '/empowerment/smosa-alumni', 40));

        return $out;
    }

    /** @param array<int, array<string, mixed>> $out */
    private function collect(array &$out, callable $producer): void
    {
        try {
            foreach ($producer() as $row) {
                $out[] = $row;
            }
        } catch (Throwable) {
            // One unavailable table must not take the whole feed down.
        }
    }

    private function plain(mixed $value, int $max = self::MAX_TEXT): string
    {
        $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        $text = trim($text);
        return mb_strlen($text) > $max ? rtrim(mb_substr($text, 0, $max)) . '…' : $text;
    }

    /** @return iterable<array<string, mixed>> */
    private function calendar(): iterable
    {
        foreach ((new CalendarEventService())->all() as $e) {
            yield [
                'kind' => 'event',
                'id' => 'event-' . $e['id'],
                'title' => $this->plain($e['title'], 200),
                'text' => $this->plain($e['description'] ?? '', 400),
                'date' => $e['event_date'],
                'completed' => (bool) ($e['is_completed'] ?? false),
                'url' => '/academics/school-calendar#event-' . $e['id'],
            ];
        }
    }

    /** @return iterable<array<string, mixed>> */
    private function news(): iterable
    {
        foreach ((new NewsService())->published(80) as $n) {
            yield [
                'kind' => 'news',
                'id' => 'news-' . $n['id'],
                'title' => $this->plain($n['title'], 300),
                'text' => $this->plain(($n['content'] ?? '') ?: ($n['excerpt'] ?? '')),
                'date' => substr((string) ($n['published_at'] ?? ''), 0, 10) ?: null,
                'url' => '/news/' . $n['slug'],
            ];
        }
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @param string[] $textFields first non-empty field is used as the body
     * @param array<string, string> $extraLabels extra "label: value" parts appended to the text
     * @return iterable<array<string, mixed>>
     */
    private function simple(string $kind, array $rows, string $titleField, array $textFields, callable $url, int $limit = 60, ?string $dateField = null, array $extraLabels = []): iterable
    {
        foreach (array_slice($rows, 0, $limit) as $r) {
            $body = '';
            foreach ($textFields as $f) {
                if (!empty($r[$f])) {
                    $body = $this->plain($r[$f]);
                    break;
                }
            }
            $extras = [];
            foreach ($extraLabels as $field => $label) {
                if (!empty($r[$field])) {
                    $extras[] = $label . ': ' . $this->plain($r[$field], 120);
                }
            }
            $title = $this->plain($r[$titleField] ?? '', 300);
            if ($title === '') {
                continue;
            }
            yield [
                'kind' => $kind,
                // several tables share a kind (e.g. 'programme'), so the page is part of the id to keep it unique
                'id' => $kind . '-' . substr(md5((string) $url($r)), 0, 6) . '-' . ($r['id'] ?? md5($title)),
                'title' => $title,
                'text' => trim(implode('. ', array_filter([$body, implode('. ', $extras)]))),
                'date' => $dateField && !empty($r[$dateField]) ? (string) $r[$dateField] : null,
                'url' => $url($r),
            ];
        }
    }

    /** @return iterable<array<string, mixed>> */
    private function staff(): iterable
    {
        foreach ((new StaffService())->all(['sort_order' => 'ASC']) as $s) {
            $name = $this->plain($s['name'] ?? '', 120);
            if ($name === '') {
                continue;
            }
            yield [
                'kind' => 'staff',
                'id' => 'staff-' . $s['id'],
                'title' => $name,
                'text' => $this->plain(trim(($s['department'] ?? '') . ' (' . ($s['category'] ?? '') . ')'), 200),
                'url' => '/staff',
            ];
        }
    }

    /** @return iterable<array<string, mixed>> */
    private function highAchievers(): iterable
    {
        foreach ((new HighAchieverService())->all() as $h) {
            $parts = array_filter([
                !empty($h['exam']) ? $this->plain($h['exam'], 40) . (!empty($h['year']) ? ' ' . $this->plain($h['year'], 8) : '') : '',
                !empty($h['division']) ? $this->plain($h['division'], 80) : '',
                !empty($h['description']) ? $this->plain($h['description'], 300) : '',
            ]);
            yield [
                'kind' => 'achiever',
                'id' => 'achiever-' . $h['id'],
                'title' => $this->plain($h['name'] ?? '', 120),
                'text' => implode('. ', $parts),
                'url' => '/academics/high-achievers',
            ];
        }
    }

    /** Fee structures, term programmes and other uploaded documents on the Fees, Rules & Calendar page. */
    private function feePdfs(): iterable
    {
        foreach ((new FeeStructureService())->all() as $d) {
            yield $this->pdfSource('document', 'fee-structures', $d, (string) ($d['file_path'] ?? ''), true);
        }
    }

    private function resultsPdfs(): iterable
    {
        foreach ((new PerformanceService())->all() as $d) {
            yield $this->pdfSource('results', 'performances', $d, (string) ($d['pdf'] ?? ''), self::INDEX_RESULTS_PDF_TEXT);
        }
    }

    /** @param array<string, mixed> $d */
    private function pdfSource(string $kind, string $endpoint, array $d, string $storedPath, bool $readText): array
    {
        $text = null;
        if ($readText && str_starts_with($storedPath, '/uploads/')) {
            $text = $this->pdf->extract(Config::getUploadsPath(substr($storedPath, strlen('/uploads/'))));
        }

        return [
            'kind' => $kind,
            'id' => $kind . '-' . $d['id'],
            'title' => $this->plain($d['title'], 200),
            'text' => $text ?? '',
            // The frontend prefixes its own base path; uploaded files are served through this endpoint.
            'endpoint' => str_starts_with($storedPath, '/uploads/') ? "/{$endpoint}/{$d['id']}/pdf" : null,
            'file_url' => $d['file_url'] ?? null,
            'readable' => $text !== null,
            'url' => '/fee-structures',
        ];
    }
}
