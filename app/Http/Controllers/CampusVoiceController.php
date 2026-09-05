<?php

namespace App\Http\Controllers;

use App\Models\CampusVoice;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CampusVoiceController extends Controller
{
    public function index(Request $request)
    {
        // Get featured article for hero section first
        $featuredArticle = CampusVoice::published()
            ->featured()
            ->byStudentName()
            ->first();

        if ($featuredArticle) {
            $featuredArticle = [
                'id' => $featuredArticle->id,
                'student_name' => $featuredArticle->student_name,
                'title' => $featuredArticle->title,
                'slug' => $featuredArticle->slug,
                'summary' => $featuredArticle->summary,
                'featured_image' => $featuredArticle->featured_image,
                'image_url' => $featuredArticle->featured_image ? '../storage/' . $featuredArticle->featured_image : null,
                'category' => $featuredArticle->category,
                'author' => $featuredArticle->author,
                'views' => $featuredArticle->views,
                'published_at' => $featuredArticle->published_at?->format('M d, Y'),
                'reading_time' => $featuredArticle->reading_time,
            ];
        }

        // Get all published articles for the main grid
        $query = CampusVoice::published();

        // Always order alphabetically by student name (A-Z)
        $query->byStudentName();

        $articles = $query->get()->map(function ($article) {
            return [
                'id' => $article->id,
                'student_name' => $article->student_name,
                'title' => $article->title,
                'slug' => $article->slug,
                'summary' => $article->summary,
                'featured_image' => $article->featured_image,
                'image_url' => $article->featured_image ? '../storage/' . $article->featured_image : null,
                'category' => $article->category,
                'author' => $article->author,
                'featured' => $article->featured,
                'views' => $article->views,
                'published_at' => $article->published_at?->format('M d, Y'),
                'reading_time' => $article->reading_time,
            ];
        });

        // Get unique categories for sidebar
        $categories = CampusVoice::published()
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        // Get recently published (latest 5)
        $recentArticles = CampusVoice::published()
            ->latest('published_at')
            ->take(5)
            ->get()
            ->map(function ($article) {
                return [
                    'id' => $article->id,
                    'student_name' => $article->student_name,
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'published_at' => $article->published_at?->format('M d, Y'),
                ];
            });

        // Get most viewed (latest 5)
        $mostViewed = CampusVoice::published()
            ->orderBy('views', 'desc')
            ->take(5)
            ->get()
            ->map(function ($article) {
                return [
                    'id' => $article->id,
                    'student_name' => $article->student_name,
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'views' => $article->views,
                ];
            });

        return Inertia::render('CampusVoices/Index', [
            'articles' => $articles,
            'featuredArticle' => $featuredArticle,
            'categories' => $categories,
            'recentArticles' => $recentArticles,
            'mostViewed' => $mostViewed,
            'search' => $request->search,
        ]);
    }

    public function show($slug)
    {
        $article = CampusVoice::where('slug', $slug)
            ->published()
            ->firstOrFail();

        // Increment view count
        $article->incrementViews();

        // Get previous and next articles (alphabetically by student name)
        $previousArticle = CampusVoice::published()
            ->byStudentName()
            ->where('student_name', '<', $article->student_name)
            ->orderBy('student_name', 'desc')
            ->first();

        $nextArticle = CampusVoice::published()
            ->byStudentName()
            ->where('student_name', '>', $article->student_name)
            ->orderBy('student_name', 'asc')
            ->first();

        // Get related articles (same category, excluding current)
        $relatedArticles = collect();
        if ($article->category) {
            $relatedArticles = CampusVoice::published()
                ->where('category', $article->category)
                ->where('id', '!=', $article->id)
                ->byStudentName()
                ->take(4)
                ->get()
                ->map(function ($a) {
                    return [
                        'id' => $a->id,
                        'student_name' => $a->student_name,
                        'title' => $a->title,
                        'slug' => $a->slug,
                        'featured_image' => $a->featured_image,
                        'image_url' => $a->featured_image ? '../storage/' . $a->featured_image : null,
                        'summary' => $a->summary,
                        'published_at' => $a->published_at?->format('M d, Y'),
                    ];
                });
        }

        $articleData = [
            'id' => $article->id,
            'student_name' => $article->student_name,
            'title' => $article->title,
            'slug' => $article->slug,
            'summary' => $article->summary,
            'author_bio' => $article->author_bio,
            'content' => $article->content,
            'featured_image' => $article->featured_image,
            'image_url' => $article->featured_image ? '../storage/' . $article->featured_image : null,
            'category' => $article->category,
            'author' => $article->author,
            'featured' => $article->featured,
            'views' => $article->views,
            'published_at' => $article->published_at?->format('M d, Y'),
            'reading_time' => $article->reading_time,
        ];

        return Inertia::render('CampusVoices/Show', [
            'article' => $articleData,
            'previousArticle' => $previousArticle ? [
                'slug' => $previousArticle->slug,
                'title' => $previousArticle->title,
                'student_name' => $previousArticle->student_name,
            ] : null,
            'nextArticle' => $nextArticle ? [
                'slug' => $nextArticle->slug,
                'title' => $nextArticle->title,
                'student_name' => $nextArticle->student_name,
            ] : null,
            'relatedArticles' => $relatedArticles,
        ]);
    }
}
