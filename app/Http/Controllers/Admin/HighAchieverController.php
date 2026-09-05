<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HighAchiever;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HighAchieverController extends Controller
{
    public function index()
    {
        $achievers = HighAchiever::latest()->get()->map(function ($a) {
            return [
                'id' => $a->id,
                'name' => $a->name,
                'year' => $a->year,
                'exam' => $a->exam,
                'division' => $a->division,
                'description' => $a->description,
                'photo' => $a->photo,
                'photo_url' => $a->photo ? asset('storage/high_achievers/' . $a->photo) : null,
            ];
        });

        return Inertia::render('Admin/HighAchievers/Index', [
            'achievers' => $achievers,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|string|max:50',
            'exam' => 'required|string|max:50',
            'division' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'required|image|max:2048',
        ]);

        $file = $request->file('photo');

        $destination = base_path('../storage/high_achievers');

        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        $filename = time() . '_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $file->getClientOriginalName());

        $file->move($destination, $filename);

        HighAchiever::create([
            'name' => trim($request->name),
            'year' => $request->year,
            'exam' => $request->exam,
            'division' => $request->division,
            'description' => $request->description,
            'photo' => $filename,
        ]);

        return back()->with('success', 'Saved successfully');
    }

    public function update(Request $request, HighAchiever $highAchiever)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|string|max:50',
            'exam' => 'required|string|max:50',
            'division' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        $data = [
            'name' => trim($request->name),
            'year' => $request->year,
            'exam' => $request->exam,
            'division' => $request->division,
            'description' => $request->description,
        ];

        if ($request->hasFile('photo')) {
            $oldPath = base_path('../storage/high_achievers/' . $highAchiever->photo);

            if ($highAchiever->photo && file_exists($oldPath)) {
                unlink($oldPath);
            }

            $file = $request->file('photo');
            $destination = base_path('../storage/high_achievers');

            if (!is_dir($destination)) {
                mkdir($destination, 0777, true);
            }

            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $file->getClientOriginalName());
            $file->move($destination, $filename);

            $data['photo'] = $filename;
        }

        $highAchiever->update($data);

        return back()->with('success', 'Updated successfully');
    }

    public function destroy(HighAchiever $highAchiever)
    {
        $filePath = base_path('../storage/high_achievers/' . $highAchiever->photo);

        if ($highAchiever->photo && file_exists($filePath)) {
            unlink($filePath);
        }

        $highAchiever->delete();

        return back()->with('success', 'Deleted successfully');
    }
}