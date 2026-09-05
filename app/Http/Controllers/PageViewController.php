<?php

namespace App\Http\Controllers;

use App\Models\PageView;
use Illuminate\Http\Request;

class PageViewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'page_url' => 'required|string',
            'page_type' => 'nullable|string',
            'page_id' => 'nullable|integer',
        ]);

        PageView::create([
            'page_url' => $validated['page_url'],
            'page_type' => $validated['page_type'] ?? null,
            'page_id' => $validated['page_id'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'view_date' => now()->toDateString(),
        ]);

        return response()->json(['success' => true]);
    }

    public function getStats($pageType = null, $pageId = null)
    {
        $query = PageView::query();

        if ($pageType && $pageId) {
            $query->where('page_type', $pageType)
                  ->where('page_id', $pageId);
        }

        $dailyViews = $query->selectRaw('view_date, COUNT(*) as views')
            ->groupBy('view_date')
            ->orderBy('view_date', 'desc')
            ->limit(30)
            ->get();

        $totalViews = $query->count();

        return response()->json([
            'daily_views' => $dailyViews,
            'total_views' => $totalViews,
        ]);
    }

    public function getGeneralStats()
    {
        $dailyViews = PageView::selectRaw('view_date, COUNT(*) as views')
            ->groupBy('view_date')
            ->orderBy('view_date', 'desc')
            ->limit(30)
            ->get();

        $totalViews = PageView::count();

        $byPageType = PageView::selectRaw('page_type, COUNT(*) as views')
            ->groupBy('page_type')
            ->orderBy('views', 'desc')
            ->get();

        return response()->json([
            'daily_views' => $dailyViews,
            'total_views' => $totalViews,
            'by_page_type' => $byPageType,
        ]);
    }
}
