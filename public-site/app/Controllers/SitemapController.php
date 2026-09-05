<?php

declare(strict_types=1);

namespace StMarks\PublicSite\Controllers;

use StMarks\Shared\Services\CampusVoiceService;
use StMarks\Shared\Services\ClubService;
use StMarks\Shared\Services\NewsService;
use StMarks\Shared\Services\PostService;

/** Generated on the fly rather than a static file, so new News/CampusVoices/Clubs/Posts appear automatically. */
class SitemapController
{
    private const STATIC_PATHS = [
        '/', '/about', '/academics', '/academics/curriculum', '/academics/uneb-results',
        '/academics/circulars', '/academics/school-calendar', '/academics/co-curricular',
        '/academics/high-achievers', '/admissions', '/staff', '/board-members', '/core-values',
        '/school-anthem', '/college-name', '/headteacher', '/director1', '/director2',
        '/empowerment-programmes', '/empowerment/chaplaincy', '/empowerment/mentorship',
        '/empowerment/girl-boy-talk', '/empowerment/inspiration-night', '/empowerment/smosa-alumni',
        '/empowerment/christmas-cantata', '/smosa-feedback', '/apply', '/contact', '/news',
        '/fee-structures', '/performance', '/explore/career', '/explore/personal-needs',
        '/explore/uniform', '/explore/gallery', '/explore/student-leadership', '/clubs',
        '/campus-voices', '/posts',
    ];

    public function index(): void
    {
        $urls = array_map(fn ($path) => ['loc' => $path], self::STATIC_PATHS);

        foreach ((new NewsService())->published() as $item) {
            $urls[] = ['loc' => '/news/' . $item['slug'], 'lastmod' => $item['updated_at']];
        }
        foreach ((new CampusVoiceService())->published() as $item) {
            $urls[] = ['loc' => '/campus-voices/' . $item['slug']];
        }
        foreach ((new ClubService())->all() as $item) {
            $urls[] = ['loc' => '/clubs/' . $item['slug']];
        }
        foreach ((new PostService())->published() as $item) {
            $urls[] = ['loc' => '/posts/' . $item['slug']];
        }

        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            echo '  <url><loc>' . htmlspecialchars($url['loc']) . '</loc>';
            if (!empty($url['lastmod'])) {
                echo '<lastmod>' . substr($url['lastmod'], 0, 10) . '</lastmod>';
            }
            echo "</url>\n";
        }
        echo '</urlset>';
        exit;
    }
}
