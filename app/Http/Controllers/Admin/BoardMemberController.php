<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BoardMember;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BoardMemberController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/BoardMembers/Index', [
            'members' => BoardMember::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('board', 'public');
        }

        BoardMember::create($validated);

        return back()->with('success', 'Board member added.');
    }

    public function edit(BoardMember $boardMember)
    {
        return Inertia::render('Admin/BoardMembers/Edit', [
            'member' => $boardMember
        ]);
    }

    public function update(Request $request, BoardMember $boardMember)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('board', 'public');
        }

        $boardMember->update($validated);

        return redirect()->route('admin.board-members.index')
            ->with('success', 'Board member updated.');
    }

    public function destroy(BoardMember $boardMember)
    {
        $boardMember->delete();

        return back()->with('success', 'Deleted successfully.');
    }
}