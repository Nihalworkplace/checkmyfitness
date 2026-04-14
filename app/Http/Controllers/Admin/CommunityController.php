<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Models\School;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function index()
    {
        $posts  = CommunityPost::with('author')->latest()->paginate(20);
        $schools = School::where('is_active', true)->orderBy('name')->pluck('name');

        return view('admin.community.index', compact('posts', 'schools'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'    => 'required|string|max:255',
            'content'  => 'required|string|max:5000',
            'tags'     => 'nullable|array',
            'tags.*'   => 'string|max:40',
            'audience' => 'required|string|max:100',
        ]);

        CommunityPost::create($data + ['created_by' => auth()->id(), 'is_published' => true]);

        return back()->with('success', 'Post published successfully!');
    }

    public function boost(CommunityPost $post)
    {
        $post->increment('likes', 10);
        return back()->with('success', 'Post boosted!');
    }

    public function destroy(CommunityPost $post)
    {
        $post->delete();
        return back()->with('success', 'Post removed.');
    }
}
