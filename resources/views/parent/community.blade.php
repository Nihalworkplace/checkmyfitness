@extends('layouts.app')
@section('title','Community')
@section('page-title','Community')

@section('sidebar-nav')
@include('parent.partials.nav')
@endsection

@section('content')

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($posts->isEmpty())
  <div class="card" style="text-align:center;padding:48px;">
    <div style="font-size:48px;margin-bottom:16px;">📣</div>
    <div style="font-size:16px;font-weight:700;color:var(--gr);">No posts yet.</div>
    <div style="font-size:13px;color:var(--bd);margin-top:8px;">Your school's health updates will appear here.</div>
  </div>
@else
  @foreach($posts as $post)
    <div class="card" style="margin-bottom:16px;">

      {{-- Post header --}}
      <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:10px;">
        <div>
          <div style="font-size:16px;font-weight:700;color:var(--dk);line-height:1.3;">{{ $post->title }}</div>
          <div style="font-size:11px;color:var(--gr);margin-top:3px;">
            {{ $post->created_at->diffForHumans() }}
            &nbsp;·&nbsp;
            {{ $post->audience === 'all' ? 'All parents' : str_replace('school:', '', $post->audience) }}
          </div>
        </div>
      </div>

      {{-- Post content --}}
      <div style="font-size:14px;color:var(--dk);line-height:1.7;margin-bottom:12px;">{{ $post->content }}</div>

      {{-- Tags --}}
      @if($post->tags)
        <div style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:12px;">
          @foreach($post->tags as $tag)
            <span style="font-size:11px;font-weight:700;color:var(--g);background:#F0FDF9;padding:3px 9px;border-radius:20px;border:1px solid #D1FAE5;">{{ $tag }}</span>
          @endforeach
        </div>
      @endif

      {{-- Like & comment counts --}}
      <div style="display:flex;align-items:center;gap:16px;padding:10px 0;border-top:1px solid var(--lgr);border-bottom:1px solid var(--lgr);margin-bottom:12px;">
        <form method="POST" action="{{ route('parent.community.like', $post) }}">
          @csrf
          <button type="submit" style="display:flex;align-items:center;gap:5px;font-size:13px;font-weight:600;color:{{ in_array($post->id, $likedIds) ? 'var(--r)' : 'var(--gr)' }};background:none;border:none;cursor:pointer;padding:0;">
            {{ in_array($post->id, $likedIds) ? '❤️' : '🤍' }}
            <span>{{ $post->likes }}</span>
          </button>
        </form>
        <span style="display:flex;align-items:center;gap:5px;font-size:13px;color:var(--gr);">
          💬 <span>{{ $post->comments }}</span>
        </span>
      </div>

      {{-- Comments list --}}
      @if($post->postComments->isNotEmpty())
        <div style="margin-bottom:12px;">
          @foreach($post->postComments->take(10) as $comment)
            <div style="display:flex;align-items:flex-start;gap:10px;padding:8px 0;border-bottom:1px solid var(--lgr);">
              <div style="width:30px;height:30px;border-radius:50%;background:rgba(29,158,117,0.15);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:var(--g);flex-shrink:0;">
                {{ strtoupper(substr($comment->author->name ?? 'P', 0, 1)) }}
              </div>
              <div style="flex:1;">
                <div style="font-size:12px;font-weight:600;color:var(--dk);">{{ $comment->author->name ?? 'Parent' }}</div>
                <div style="font-size:13px;color:var(--dk);margin-top:2px;line-height:1.5;">{{ $comment->body }}</div>
                <div style="font-size:11px;color:var(--gr);margin-top:3px;">{{ $comment->created_at->diffForHumans() }}</div>
              </div>
              @if($comment->parent_id === $guardian->id)
                <form method="POST" action="{{ route('parent.community.comment.destroy', $comment) }}">
                  @csrf @method('DELETE')
                  <button type="submit" style="font-size:11px;color:var(--r);background:none;border:none;cursor:pointer;padding:2px 6px;" onclick="return confirm('Delete comment?')">✕</button>
                </form>
              @endif
            </div>
          @endforeach
        </div>
      @endif

      {{-- Add comment form --}}
      <form method="POST" action="{{ route('parent.community.comment', $post) }}" style="display:flex;gap:8px;align-items:flex-end;">
        @csrf
        <textarea name="body" class="form-input" placeholder="Write a comment…" rows="1"
          style="flex:1;resize:none;min-height:38px;line-height:1.4;padding:8px 12px;font-size:13px;"
          required maxlength="1000"></textarea>
        <button type="submit" class="btn btn-g btn-sm" style="flex-shrink:0;">Post</button>
      </form>

    </div>
  @endforeach

  @if($posts->hasPages())
    <div style="padding:8px 0;">{{ $posts->links() }}</div>
  @endif
@endif

@endsection
