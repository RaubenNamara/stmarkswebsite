<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Inertia\Inertia;

class AdminJobApplicationController extends Controller
{
    /**
     * Display all job applications
     */
    public function index()
    {
        $applications = JobApplication::latest()->get()->map(function ($app) {
            return [
                'id'         => $app->id,
                'full_name'  => $app->full_name,
                'contact'    => $app->contact,
                'email'      => $app->email,
                'position'   => $app->position,
                'created_at' => $app->created_at->format('M d, Y'),
                'file_url'   => asset('storage/' . $app->file_path),
            ];
        });

        return Inertia::render('Admin/Applications/Index', [
            'applications' => $applications
        ]);
    }

    /**
     * Delete job application
     */
    public function destroy($id)
    {
        $application = JobApplication::findOrFail($id);

        // Delete CV file if exists
        if ($application->file_path && file_exists(public_path('storage/' . $application->file_path))) {
            unlink(public_path('storage/' . $application->file_path));
        }

        $application->delete();

        return redirect()->back()->with('success', 'Application deleted successfully.');
    }
}