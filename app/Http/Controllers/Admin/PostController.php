<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index - Show all posts
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        return Inertia::render('Admin/Posts/Index', [
            'posts' => Post::latest()->get()
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Create - Show create form
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        return Inertia::render('Admin/Posts/Create');
    }

    /*
    |--------------------------------------------------------------------------
    | Store - Save new post
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        Post::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'content' => $request->content,
        ]);

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Edit - Show edit form
    |--------------------------------------------------------------------------
    */
    public function edit(Post $post)
    {
        return Inertia::render('Admin/Posts/Create', [
            'post' => $post
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update - Update existing post
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'content' => $request->content,
        ]);

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy - Delete post
    |--------------------------------------------------------------------------
    */
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post deleted successfully.');
    }
}