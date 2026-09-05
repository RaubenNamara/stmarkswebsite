<?php

namespace App\Http\Controllers;

use App\Models\Chaplaincy;
use Inertia\Inertia;

class ChaplaincyController extends Controller
{
    public function index()
    {
        $items = Chaplaincy::select(
            'id',
            'title',
            'content',
            'image',
            'video',
            'video_link',
            'created_at',
            'updated_at'
        )->latest()->get();

        return Inertia::render('Empowerment/Chaplaincy', [
            'items' => $items
        ]);
    }
}