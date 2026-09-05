<?php

namespace App\Http\Controllers;

use App\Models\InspirationNight;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class InspirationNightController extends Controller
{
    public function index()
    {
        $nights = InspirationNight::latest()->get()->map(function ($item) {

            return [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'speaker' => $item->speaker,
                'date' => $item->date,
                'video' => $item->video,
                'image' => $item->image
                    ? Storage::url($item->image)
                    : null
            ];
        });

        return Inertia::render('Empowerment/InspirationNight', [
            'nights' => $nights
        ]);
    }
}