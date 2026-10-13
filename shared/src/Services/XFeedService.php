<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use StMarks\Shared\Config\Config;
use StMarks\Shared\Support\Logger;

/**
 * Latest posts from the school's X account (@Smacon24) for the public homepage's
 * "Latest Updates from SMACON" section. Only official X sources are used, never page scraping:
 *
 *  1. X API v2 (automatic "latest four") - only when X_API_BEARER_TOKEN is set in shared/.env.
 *     The API is prepaid pay-per-use (billed per post read), so it is opt-in and every result is
 *     cached for X_FEED_CACHE_MINUTES to keep the bill to a handful of reads per hour.
 *  2. X's public oEmbed endpoint (no credentials) for the post URLs listed in X_FEED_POST_URLS -
 *     real text, date and permalink straight from X, but no media files or profile image, which
 *     oEmbed doesn't expose (cards then say "includes photo/video" and link to the post instead).
 *  3. Nothing - 'source' => 'none' and an empty list, so the frontend shows its follow-us fallback.
 *
 * The embedded timeline widget was evaluated first and rejected: X answers logged-out visitors'
 * timeline requests with HTTP 429, and syndication.twitter.com is blocked outright by common
 * family/ad-filtering DNS (verified 2026-10-10), so it renders blank for many visitors.
 *
 * Every field is returned as plain text - the frontend escapes it, nothing here is meant as HTML.
 */
class XFeedService extends Service
{
    public const USERNAME = 'Smacon24';
    public const PROFILE_URL = 'https://x.com/' . self::USERNAME;
    private const LIMIT = 4;
    private const API_BASE = 'https://api.x.com/2';
    private const OEMBED_URL = 'https://publish.x.com/oembed';
    // After a failed refresh, wait this long before asking X again (serving stale/empty meanwhile)
    // so an X outage or a rate limit doesn't turn into one outbound request per homepage visit.
    private const RETRY_AFTER_FAILURE_SECONDS = 300;

    public function latest(): array
    {
        $cached = $this->readCache();
        $ttl = max(5, (int) Config::get('X_FEED_CACHE_MINUTES', 60)) * 60;

        if ($cached !== null && $cached['source_key'] === $this->sourceKey() && time() < $cached['expires_at']) {
            return $cached['feed'];
        }

        $feed = $this->fetch();

        if ($feed === null) {
            // Refresh failed - keep serving the last good feed (if it came from the same source)
            // rather than blanking the section, and back off before trying again.
            $stale = ($cached !== null && $cached['source_key'] === $this->sourceKey()) ? $cached['feed'] : $this->emptyFeed();
            $this->writeCache($stale, self::RETRY_AFTER_FAILURE_SECONDS);
            return $stale;
        }

        $this->writeCache($feed, $ttl);
        return $feed;
    }

    /** Returns null when the configured source failed (so the caller can fall back to stale data). */
    private function fetch(): ?array
    {
        if ($this->bearerToken() !== '') {
            return $this->fetchFromApi();
        }

        if ($this->curatedUrls() !== []) {
            return $this->fetchFromOembed();
        }

        return $this->emptyFeed();
    }

    // ------------------------------------------------------------------ X API v2

