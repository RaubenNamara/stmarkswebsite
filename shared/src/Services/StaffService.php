<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\Staff;

class StaffService extends SinglePhotoContentService
{
    public function __construct(private Staff $staffModel = new Staff())
    {
        parent::__construct($staffModel, 'staff', ['name' => ['required', 'max:255'], 'department' => ['required', 'max:255']]);
    }

    public function all(array $orderBy = ['created_at' => 'DESC']): array
    {
        return array_map([$this, 'withUrl'], $this->staffModel->allOrdered());
    }

    public function create(array $data, ?array $photoFile): array
    {
        $data['sort_order'] ??= $this->staffModel->count();
        return parent::create($data, $photoFile);
    }

    /** @param array<int, int> $orderedIds Staff ids in their new display order */
    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            $this->staffModel->update((int) $id, ['sort_order' => $index]);
        }
    }

    /**
     * Public staff directory, grouped under one canonical label per category. The live data has
     * inconsistent category strings from the old app ("Administrator" vs "Administrators", "Head
     * of Department" vs "Heads of Departments", "Support Staff" vs "Non Teaching Staff") - the old
     * app normalized this ad-hoc in three different controllers; this is the one place it happens
     * now (the plan's StaffDirectoryService consolidation).
     *
     * @return array<string, array> canonical category label => staff rows
     */
    public function groupedForDisplay(): array
    {
        $order = ['Head of Department', 'Teaching Staff', 'Administrator', 'Support Staff'];
        $groups = array_fill_keys($order, []);

        foreach ($this->all() as $member) {
            $groups[$this->canonicalCategory($member['category'] ?? '')][] = $member;
        }

        return array_filter($groups);
    }

    private function canonicalCategory(string $category): string
    {
        $lower = strtolower($category);

        return match (true) {
            str_contains($lower, 'head') => 'Head of Department',
            str_contains($lower, 'admin') => 'Administrator',
            str_contains($lower, 'teach') => 'Teaching Staff',
            default => 'Support Staff',
        };
    }
}
