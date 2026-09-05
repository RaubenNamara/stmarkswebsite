<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmosaAlumni;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SmosaAlumniController extends Controller
{
    public function index()
    {
        $alumni = SmosaAlumni::latest()->get()->map(function ($a) {
            return [
                'id' => $a->id,
                'name' => $a->name,
                'profession' => $a->profession,
                'message' => $a->message,
                'video' => $a->video,

                // ✅ FIX
                'photo_url' => $a->photo
                    ? asset('storage/smosa/' . $a->photo)
                    : null,
            ];
        });

        return Inertia::render('Admin/SmosaAlumni/Index', [
            'alumni' => $alumni,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/SmosaAlumni/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'profession' => 'nullable|string|max:255',
            'message' => 'nullable|string',
            'video' => 'nullable|string',
            'photo' => 'nullable|image|max:4096',
        ]);

        $destination = base_path('../storage/smosa');

        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');

            $filename = time() . '_img_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $file->getClientOriginalName());

            $file->move($destination, $filename);

            $data['photo'] = $filename;
        }

        SmosaAlumni::create($data);

        return redirect()->route('admin.smosa.index')
            ->with('success', 'Alumni added.');
    }

    public function edit(SmosaAlumni $smosa)
    {
        return Inertia::render('Admin/SmosaAlumni/Edit', [
            'alumni' => [
                'id' => $smosa->id,
                'name' => $smosa->name,
                'profession' => $smosa->profession,
                'message' => $smosa->message,
                'video' => $smosa->video,

                // ✅ FIX
                'photo_url' => $smosa->photo
                    ? asset('storage/smosa/' . $smosa->photo)
                    : null,

                'photo' => $smosa->photo,
            ]
        ]);
    }

    public function update(Request $request, SmosaAlumni $smosa)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'profession' => 'nullable|string|max:255',
            'message' => 'nullable|string',
            'video' => 'nullable|string',
            'photo' => 'nullable|image|max:4096',
        ]);

        $destination = base_path('../storage/smosa');

        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        if ($request->hasFile('photo')) {

            // delete old
            if ($smosa->photo) {
                $oldPath = base_path('../storage/smosa/' . $smosa->photo);
                if (file_exists($oldPath)) unlink($oldPath);
            }

            $file = $request->file('photo');

            $filename = time() . '_img_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $file->getClientOriginalName());

            $file->move($destination, $filename);

            $data['photo'] = $filename;
        }

        $smosa->update($data);

        return redirect()->route('admin.smosa.index')
            ->with('success', 'Alumni updated.');
    }

    public function destroy(SmosaAlumni $smosa)
    {
        if ($smosa->photo) {
            $path = base_path('../storage/smosa/' . $smosa->photo);
            if (file_exists($path)) unlink($path);
        }

        $smosa->delete();

        return redirect()->route('admin.smosa.index')
            ->with('success', 'Alumni deleted.');
    }
}