    private function fetchFromApi(): ?array
    {
        $user = $this->apiGet('/users/by/username/' . self::USERNAME, [
            'user.fields' => 'name,username,profile_image_url,verified,verified_type',
        ]);
        if (!isset($user['data']['id'])) {
            return null;
        }
        $u = $user['data'];

        $timeline = $this->apiGet('/users/' . $u['id'] . '/tweets', [
            // 5 is the API's minimum page size; replies/reposts are excluded so the four cards are
            // the school's own announcements, not one side of a conversation.
            'max_results' => '5',
            'exclude' => 'replies,retweets',
            'tweet.fields' => 'created_at,attachments,entities,note_tweet',
            'expansions' => 'attachments.media_keys',
            'media.fields' => 'type,url,preview_image_url,width,height,alt_text',
        ]);
        if ($timeline === null) {
            return null;
        }

        $mediaByKey = [];
        foreach ($timeline['includes']['media'] ?? [] as $m) {
            $mediaByKey[$m['media_key']] = $m;
        }

        $posts = [];
        foreach (array_slice($timeline['data'] ?? [], 0, self::LIMIT) as $t) {
            $text = $t['note_tweet']['text'] ?? $t['text'] ?? '';
            // Media attachments arrive as a trailing t.co link in the text - drop it, the media
            // itself is shown in the card.
            foreach ($t['entities']['urls'] ?? [] as $link) {
                if (isset($link['media_key'])) {
                    $text = str_replace($link['url'], '', $text);
                }
            }

            $media = [];
            foreach ($t['attachments']['media_keys'] ?? [] as $key) {
                $m = $mediaByKey[$key] ?? null;
                $image = $m['url'] ?? $m['preview_image_url'] ?? null;
                if ($m === null || !$this->isHttpsUrl($image)) {
                    continue;
                }
                $media[] = [
                    'type' => $m['type'] === 'photo' ? 'photo' : 'video',
                    'image_url' => $image,
                    'width' => isset($m['width']) ? (int) $m['width'] : null,
                    'height' => isset($m['height']) ? (int) $m['height'] : null,
                    'alt' => (string) ($m['alt_text'] ?? ''),
                ];
            }

            $posts[] = [
                'id' => (string) $t['id'],
                'url' => self::PROFILE_URL . '/status/' . $t['id'],
                'text' => trim(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')),
                'created_at' => $t['created_at'] ?? null,
                'date_precision' => 'time',
                'media' => $media,
                'has_media' => $media !== [] || !empty($t['attachments']['media_keys']),
            ];
        }

        $avatar = $u['profile_image_url'] ?? null;

        return [
            'source' => 'api',
            'account' => [
                'name' => (string) ($u['name'] ?? self::USERNAME),
                'username' => (string) ($u['username'] ?? self::USERNAME),
                'url' => self::PROFILE_URL,
                // _normal is 48px - _bigger (73px) stays sharp on high-DPI screens at 40-44px.
                'profile_image_url' => $this->isHttpsUrl($avatar) ? str_replace('_normal.', '_bigger.', $avatar) : null,
                'verified' => !empty($u['verified']),
            ],
            'posts' => $posts,
        ];
    }

    private function apiGet(string $path, array $query): ?array
    {
        $response = $this->httpGet(
            self::API_BASE . $path . '?' . http_build_query($query),
            ['Authorization: Bearer ' . $this->bearerToken()],
        );
        if ($response === null) {
            return null;
        }

        [$status, $body] = $response;
        if ($status !== 200) {
            // Log status and X's error title only - never the token or the full request.
            $title = json_decode($body, true)['title'] ?? null;
            Logger::warning('X API request failed', ['path' => $path, 'status' => $status, 'title' => $title]);
            return null;
        }

        $data = json_decode($body, true);
        return is_array($data) ? $data : null;
    }

    // ------------------------------------------------------------------ oEmbed (curated URLs)

    private function fetchFromOembed(): ?array
    {
        $posts = [];
        $anyFetched = false;

        foreach ($this->curatedUrls() as $url) {
            if (count($posts) >= self::LIMIT) {
                break;
            }

            $response = $this->httpGet(self::OEMBED_URL . '?' . http_build_query([
                'url' => $url,
                'omit_script' => '1',
                'dnt' => '1',
            ]));
            if ($response === null) {
                continue;
            }

            [$status, $body] = $response;
            if ($status === 404) {
                // Deleted, protected or mistyped post - skip it, the next URL takes its slot.
                $anyFetched = true;
                Logger::info('X post not available via oEmbed, skipping', ['url' => $url]);
                continue;
            }
            if ($status !== 200) {
                Logger::warning('X oEmbed request failed', ['url' => $url, 'status' => $status]);
                continue;
            }

            $anyFetched = true;
            $post = $this->parseOembed(json_decode($body, true), $url);
            if ($post !== null) {
                $posts[] = $post;
            }
        }

        if (!$anyFetched) {
            return null;
        }

        return [
            'source' => 'oembed',
            'account' => [
                'name' => "St. Mark's College Namagoma",
                'username' => self::USERNAME,
                'url' => self::PROFILE_URL,
                'profile_image_url' => null,
                // oEmbed says nothing about verification - unknown, so never claimed.
                'verified' => null,
            ],
            'posts' => $posts,
        ];
    }

    private function parseOembed(mixed $data, string $requestedUrl): ?array
    {
        if (!is_array($data) || !isset($data['html'], $data['author_url'])) {
            return null;
        }

        // Only ever show the school's own posts, even if someone pastes another account's URL.
        if (strcasecmp(rtrim((string) parse_url($data['author_url'], PHP_URL_PATH), '/'), '/' . self::USERNAME) !== 0) {
            Logger::warning('Ignoring X post from another account', ['url' => $requestedUrl]);
            return null;
        }

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div>' . $data['html'] . '</div>');
        libxml_clear_errors();

        $paragraph = $doc->getElementsByTagName('p')->item(0);
        $anchors = $doc->getElementsByTagName('a');
        $permalink = $anchors->length ? $anchors->item($anchors->length - 1) : null;
        if (!$paragraph instanceof DOMElement || !$permalink instanceof DOMElement) {
            return null;
        }

        $hasMedia = false;
        $text = $this->textOf($paragraph, $hasMedia);

        if (!preg_match('~/status/(\d+)~', $permalink->getAttribute('href'), $m)) {
            return null;
        }
        $timestamp = strtotime(trim($permalink->textContent));

        return [
            'id' => $m[1],
            'url' => self::PROFILE_URL . '/status/' . $m[1],
            'text' => trim($text),
            // oEmbed only gives the day ("March 21, 2006"), not the time.
            'created_at' => $timestamp ? date('Y-m-d', $timestamp) : null,
            'date_precision' => 'day',
            'media' => [],
            'has_media' => $hasMedia,
        ];
    }

    /** Flattens the oEmbed <p> to plain text: <br> becomes a newline, pic.x.com links become a has-media flag. */
    private function textOf(DOMNode $node, bool &$hasMedia): string
    {
        $out = '';
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $out .= $child->textContent;
            } elseif ($child->nodeName === 'br') {
                $out .= "\n";
            } elseif ($child->nodeName === 'a' && preg_match('~^pic\.(twitter|x)\.com/~i', trim($child->textContent))) {
                $hasMedia = true;
            } else {
                $out .= $this->textOf($child, $hasMedia);
            }
        }
        return $out;
    }

    // ------------------------------------------------------------------ helpers

    /** @return array{0:int,1:string}|null [status, body], or null on a network failure */
    private function httpGet(string $url, array $headers = []): ?array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => array_merge(['Accept: application/json'], $headers),
            CURLOPT_USERAGENT => 'StMarksWebsite/1.0 (+' . self::PROFILE_URL . ')',
        ]);
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            Logger::warning('X request network error', ['host' => parse_url($url, PHP_URL_HOST), 'error' => $error]);
            return null;
        }
        return [$status, (string) $body];
    }

    private function bearerToken(): string
    {
        return trim((string) Config::get('X_API_BEARER_TOKEN', ''));
    }

    /** Valid status URLs of the school's account from X_FEED_POST_URLS (comma/space separated, newest first). */
    private function curatedUrls(): array
    {
        $raw = preg_split('/[\s,]+/', (string) Config::get('X_FEED_POST_URLS', ''), -1, PREG_SPLIT_NO_EMPTY);
        return array_values(array_filter(
            $raw,
            fn (string $url) => (bool) preg_match('~^https://(www\.)?(x|twitter)\.com/' . self::USERNAME . '/status/\d+~i', $url),
        ));
    }

    /** Changes whenever the configuration does, so editing .env takes effect without waiting out the cache. */
    private function sourceKey(): string
    {
        return sha1(($this->bearerToken() !== '' ? 'api' : 'oembed') . '|' . implode(',', $this->curatedUrls()));
    }

    private function isHttpsUrl(mixed $url): bool
    {
        return is_string($url) && str_starts_with($url, 'https://');
    }

    private function emptyFeed(): array
    {
        return [
            'source' => 'none',
            'account' => [
                'name' => "St. Mark's College Namagoma",
                'username' => self::USERNAME,
                'url' => self::PROFILE_URL,
                'profile_image_url' => null,
                'verified' => null,
            ],
            'posts' => [],
        ];
    }

    private function cacheFile(): string
    {
        return Config::getStoragePath('cache') . DIRECTORY_SEPARATOR . 'x-feed.json';
    }

    private function readCache(): ?array
    {
        $raw = @file_get_contents($this->cacheFile());
        $data = $raw ? json_decode($raw, true) : null;
        return is_array($data) && isset($data['feed'], $data['expires_at'], $data['source_key']) ? $data : null;
    }

    private function writeCache(array $feed, int $ttl): void
    {
        $file = $this->cacheFile();
        if (!is_dir(dirname($file))) {
            @mkdir(dirname($file), 0755, true);
        }
        // Write-then-rename so a concurrent reader never sees a half-written file.
        $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
        $json = json_encode([
            'source_key' => $this->sourceKey(),
            'expires_at' => time() + $ttl,
            'feed' => $feed,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (@file_put_contents($tmp, $json) !== false) {
            @rename($tmp, $file);
        }
    }
}
