<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GirlBoyTalk;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GirlBoyTalkController extends Controller
{
    public function index()
    {
        $talks = GirlBoyTalk::latest()->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'image' => $item->image,
                'video' => $item->video,
                'video_link' => $item->video_link,

                'image_url' => $item->image
                    ? asset('storage/girlboytalk/' . $item->image)
                    : null,

                'video_url' => $item->video
                    ? asset('storage/girlboytalk/' . $item->video)
                    : null,
            ];
        });

        return Inertia::render('Admin/GirlBoyTalk/Index', [
            'talks' => $talks
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/GirlBoyTalk/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'video' => 'nullable|mimes:mp4,mov,avi|max:20480',
            'video_link' => 'nullable|url',
        ]);

        $destination = base_path('../storage/girlboytalk');

        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        $imagePath = null;
        $videoPath = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_img_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $file->getClientOriginalName());
            $file->move($destination, $filename);
            $imagePath = $filename;
        }

        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $filename = time() . '_vid_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $file->getClientOriginalName());
            $file->move($destination, $filename);
            $videoPath = $filename;
        }

        GirlBoyTalk::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imagePath,
            'video' => $videoPath,
            'video_link' => $request->video_link,
        ]);

        return redirect()->route('admin.girlboytalk.index')
            ->with('success', 'Talk created successfully.');
    }

    public function edit(GirlBoyTalk $girlboytalk)
    {
        return Inertia::render('Admin/GirlBoyTalk/Edit', [
            'talk' => $girlboytalk
        ]);
    }

    public function update(Request $request, GirlBoyTalk $girlboytalk)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'video' => 'nullable|mimes:mp4,mov,avi|max:20480',
            'video_link' => 'nullable|url',
        ]);

        $destination = base_path('../storage/girlboytalk');

        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        $imagePath = $girlboytalk->image;
        $videoPath = $girlboytalk->video;

        if ($request->hasFile('image')) {
            if ($girlboytalk->image) {
                $oldPath = base_path('../storage/girlboytalk/' . $girlboytalk->image);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $file = $request->file('image');
            $filename = time() . '_img_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $file->getClientOriginalName());
            $file->move($destination, $filename);
            $imagePath = $filename;
        }

        if ($request->hasFile('video')) {
            if ($girlboytalk->video) {
                $oldPath = base_path('../storage/girlboytalk/' . $girlboytalk->video);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $file = $request->file('video');
            $filename = time() . '_vid_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $file->getClientOriginalName());
            $file->move($destination, $filename);
            $videoPath = $filename;
        }

        $girlboytalk->update([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imagePath,
            'video' => $videoPath,
            'video_link' => $request->video_link,
        ]);

        return redirect()->route('admin.girlboytalk.index')
            ->with('success', 'Talk updated successfully.');
    }

    public function destroy(GirlBoyTalk $girlboytalk)
    {
        if ($girlboytalk->image) {
            $path = base_path('../storage/girlboytalk/' . $girlboytalk->image);
            if (file_exists($path)) {
                unlink($path);
            }
        }

        if ($girlboytalk->video) {
            $path = base_path('../storage/girlboytalk/' . $girlboytalk->video);
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $girlboytalk->delete();

        return back()->with('success', 'Talk deleted successfully.');
    }
}