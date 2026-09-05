<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fullName' => 'required|string|max:255',
            'contact'  => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'address'  => 'required|string',
            'position' => 'required|string|max:255',
            'file'     => 'required|file|mimes:pdf|max:5120', // 5MB
        ]);

        // Store file
        $filePath = $request->file('file')->store('applications', 'public');

        // Save to database
        JobApplication::create([
            'full_name' => $validated['fullName'],
            'contact'   => $validated['contact'],
            'email'     => $validated['email'],
            'address'   => $validated['address'],
            'position'  => $validated['position'],
            'file_path' => $filePath,
        ]);

        return back()->with('success', 'Application submitted successfully.');
    }
}