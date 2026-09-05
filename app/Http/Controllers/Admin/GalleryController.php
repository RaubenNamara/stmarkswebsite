<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryEvent;
use App\Models\GalleryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    /**
     * Display all gallery events with images
     */
    public function index()
    {
        $events = GalleryEvent::with('images')->latest()->get();

        return inertia('Admin/Gallery/Index', [
            'events' => $events
        ]);
    }

    /**
     * Store new event with multiple images
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'images' => 'required',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120'
        ]);

        // ✅ NO description here
        $event = GalleryEvent::create([
            'title' => $request->title,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('gallery', 'public');

                GalleryImage::create([
                    'gallery_event_id' => $event->id,
                    'image_path' => $path,
                ]);
            }
        }

        return back()->with('success', 'Gallery event created successfully.');
    }

    /**
     * Add more images to existing event
     */
    public function addImages(Request $request, GalleryEvent $event)
    {
        $request->validate([
            'images' => 'required',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120'
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('gallery', 'public');

                GalleryImage::create([
                    'gallery_event_id' => $event->id,
                    'image_path' => $path,
                ]);
            }
        }

        return back()->with('success', 'Images added successfully.');
    }

    /**
     * Delete single image from an event
     */
    public function destroyImage(GalleryImage $image)
    {
        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        return back()->with('success', 'Image deleted.');
    }

    /**
     * Delete event and all its images
     */
    public function destroy(GalleryEvent $gallery)
    {
        foreach ($gallery->images as $img) {
            if (Storage::disk('public')->exists($img->image_path)) {
                Storage::disk('public')->delete($img->image_path);
            }
            $img->delete();
        }

        $gallery->delete();

        return back()->with('success', 'Gallery event deleted.');
    }
}