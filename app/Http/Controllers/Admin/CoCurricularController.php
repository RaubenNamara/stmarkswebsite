<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoCurricular;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class CoCurricularController extends Controller
{
    // ===============================
    // Display All Records
    // ===============================
    public function index()
    {
        $items = CoCurricular::latest()->get();

        return Inertia::render('Admin/CoCurricular/Index', [
            'items' => $items
        ]);
    }

    // ===============================
    // Show Create Page
    // ===============================
    public function create()
    {
        return Inertia::render('Admin/CoCurricular/Create');
    }

    // ===============================
    // Store New Record
    // ===============================
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'video' => 'nullable|string'
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')
                ->store('co_curricular', 'public');
        }

        CoCurricular::create([
            'title' => $request->title,
            'content' => $request->content,
            'image' => $imagePath,
            'video' => $request->video,
        ]);

        return redirect()->route('admin.co-curricular.index')
            ->with('success', 'Content created successfully');
    }

    // ===============================
    // Show Edit Page
    // ===============================
    public function edit(CoCurricular $coCurricular)
    {
        return Inertia::render('Admin/CoCurricular/Edit', [
            'item' => $coCurricular
        ]);
    }

    // ===============================
    // Update Record
    // ===============================
    public function update(Request $request, CoCurricular $coCurricular)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'video' => 'nullable|string'
        ]);

        $imagePath = $coCurricular->image;

        if ($request->hasFile('image')) {

            // Delete old image if exists
            if ($coCurricular->image && Storage::disk('public')->exists($coCurricular->image)) {
                Storage::disk('public')->delete($coCurricular->image);
            }

            $imagePath = $request->file('image')
                ->store('co_curricular', 'public');
        }

        $coCurricular->update([
            'title' => $request->title,
            'content' => $request->content,
            'image' => $imagePath,
            'video' => $request->video,
        ]);

        return redirect()->route('admin.co-curricular.index')
            ->with('success', 'Content updated successfully');
    }

    // ===============================
    // Delete Record
    // ===============================
    public function destroy(CoCurricular $coCurricular)
    {
        if ($coCurricular->image && Storage::disk('public')->exists($coCurricular->image)) {
            Storage::disk('public')->delete($coCurricular->image);
        }

        $coCurricular->delete();

        return redirect()->back()
            ->with('success', 'Content deleted successfully');
    }
}