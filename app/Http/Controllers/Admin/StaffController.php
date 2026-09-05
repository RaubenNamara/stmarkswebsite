<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StaffController extends Controller
{
    private function normalizeCategory(?string $category): string
    {
        $value = trim((string) $category);
        $lower = strtolower($value);

        if ($lower === 'administrator' || $lower === 'administrators') {
            return 'Administrators';
        }

        if ($lower === 'head of department' || $lower === 'heads of departments') {
            return 'Heads of Departments';
        }

        if ($lower === 'support staff' || $lower === 'non teaching staff') {
            return 'Non Teaching Staff';
        }

        if ($lower === 'teaching staff') {
            return 'Teaching Staff';
        }

        return $value !== '' ? $value : 'Uncategorized';
    }

    private function categorySortValue(string $category): int
    {
        return match ($category) {
            'Administrators' => 1,
            'Heads of Departments' => 2,
            'Teaching Staff' => 3,
            'Non Teaching Staff' => 4,
            default => 5,
        };
    }

    /**
     * Display a listing of the staff.
     */
    public function index()
    {
        $staff = Staff::all()
            ->map(function ($item) {
                $item->category = $this->normalizeCategory($item->category);
                return $item;
            })
            ->sort(function ($a, $b) {
                $catA = $this->categorySortValue($a->category);
                $catB = $this->categorySortValue($b->category);

                if ($catA !== $catB) {
                    return $catA <=> $catB;
                }

                $orderA = (int) ($a->sort_order ?? 0);
                $orderB = (int) ($b->sort_order ?? 0);

                if ($orderA !== $orderB) {
                    return $orderA <=> $orderB;
                }

                return $a->id <=> $b->id;
            })
            ->values();

        return Inertia::render('Admin/Staff/Index', [
            'staff' => $staff,
        ]);
    }

    /**
     * Store a newly created staff.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
        ]);

        $category = $this->normalizeCategory($request->category);
        $path = null;

        if ($request->hasFile('photo')) {
            $destination = $_SERVER['DOCUMENT_ROOT'] . '/storage/staff';

            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            $filename = time() . '_' . preg_replace('/\s+/', '_', $request->file('photo')->getClientOriginalName());

            $request->file('photo')->move($destination, $filename);

            $path = 'staff/' . $filename;
        }

        $nextOrder = (Staff::where('category', $category)->max('sort_order') ?? 0) + 1;

        Staff::create([
            'name' => $request->name,
            'department' => $request->department,
            'category' => $category,
            'photo' => $path,
            'sort_order' => $nextOrder,
        ]);

        return back()->with('success', 'Staff added successfully.');
    }

    /**
     * Show the form for editing the specified staff.
     */
    public function edit(Staff $staff)
    {
        $staff->category = $this->normalizeCategory($staff->category);

        return Inertia::render('Admin/Staff/Edit', [
            'staff' => $staff,
        ]);
    }

    /**
     * Update the specified staff in storage.
     */
    public function update(Request $request, Staff $staff)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
        ]);

        $category = $this->normalizeCategory($request->category);
        $path = $staff->photo;

        if ($request->hasFile('photo')) {
            if ($staff->photo) {
                $oldPath = $_SERVER['DOCUMENT_ROOT'] . '/storage/' . $staff->photo;
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $destination = $_SERVER['DOCUMENT_ROOT'] . '/storage/staff';

            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            $filename = time() . '_' . preg_replace('/\s+/', '_', $request->file('photo')->getClientOriginalName());

            $request->file('photo')->move($destination, $filename);

            $path = 'staff/' . $filename;
        }

        $staff->update([
            'name' => $request->name,
            'department' => $request->department,
            'category' => $category,
            'photo' => $path,
        ]);

        return redirect()->route('admin.staff.index');
    }

    /**
     * Save staff order per category.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'staff' => ['required', 'array'],
            'staff.*.id' => ['required', 'integer'],
            'staff.*.sort_order' => ['required', 'integer'],
        ]);

        foreach ($request->staff as $item) {
            Staff::where('id', $item['id'])->update([
                'sort_order' => $item['sort_order'],
            ]);
        }

        return back()->with('success', 'Staff order updated successfully.');
    }

    /**
     * Remove the specified staff from storage.
     */
    public function destroy(Staff $staff)
    {
        if ($staff->photo) {
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/storage/' . $staff->photo;

            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        $staff->delete();

        return back()->with('success', 'Staff deleted successfully.');
    }
}