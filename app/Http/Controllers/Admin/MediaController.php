<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MediaController extends Controller
{
    public function index()
    {
        $media = Media::latest()->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'type' => $item->type,
                'video_url' => $item->video_url,
                'file_path' => $item->file_path,
                'file_url' => $item->file_path ? asset('storage/' . $item->file_path) : null,
            ];
        });

        return Inertia::render('Admin/Media/Index', [
            'mediaItems' => $media
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'type' => 'required|in:image,video,link',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:20480',
            'video_url' => 'nullable|url'
        ]);

        $filePath = null;

        if ($request->hasFile('file')) {
            $destination = base_path('../storage/media');

            if (!is_dir($destination)) {
                mkdir($destination, 0777, true);
            }

            $file = $request->file('file');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $file->getClientOriginalName());
            $file->move($destination, $filename);

            $filePath = 'media/' . $filename;
        }

        Media::create([
            'title' => $request->title,
            'type' => $request->type,
            'file_path' => $filePath,
            'video_url' => $request->video_url,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.media.index')
            ->with('success', 'Media uploaded successfully!');
    }

    public function destroy(Media $media)
    {
        if ($media->file_path) {
            $filePath = base_path('../storage/' . $media->file_path);

            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $media->delete();

        return redirect()
            ->route('admin.media.index')
            ->with('success', 'Media deleted successfully!');
    }
}