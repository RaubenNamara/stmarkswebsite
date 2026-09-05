<?php

namespace App\Http\Controllers;

use App\Models\StudentLeadership;
use Inertia\Inertia;

class StudentLeadershipController extends Controller
{
    public function index()
    {
        $leaders = StudentLeadership::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Explore/StudentLeadership', [
            'leaders' => $leaders
        ]);
    }
}