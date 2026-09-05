<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Club;
use App\Models\ClubImage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ClubController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        return Inertia::render('Admin/Clubs/Index', [
            'clubs' => Club::with('images')->latest()->get()
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        return Inertia::render('Admin/Clubs/Create');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'images.*' => 'nullable|image|max:2048',
            'captions.*' => 'nullable|string|max:255',
        ]);

        // Generate unique slug
        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $count = 1;

        while (Club::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $club = Club::create([
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
        ]);

        // Upload images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {

                $path = $image->store('clubs', 'public');

                $club->images()->create([
                    'image_path' => $path,
                    'caption' => $request->captions[$index] ?? null,
                ]);
            }
        }

        return redirect()->route('admin.clubs.index')
            ->with('success', 'Club created successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show(Club $club)
    {
        abort(404);
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit(Club $club)
    {
        $club->load('images');

        return Inertia::render('Admin/Clubs/Edit', [
            'club' => $club
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Club $club)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'images.*' => 'nullable|image|max:2048',
            'captions.*' => 'nullable|string|max:255',
            'existing_images' => 'nullable|array'
        ]);

        // Generate unique slug (ignore current club)
        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $count = 1;

        while (
            Club::where('slug', $slug)
                ->where('id', '!=', $club->id)
                ->exists()
        ) {
            $slug = $originalSlug . '-' . $count++;
        }

        // Update club
        $club->update([
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
        ]);

        /*
        |--------------------------------------------------------------------------
        | UPDATE EXISTING IMAGE CAPTIONS
        |--------------------------------------------------------------------------
        */
        if ($request->has('existing_images')) {
            foreach ($request->existing_images as $img) {

                $image = ClubImage::find($img['id']);

                if ($image) {
                    $image->update([
                        'caption' => $img['caption'] ?? null
                    ]);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | ADD NEW IMAGES
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {

                $path = $image->store('clubs', 'public');

                $club->images()->create([
                    'image_path' => $path,
                    'caption' => $request->captions[$index] ?? null,
                ]);
            }
        }

        return redirect()->route('admin.clubs.index')
            ->with('success', 'Club updated successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE SINGLE IMAGE
    |--------------------------------------------------------------------------
    */
    public function deleteImage($id)
    {
        $image = ClubImage::findOrFail($id);

        Storage::disk('public')->delete($image->image_path);

        $image->delete();

        return back()->with('success', 'Image deleted successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY CLUB
    |--------------------------------------------------------------------------
    */
    public function destroy(Club $club)
    {
        foreach ($club->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $club->delete();

        return redirect()->route('admin.clubs.index')
            ->with('success', 'Club deleted successfully');
    }
}