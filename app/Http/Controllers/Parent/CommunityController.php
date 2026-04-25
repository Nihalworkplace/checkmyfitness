<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\CommunityPostLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunityController extends Controller
{
    public function index()
    {
        $guardian = Auth::guard('parent')->user();

        $schoolNames = $guardian->students()->pluck('school_name')->unique()->filter()->values()->toArray();

        $posts = CommunityPost::where('is_published', true)
            ->where(function ($q) use ($schoolNames) {
                $q->where('audience', 'all');
                foreach ($schoolNames as $school) {
                    $q->orWhere('audience', 'school:' . $school);
                }
            })
            ->with(['postComments.author'])
            ->withCount('postLikes')
            ->latest()
            ->paginate(15);

        $likedIds = CommunityPostLike::where('parent_id', $guardian->id)
            ->whereIn('post_id', $posts->pluck('id'))
            ->pluck('post_id')
            ->toArray();

        return view('parent.community', compact('posts', 'likedIds', 'guardian'));
    }

    public function like(CommunityPost $post)
    {
        $parentId = Auth::guard('parent')->id();

        $existing = CommunityPostLike::where('post_id', $post->id)
            ->where('parent_id', $parentId)
            ->first();

        if ($existing) {
            $existing->delete();
            $post->decrement('likes');
        } else {
            CommunityPostLike::create(['post_id' => $post->id, 'parent_id' => $parentId]);
            $post->increment('likes');
        }

        return back();
    }

    public function comment(Request $request, CommunityPost $post)
    {
        $request->validate(['body' => 'required|string|max:1000']);

        CommunityComment::create([
            'post_id'   => $post->id,
            'parent_id' => Auth::guard('parent')->id(),
            'body'      => $request->body,
        ]);

        $post->increment('comments');

        return back();
    }

    public function deleteComment(CommunityComment $comment)
    {
        if ($comment->parent_id !== Auth::guard('parent')->id()) {
            abort(403);
        }

        $comment->post->decrement('comments');
        $comment->delete();

        return back();
    }
}
