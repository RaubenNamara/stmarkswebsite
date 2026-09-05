<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::latest()->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug,
                'excerpt' => $item->excerpt,
                'content' => $item->content,
                'image_path' => $item->image_path,
                'image_url' => $item->image_path ? asset('storage/' . $item->image_path) : null,
                'is_published' => $item->is_published,
                'created_at' => $item->created_at?->toDateTimeString(),
            ];
        });

        return Inertia::render('Admin/News/Index', [
            'news' => $news
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:news,slug',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'image' => 'nullable|image|max:5120',
            'is_published' => 'boolean'
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['title']);

        if (News::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::random(5);
        }

        $imagePath = null;

        if ($request->hasFile('image')) {
            $destination = base_path('../storage/news');

            if (!is_dir($destination)) {
                mkdir($destination, 0777, true);
            }

            $file = $request->file('image');
            $filename = time() . '_img_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $file->getClientOriginalName());
            $file->move($destination, $filename);

            $imagePath = 'news/' . $filename;
        }

        News::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'image_path' => $imagePath,
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()
            ->route('admin.news.index')
            ->with('success', 'News created successfully.');
    }

    public function destroy(News $news)
    {
        if ($news->image_path) {
            $fullPath = base_path('../storage/' . $news->image_path);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        $news->delete();

        return back()->with('success', 'News deleted successfully.');
    }
}