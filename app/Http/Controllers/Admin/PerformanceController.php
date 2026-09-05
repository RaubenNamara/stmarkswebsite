<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Performance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class PerformanceController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Performances/Index', [
            'performances' => Performance::latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'pdf' => 'required|mimes:pdf|max:10000'
        ]);

        $path = $request->file('pdf')->store('performances', 'public');

        Performance::create([
            'title' => $request->title,
            'pdf' => $path
        ]);

        return redirect()->back()->with('success', 'PDF uploaded successfully.');
    }

    public function destroy(Performance $performance)
    {
        Storage::disk('public')->delete($performance->pdf);
        $performance->delete();

        return back()->with('success', 'Deleted successfully.');
    }
}