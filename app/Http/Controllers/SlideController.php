<?php

namespace App\Http\Controllers;

use App\Models\Slide;
use Illuminate\Http\Request;

class SlideController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'nullable|image|max:20480',
            'video' => 'nullable|mimes:mp4,mov,avi|max:51200',
            'title' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:255',
        ]);

        if (!$request->hasFile('image') && !$request->hasFile('video')) {
            return back()->withErrors([
                'file' => 'Please upload an image or a video.'
            ]);
        }

        $slideData = [
            'title' => $request->title,
            'caption' => $request->caption,
            'order' => Slide::count() + 1,
            'is_active' => true,
            'type' => 'image',
            'image_path' => null,
            'video_path' => null,
        ];

        $destination = base_path('../storage/slides');

        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        if ($request->hasFile('video')) {
            $videoDir = $destination . '/videos';

            if (!is_dir($videoDir)) {
                mkdir($videoDir, 0777, true);
            }

            $file = $request->file('video');
            $filename = time() . '_vid_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $file->getClientOriginalName());
            $file->move($videoDir, $filename);

            $slideData['video_path'] = 'slides/videos/' . $filename;
            $slideData['type'] = 'video';
        }

        if ($request->hasFile('image')) {
            $imageDir = $destination . '/images';

            if (!is_dir($imageDir)) {
                mkdir($imageDir, 0777, true);
            }

            $file = $request->file('image');
            $filename = time() . '_img_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $file->getClientOriginalName());
            $file->move($imageDir, $filename);

            $slideData['image_path'] = 'slides/images/' . $filename;
            $slideData['type'] = 'image';
        }

        Slide::create($slideData);

        return back()->with('success', 'Slide uploaded successfully.');
    }

    public function destroy(Slide $slide)
    {
        if ($slide->image_path) {
            $imageFullPath = base_path('../storage/' . $slide->image_path);
            if (file_exists($imageFullPath)) {
                unlink($imageFullPath);
            }
        }

        if ($slide->video_path) {
            $videoFullPath = base_path('../storage/' . $slide->video_path);
            if (file_exists($videoFullPath)) {
                unlink($videoFullPath);
            }
        }

        $slide->delete();

        return back()->with('success', 'Slide deleted successfully.');
    }
}