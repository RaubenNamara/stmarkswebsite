<?php

namespace App\Http\Controllers;

use App\Models\GalleryEvent;
use Inertia\Inertia;

class GalleryController extends Controller
{
    public function index()
    {
        $events = GalleryEvent::with('images')
            ->latest()
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'images' => $event->images->map(function ($img) {
                        return [
                            'id' => $img->id,
                            'image_url' => asset('storage/' . $img->image_path),
                        ];
                    }),
                ];
            });

        return Inertia::render('Explore/Gallery', [
            'events' => $events
        ]);
    }
}