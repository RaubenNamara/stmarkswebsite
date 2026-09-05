<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChristmasCantata;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChristmasCantataController extends Controller
{
    public function index()
    {
        $cantatas = ChristmasCantata::latest()->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'title' => $c->title,
                'choir' => $c->choir,
                'date' => $c->date ? $c->date->format('Y-m-d') : null,
                'description' => $c->description,
                'video' => $c->video,

                // ✅ FIX
                'image_url' => $c->image
                    ? asset('storage/cantatas/' . $c->image)
                    : null,
            ];
        });

        return Inertia::render('Admin/ChristmasCantata/Index', [
            'cantatas' => $cantatas
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/ChristmasCantata/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'choir' => 'nullable|string',
            'date' => 'nullable|date',
            'description' => 'nullable|string',
            'video' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $destination = base_path('../storage/cantatas');

        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');

            $filename = time() . '_img_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $file->getClientOriginalName());

            $file->move($destination, $filename);

            $data['image'] = $filename;
        }

        ChristmasCantata::create($data);

        return redirect()->route('admin.christmas-cantata.index');
    }

    public function edit(ChristmasCantata $christmasCantata)
    {
        return Inertia::render('Admin/ChristmasCantata/Edit', [
            'cantata' => [
                'id' => $christmasCantata->id,
                'title' => $christmasCantata->title,
                'choir' => $christmasCantata->choir,
                'date' => $christmasCantata->date ? $christmasCantata->date->format('Y-m-d') : null,
                'description' => $christmasCantata->description,
                'video' => $christmasCantata->video,

                // ✅ FIX
                'image_url' => $christmasCantata->image
                    ? asset('storage/cantatas/' . $christmasCantata->image)
                    : null,

                'image' => $christmasCantata->image,
            ]
        ]);
    }

    public function update(Request $request, ChristmasCantata $christmasCantata)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'choir' => 'nullable|string',
            'date' => 'nullable|date',
            'description' => 'nullable|string',
            'video' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $destination = base_path('../storage/cantatas');

        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        if ($request->hasFile('image')) {

            if ($christmasCantata->image) {
                $oldPath = base_path('../storage/cantatas/' . $christmasCantata->image);
                if (file_exists($oldPath)) unlink($oldPath);
            }

            $file = $request->file('image');

            $filename = time() . '_img_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $file->getClientOriginalName());

            $file->move($destination, $filename);

            $data['image'] = $filename;
        }

        $christmasCantata->update($data);

        return redirect()->route('admin.christmas-cantata.index');
    }

    public function destroy(ChristmasCantata $christmasCantata)
    {
        if ($christmasCantata->image) {
            $path = base_path('../storage/cantatas/' . $christmasCantata->image);
            if (file_exists($path)) unlink($path);
        }

        $christmasCantata->delete();

        return redirect()->route('admin.christmas-cantata.index');
    }
}