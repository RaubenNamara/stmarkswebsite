<?php

namespace App\Http\Controllers;

use App\Models\SmosaAlumni;
use Inertia\Inertia;

class SmosaAlumniController extends Controller
{
    public function index()
    {
        $alumni = SmosaAlumni::latest()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'profession' => $item->profession,
                    'message' => $item->message,
                    'video' => $item->video,
                    // return an absolute URL if photo exists, otherwise null
                    'photo' => $item->photo ? asset('storage/' . $item->photo) : null,
                ];
            });

        return Inertia::render('Empowerment/SmosaAlumni', [
            'alumni' => $alumni,
            'pageTitle' => 'SMOSA Alumni'
        ]);
    }
}