<?php

namespace App\Http\Controllers;

use App\Models\Performance;

class PerformanceController extends Controller
{
    public function index()
    {
        $performances = Performance::latest()->get();
        return inertia('Performance/Index', [
            'performances' => $performances
        ]);
    }
}