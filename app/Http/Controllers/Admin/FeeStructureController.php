<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FeeStructureController extends Controller
{
    public function index()
    {
        $fees = FeeStructure::latest()->get();

        return inertia('Admin/FeeStructures/Index', [
            'fees' => $fees,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'pdf'   => 'required|mimes:pdf|max:10240', // 10MB
        ]);

        $path = $request->file('pdf')->store('fee_structures', 'public');

        FeeStructure::create([
            'title'     => $request->title,
            'file_path' => $path,
        ]);

        return back()->with('success', 'Fee structure uploaded.');
    }

    public function destroy(FeeStructure $fee_structure)
    {
        // delete file from storage (ignore missing)
        Storage::disk('public')->delete($fee_structure->file_path);

        $fee_structure->delete();

        return back()->with('success', 'Deleted.');
    }
}