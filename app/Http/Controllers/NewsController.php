<?php

namespace App\Http\Controllers;

use App\Models\News;
use Inertia\Inertia;

class NewsController extends Controller
{
    /**
     * Show a single published news item by slug (public page)
     */
    public function show($slug)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Get Current News
        |--------------------------------------------------------------------------
        */
        $news = News::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $news->image_url = $news->image_path
            ? asset('storage/' . $news->image_path)
            : null;

        /*
        |--------------------------------------------------------------------------
        | 2. Get 24 Posts for this page only
        |--------------------------------------------------------------------------
        */
        $posts = News::where('is_published', true)
            ->where('id', '!=', $news->id)
            ->latest()
            ->take(24)
            ->get()
            ->map(function ($item) {
                $content = $item->excerpt ?: $item->content;

                return [
                    'id'           => $item->id,
                    'title'        => $item->title,
                    'slug'         => $item->slug,
                    'content'      => $content,
                    'excerpt'      => $item->excerpt,
                    'image_url'    => $item->image_path
                        ? asset('storage/' . $item->image_path)
                        : null,
                    'created_at'   => optional($item->created_at)->toDateTimeString(),
                    'date'         => optional($item->created_at)->toDateTimeString(),
                    'published_at' => optional($item->published_at)->toDateTimeString(),
                    'url'          => url('/news/' . $item->slug),
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | 3. Render Inertia Page
        |--------------------------------------------------------------------------
        */
        return Inertia::render('News/Show', [
            'news'  => $news,
            'posts' => $posts,
        ]);
    }
}