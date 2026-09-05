<?php

namespace App\Http\Controllers;

use App\Models\Mentorship;
use Inertia\Inertia;

class MentorshipPageController extends Controller
{
    public function index()
    {
        $mentorships = Mentorship::latest()->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'caption' => $item->caption,
                'description' => $item->description,

                // ✅ THIS IS THE FIX
                'image_url' => $item->image
                    ? asset('storage/mentorship/' . $item->image)
                    : null,

                'video_url' => $item->video
                    ? asset('storage/mentorship/' . $item->video)
                    : null,

                'video_link' => $item->video_link,
            ];
        });

        return Inertia::render('Mentorship', [
            'mentorships' => $mentorships
        ]);
    }
}