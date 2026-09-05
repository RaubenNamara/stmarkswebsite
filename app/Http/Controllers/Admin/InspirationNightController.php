<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InspirationNight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class InspirationNightController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = 10;

        $talks = InspirationNight::latest()
            ->paginate($perPage)
            ->through(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'speaker' => $item->speaker,
                    'date' => $item->date, // FIXED HERE
                    'image_url' => $item->image ? Storage::url($item->image) : null,
                    'created_at' => $item->created_at,
                ];
            });

        return Inertia::render('Admin/InspirationNight/Index', [
            'talks' => $talks,
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/InspirationNight/Create');
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|max:5120',
            'video' => 'nullable|url',
            'date' => 'nullable|date',
            'speaker' => 'nullable|string|max:255',
        ]);


        if ($request->hasFile('image')) {

            $data['image'] = $request->file('image')->store('inspiration', 'public');

        }


        InspirationNight::create($data);


        return redirect()->route('admin.inspiration.index')
            ->with('success', 'Inspiration Night created successfully.');

    }



    /**
     * Display the specified resource.
     */
    public function show(InspirationNight $inspiration)
    {

        $talk = $inspiration->toArray();

        $talk['image_url'] = $inspiration->image
            ? Storage::url($inspiration->image)
            : null;

        return Inertia::render('Admin/InspirationNight/Show', [
            'talk' => $talk
        ]);

    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InspirationNight $inspiration)
    {

        $talk = $inspiration->toArray();

        $talk['image_url'] = $inspiration->image
            ? Storage::url($inspiration->image)
            : null;

        return Inertia::render('Admin/InspirationNight/Edit', [
            'talk' => $talk
        ]);

    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InspirationNight $inspiration)
    {

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|max:5120',
            'video' => 'nullable|url',
            'date' => 'nullable|date',
            'speaker' => 'nullable|string|max:255',
        ]);


        if ($request->hasFile('image')) {

            if ($inspiration->image && Storage::disk('public')->exists($inspiration->image)) {

                Storage::disk('public')->delete($inspiration->image);

            }

            $data['image'] = $request->file('image')->store('inspiration', 'public');

        }


        $inspiration->update($data);


        return redirect()->route('admin.inspiration.index')
            ->with('success', 'Inspiration Night updated successfully.');

    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InspirationNight $inspiration)
    {

        if ($inspiration->image && Storage::disk('public')->exists($inspiration->image)) {

            Storage::disk('public')->delete($inspiration->image);

        }


        $inspiration->delete();


        return redirect()->route('admin.inspiration.index')
            ->with('success', 'Inspiration Night deleted successfully.');

    }

}