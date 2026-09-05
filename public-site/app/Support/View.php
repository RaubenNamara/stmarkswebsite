<?php

declare(strict_types=1);

namespace StMarks\PublicSite\Support;

/**
 * Minimal PHP-native templating - no Blade clone, no Twig. A page template is plain PHP using
 * <?= ?> directly; render() buffers it, then wraps the result in a layout template that receives
 * $content plus $meta (per-page <title>/description for SEO).
 */
class View
{
    private static string $templatesDir;

    public static function setTemplatesDir(string $dir): void
    {
        self::$templatesDir = rtrim($dir, '/\\');
    }

    /**
     * $page is relative to templates/pages/ (the prefix is implicit - callers write
     * View::render('news/index', ...) or View::render('errors/404', ...), never
     * 'pages/news/index'). Layouts and partials are a separate concern with their own full path,
     * rendered via renderPartial() instead.
     */
    public static function render(string $page, array $data = [], ?string $layout = 'layouts/main', array $meta = []): void
    {
        echo self::capture($page, $data, $layout, $meta);
    }

    public static function capture(string $page, array $data = [], ?string $layout = 'layouts/main', array $meta = []): string
    {
        $content = self::renderPartial('pages/' . $page, $data);

        if ($layout === null) {
            return $content;
        }

        return self::renderPartial($layout, ['content' => $content, 'meta' => $meta]);
    }

    public static function renderPartial(string $template, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require self::$templatesDir . '/' . $template . '.php';
        return ob_get_clean();
    }
}
