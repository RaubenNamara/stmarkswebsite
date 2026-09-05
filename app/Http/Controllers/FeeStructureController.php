<?php

namespace App\Http\Controllers;

use App\Models\FeeStructure;
use Illuminate\Http\Response;

class FeeStructureController extends Controller
{
    /**
     * Show public list of fee structures (renders Explore/Fees.vue)
     */
    public function index()
    {
        $fees = FeeStructure::latest()->get();

        return inertia('Explore/Fees', [
            'fees' => $fees,
        ]);
    }

    /**
     * Serve PDF file (open in browser)
     */
    public function pdf($id)
    {
        $fee = FeeStructure::findOrFail($id);

        // storage/app/public/{file_path}
        $fullPath = storage_path('app/public/' . $fee->file_path);

        if (! file_exists($fullPath)) {
            abort(404);
        }

        return response()->file($fullPath);
    }
}