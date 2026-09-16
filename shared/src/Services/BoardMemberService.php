<?php

declare(strict_types=1);

namespace StMarks\Shared\Services;

use StMarks\Shared\Models\BoardMember;

class BoardMemberService extends SinglePhotoContentService
{
    public function __construct(private BoardMember $boardMemberModel = new BoardMember())
    {
        parent::__construct(
            $boardMemberModel,
            'board-members',
            ['name' => ['required', 'max:255'], 'position' => ['required', 'max:255']]
        );
    }

    public function all(array $orderBy = ['created_at' => 'DESC']): array
    {
        return array_map([$this, 'withUrl'], $this->boardMemberModel->allOrdered());
    }

    public function create(array $data, ?array $photoFile): array
    {
        $data['sort_order'] ??= $this->boardMemberModel->count();
        return parent::create($data, $photoFile);
    }

    /** @param array<int, int> $orderedIds Board member ids in their new display order */
    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            $this->boardMemberModel->update((int) $id, ['sort_order' => $index]);
        }
    }
}
