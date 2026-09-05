<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mentorship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MentorshipController extends Controller
{

    public function index()
    {
        $mentorships = Mentorship::latest()->get();

        return Inertia::render('Admin/Mentorship/Index', [
            'mentorships' => $mentorships
        ]);
    }


    public function create()
    {
        return Inertia::render('Admin/Mentorship/Create');
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'caption' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'video' => 'nullable|mimes:mp4,mov,avi|max:20000',
            'video_link' => 'nullable|url'
        ]);


        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('mentorship/images', 'public');
        }


        if ($request->hasFile('video')) {
            $data['video'] = $request->file('video')->store('mentorship/videos', 'public');
        }

        Mentorship::create($data);

        return redirect()->route('admin.mentorship.index')
            ->with('success', 'Mentorship content added successfully');
    }


    public function edit($id)
    {
        $mentorship = Mentorship::findOrFail($id);

        return Inertia::render('Admin/Mentorship/Edit', [
            'mentorship' => $mentorship
        ]);
    }


    public function update(Request $request, $id)
    {
        $mentorship = Mentorship::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'caption' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'video' => 'nullable|mimes:mp4,mov,avi|max:20000',
            'video_link' => 'nullable|url'
        ]);


        if ($request->hasFile('image')) {

            if ($mentorship->image && Storage::disk('public')->exists($mentorship->image)) {
                Storage::disk('public')->delete($mentorship->image);
            }

            $data['image'] = $request->file('image')->store('mentorship/images', 'public');
        }


        if ($request->hasFile('video')) {

            if ($mentorship->video && Storage::disk('public')->exists($mentorship->video)) {
                Storage::disk('public')->delete($mentorship->video);
            }

            $data['video'] = $request->file('video')->store('mentorship/videos', 'public');
        }

        $mentorship->update($data);

        return redirect()->route('admin.mentorship.index')
            ->with('success', 'Mentorship updated successfully');
    }


    public function destroy($id)
    {
        $mentorship = Mentorship::findOrFail($id);


        if ($mentorship->image && Storage::disk('public')->exists($mentorship->image)) {
            Storage::disk('public')->delete($mentorship->image);
        }


        if ($mentorship->video && Storage::disk('public')->exists($mentorship->video)) {
            Storage::disk('public')->delete($mentorship->video);
        }

        $mentorship->delete();

        return back()->with('success', 'Mentorship deleted successfully');
    }
}