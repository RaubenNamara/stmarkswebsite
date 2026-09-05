<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Club;

class ClubController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | PUBLIC CLUB LIST
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $clubs = Club::with('images')
            ->latest()
            ->get();

        return Inertia::render('Clubs/Index', [
            'clubs' => $clubs
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PUBLIC SINGLE CLUB
    |--------------------------------------------------------------------------
    */

    public function show(Club $club)
    {
        // Load images for current club
        $club->load('images');

        // Get related clubs (exclude current)
        $relatedClubs = Club::with('images')
            ->where('id', '!=', $club->id)
            ->latest()
            ->take(4)
            ->get();

        return Inertia::render('Clubs/Show', [
            'club' => $club,
            'relatedClubs' => $relatedClubs,
        ]);
    }
}