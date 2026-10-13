<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use Throwable;

/**
 * Extracts the text of an uploaded PDF (pure PHP via smalot/pdfparser - no shell tools, so it works
 * on shared hosting) and caches it on disk, keyed by file path + modification time + size, so a PDF
 * is parsed once and re-parsed automatically only when it is replaced.
 *
 * Only PDFs with a real text layer can be read. A scanned (image-only) PDF yields no text and
 * extract() returns null - the chatbot then simply links to the document instead of quoting it.
 */
class PdfTextExtractor
{
    private const MAX_BYTES = 8 * 1024 * 1024;
    private const MAX_CHARS = 60000;

    private string $cacheDir;

    public function __construct(?string $cacheDir = null)
    {
        $this->cacheDir = $cacheDir ?? dirname(__DIR__, 3) . '/storage/cache/pdf-text';
    }

    public function extract(string $absolutePath): ?string
    {
        if (!is_file($absolutePath) || !class_exists(\Smalot\PdfParser\Parser::class)) {
            return null;
        }
        $size = filesize($absolutePath);
        if ($size === false || $size === 0 || $size > self::MAX_BYTES) {
            return null;
        }

        $key = sha1($absolutePath . '|' . filemtime($absolutePath) . '|' . $size);
        $cacheFile = $this->cacheDir . '/' . $key . '.txt';
        if (is_file($cacheFile)) {
            $cached = file_get_contents($cacheFile);
            return $cached === '' ? null : ($cached === false ? null : $cached);
        }

        $text = $this->parse($absolutePath);

        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0775, true);
        }
        // An empty file records "parsed, but no text layer" so a scanned PDF is not re-parsed on every request.
        @file_put_contents($cacheFile, $text ?? '');

        return $text;
    }

    private function parse(string $absolutePath): ?string
    {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $raw = $parser->parseFile($absolutePath)->getText();
        } catch (Throwable) {
            return null;
        }

        $text = $this->clean($raw);
        // Fewer than a handful of letters means there is no usable text layer (scanned image).
        if (preg_match_all('/\p{L}/u', $text) < 20) {
            return null;
        }
        return mb_substr($text, 0, self::MAX_CHARS);
    }

    private function clean(string $raw): string
    {
        if (!mb_check_encoding($raw, 'UTF-8')) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'UTF-8');
        }
        $raw = str_replace(["\r\n", "\r", "\u{00A0}"], ["\n", "\n", ' '], $raw);
        $raw = preg_replace('/[^\P{C}\n\t]+/u', '', $raw) ?? $raw; // drop control/replacement characters, keep newlines and tabs
        $raw = preg_replace('/[ ]{2,}/', ' ', $raw) ?? $raw;
        $raw = preg_replace('/[ ]*\n[ \t]*/', "\n", $raw) ?? $raw;
        $raw = preg_replace('/\n{3,}/', "\n\n", $raw) ?? $raw;
        return trim($raw);
    }
}
