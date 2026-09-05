<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chaplaincy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ChaplaincyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Chaplaincy::latest()->get();

        // Return Inertia page with items
        return Inertia::render('Admin/Chaplaincy/Index', [
            'items' => $items
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'image' => 'nullable|image|max:5120', // 5MB
            'video' => 'nullable|mimetypes:video/mp4,video/quicktime,video/avi,video/mpeg|max:51200', // up to 50MB
            'video_link' => 'nullable|url',
        ]);

        $data = [
            'title' => $validated['title'],
            'content' => $validated['content'] ?? null,
            'image' => null,
            'video' => null,
            'video_link' => $validated['video_link'] ?? null,
        ];

        // store image if provided
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('chaplaincy/images', 'public');
            $data['image'] = $path;
        }

        // store video if provided
        if ($request->hasFile('video')) {
            $path = $request->file('video')->store('chaplaincy/videos', 'public');
            $data['video'] = $path;
        }

        Chaplaincy::create($data);

        return redirect()->back()->with('success', 'Chaplaincy post created.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = Chaplaincy::findOrFail($id);

        // delete files if exist
        if ($item->image && Storage::disk('public')->exists($item->image)) {
            Storage::disk('public')->delete($item->image);
        }

        if ($item->video && Storage::disk('public')->exists($item->video)) {
            Storage::disk('public')->delete($item->video);
        }

        $item->delete();

        return redirect()->back()->with('success', 'Chaplaincy post deleted.');
    }
}