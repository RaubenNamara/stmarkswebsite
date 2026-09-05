<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentLeadership;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentLeadershipController extends Controller
{
    public function index()
    {
        $leaders = StudentLeadership::latest()->get();

        return Inertia::render('Admin/StudentLeadership/Index', [
            'leaders' => $leaders,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/StudentLeadership/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'image_path' => ['nullable', 'image', 'max:4096'],
            'video_path' => ['nullable', 'file', 'max:51200'],
            'video_link' => ['nullable', 'url', 'max:2048'],
        ]);

        // ✅ CORRECT PATH → public_html/storage
        $destination = base_path('../storage');

        // ✅ IMAGE UPLOAD
        if ($request->hasFile('image_path')) {
            $file = $request->file('image_path');

            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            $file->move($destination, $filename);

            $data['image_path'] = $filename;
        }

        // ✅ VIDEO UPLOAD
        if ($request->hasFile('video_path')) {
            $file = $request->file('video_path');

            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            $file->move($destination, $filename);

            $data['video_path'] = $filename;
        }

        StudentLeadership::create($data);

        return redirect()
            ->route('admin.student-leadership.index')
            ->with('success', 'Student leadership created successfully.');
    }

    public function edit(StudentLeadership $studentLeadership)
    {
        return Inertia::render('Admin/StudentLeadership/Edit', [
            'leader' => $studentLeadership,
        ]);
    }

    public function update(Request $request, StudentLeadership $studentLeadership)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'image_path' => ['nullable', 'image', 'max:4096'],
            'video_path' => ['nullable', 'file', 'max:51200'],
            'video_link' => ['nullable', 'url', 'max:2048'],
        ]);

        // ✅ CORRECT PATH
        $destination = base_path('../storage');

        // ✅ IMAGE UPDATE
        if ($request->hasFile('image_path')) {

            // delete old image
            if ($studentLeadership->image_path) {
                $oldPath = $destination . '/' . $studentLeadership->image_path;
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $file = $request->file('image_path');

            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            $file->move($destination, $filename);

            $data['image_path'] = $filename;
        } else {
            $data['image_path'] = $studentLeadership->image_path;
        }

        // ✅ VIDEO UPDATE
        if ($request->hasFile('video_path')) {

            // delete old video
            if ($studentLeadership->video_path) {
                $oldPath = $destination . '/' . $studentLeadership->video_path;
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $file = $request->file('video_path');

            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            $file->move($destination, $filename);

            $data['video_path'] = $filename;
        } else {
            $data['video_path'] = $studentLeadership->video_path;
        }

        $studentLeadership->update($data);

        return redirect()
            ->route('admin.student-leadership.index')
            ->with('success', 'Student leadership updated successfully.');
    }

    public function destroy(StudentLeadership $studentLeadership)
    {
        $destination = base_path('../storage');

        // delete image
        if ($studentLeadership->image_path) {
            $path = $destination . '/' . $studentLeadership->image_path;
            if (file_exists($path)) {
                unlink($path);
            }
        }

        // delete video
        if ($studentLeadership->video_path) {
            $path = $destination . '/' . $studentLeadership->video_path;
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $studentLeadership->delete();

        return back()->with('success', 'Deleted successfully.');
    }
}