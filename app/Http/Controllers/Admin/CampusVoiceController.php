<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CampusVoiceRequest;
use App\Models\CampusVoice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CampusVoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = CampusVoice::query();

        // Search by student name
        if ($request->has('search')) {
            $query->searchByStudentName($request->search);
        }

        // Filter by category
        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        $campusVoices = $query->latest()->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'student_name' => $item->student_name,
                'title' => $item->title,
                'slug' => $item->slug,
                'summary' => $item->summary,
                'content' => $item->content,
                'featured_image' => $item->featured_image,
                'image_url' => $item->featured_image ? asset('storage/' . $item->featured_image) : null,
                'category' => $item->category,
                'author' => $item->author,
                'featured' => $item->featured,
                'status' => $item->status,
                'views' => $item->views,
                'published_at' => $item->published_at?->toDateTimeString(),
                'created_at' => $item->created_at?->toDateTimeString(),
            ];
        });

        // Get unique categories for filter
        $categories = CampusVoice::whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        return Inertia::render('Admin/CampusVoices/Index', [
            'campusVoices' => $campusVoices,
            'categories' => $categories,
            'filters' => [
                'search' => $request->search,
                'category' => $request->category,
                'status' => $request->status,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/CampusVoices/Create');
    }

    public function store(CampusVoiceRequest $request)
    {
        $validated = $request->validated();

        // Generate slug from title
        $slug = Str::slug($validated['title']);

        // Ensure unique slug
        if (CampusVoice::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::random(5);
        }

        // Auto-generate summary from content
        $summary = null;
        if (!empty($validated['content'])) {
            $text = strip_tags($validated['content']);
            $summary = substr($text, 0, 200) . '...';
        }

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('featured_image')) {
            $destination = dirname(base_path()) . '/storage/campus-voices';

            if (!is_dir($destination)) {
                mkdir($destination, 0777, true);
            }

            $file = $request->file('featured_image');
            $filename = time() . '_img_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $file->getClientOriginalName());
            $file->move($destination, $filename);

            $imagePath = 'campus-voices/' . $filename;
        }

        // Set published_at if status is published
        $publishedAt = null;
        if ($validated['status'] === 'published') {
            $publishedAt = now();
        }

        CampusVoice::create([
            'student_name' => $validated['student_name'],
            'title' => $validated['title'],
            'slug' => $slug,
            'summary' => $summary,
            'author_bio' => $validated['author_bio'] ?? null,
            'content' => $validated['content'],
            'featured_image' => $imagePath,
            'category' => $validated['category'] ?? null,
            'author' => null,
            'featured' => $request->boolean('featured'),
            'status' => $validated['status'],
            'published_at' => $publishedAt,
        ]);

        return redirect()
            ->route('admin.campus-voices.index')
            ->with('success', 'Campus Voice article created successfully.');
    }

    public function edit(CampusVoice $campusVoice)
    {
        return Inertia::render('Admin/CampusVoices/Edit', [
            'campusVoice' => [
                'id' => $campusVoice->id,
                'student_name' => $campusVoice->student_name,
                'title' => $campusVoice->title,
                'author_bio' => $campusVoice->author_bio,
                'content' => $campusVoice->content,
                'featured_image' => $campusVoice->featured_image,
                'image_url' => $campusVoice->featured_image ? asset('../storage/' . $campusVoice->featured_image) : null,
                'category' => $campusVoice->category,
                'featured' => $campusVoice->featured,
                'status' => $campusVoice->status,
                'views' => $campusVoice->views,
                'published_at' => $campusVoice->published_at?->toDateTimeString(),
            ],
        ]);
    }

    public function update(CampusVoiceRequest $request, CampusVoice $campusVoice)
    {
        $validated = $request->validated();

        // Generate slug from title if title changed
        $slug = $campusVoice->slug;
        if ($campusVoice->title !== $validated['title']) {
            $slug = Str::slug($validated['title']);
            // Ensure unique slug (excluding current record)
            if (CampusVoice::where('slug', $slug)->where('id', '!=', $campusVoice->id)->exists()) {
                $slug .= '-' . Str::random(5);
            }
        }

        // Auto-generate summary from content
        $summary = $campusVoice->summary;
        if (!empty($validated['content'])) {
            $text = strip_tags($validated['content']);
            $summary = substr($text, 0, 200) . '...';
        }

        // Handle image upload
        $imagePath = $campusVoice->featured_image;
        if ($request->hasFile('featured_image')) {
            // Delete old image
            if ($campusVoice->featured_image) {
                $fullPath = dirname(base_path()) . '/storage/' . $campusVoice->featured_image;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }

            $destination = dirname(base_path()) . '/storage/campus-voices';
            if (!is_dir($destination)) {
                mkdir($destination, 0777, true);
            }

            $file = $request->file('featured_image');
            $filename = time() . '_img_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $file->getClientOriginalName());
            $file->move($destination, $filename);

            $imagePath = 'campus-voices/' . $filename;
        }

        // Set published_at if status changed to published
        $publishedAt = $campusVoice->published_at;
        if ($validated['status'] === 'published' && !$campusVoice->published_at) {
            $publishedAt = now();
        }

        $campusVoice->update([
            'student_name' => $validated['student_name'],
            'title' => $validated['title'],
            'slug' => $slug,
            'summary' => $summary,
            'author_bio' => $validated['author_bio'] ?? null,
            'content' => $validated['content'],
            'featured_image' => $imagePath,
            'category' => $validated['category'] ?? null,
            'author' => null,
            'featured' => $request->boolean('featured'),
            'status' => $validated['status'],
            'published_at' => $publishedAt,
        ]);

        return redirect()
            ->route('admin.campus-voices.index')
            ->with('success', 'Campus Voice article updated successfully.');
    }

    public function destroy(CampusVoice $campusVoice)
    {
        // Delete image
        if ($campusVoice->featured_image) {
            $fullPath = dirname(base_path()) . '/storage/' . $campusVoice->featured_image;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        $campusVoice->delete();

        return back()->with('success', 'Campus Voice article deleted successfully.');
    }

    public function toggleFeatured(CampusVoice $campusVoice)
    {
        $campusVoice->update([
            'featured' => !$campusVoice->featured,
        ]);

        return back()->with('success', 'Featured status updated successfully.');
    }
}
