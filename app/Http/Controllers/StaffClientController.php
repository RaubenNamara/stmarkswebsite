<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Inertia\Inertia;

class StaffClientController extends Controller
{
    /**
     * Normalize category (same logic as admin)
     */
    private function normalizeCategory(?string $category): string
    {
        $value = trim((string) $category);
        $lower = strtolower($value);

        if ($lower === 'administrator' || $lower === 'administrators') {
            return 'Administrators';
        }

        if (
            $lower === 'head of department' ||
            $lower === 'heads of department' ||
            $lower === 'heads of departments'
        ) {
            return 'Heads of Departments';
        }

        if (
            $lower === 'support staff' ||
            $lower === 'non teaching staff' ||
            $lower === 'non-teaching staff'
        ) {
            return 'Non Teaching Staff';
        }

        if ($lower === 'teaching staff') {
            return 'Teaching Staff';
        }

        return $value !== '' ? $value : 'Uncategorized';
    }

    /**
     * Category priority for sorting
     */
    private function categoryPriority(string $category): int
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
     * Display staff for client side
     */
    public function index()
    {
        $staff = Staff::all()
            ->map(function ($item) {
                $item->category = $this->normalizeCategory($item->category);
                return $item;
            })
            ->sort(function ($a, $b) {

                // 1. Sort by category priority
                $catA = $this->categoryPriority($a->category);
                $catB = $this->categoryPriority($b->category);

                if ($catA !== $catB) {
                    return $catA <=> $catB;
                }

                // 2. Sort by order inside category
                $orderA = (int) ($a->sort_order ?? 0);
                $orderB = (int) ($b->sort_order ?? 0);

                if ($orderA !== $orderB) {
                    return $orderA <=> $orderB;
                }

                // 3. Fallback to ID
                return $a->id <=> $b->id;
            })
            ->values()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'department' => $item->department,
                    'category' => $item->category,
                    'photo' => $item->photo,
                    'sort_order' => $item->sort_order,
                ];
            });

        return Inertia::render('Client/Staff', [
            'staff' => $staff
        ]);
    }
}