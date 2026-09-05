<?php

namespace App\Http\Controllers;

use App\Models\ChristmasCantata;
use Illuminate\Support\Facades\Storage;
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
                'date' => $c->date,
                'description' => $c->description,
                'video' => $c->video,
                'image' => $c->image ? Storage::url($c->image) : null,
            ];
        });

        return Inertia::render('Empowerment/ChristmasCantata', [
            'cantatas' => $cantatas
        ]);
    }
}