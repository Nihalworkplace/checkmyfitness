@extends('layouts.app')
@section('title','Community')
@section('page-title','Community')

@section('sidebar-nav')
@include('admin.partials.nav')
@endsection

@section('content')
<div class="g2">
  {{-- Create post --}}
  <div>
    <div class="card">
      <div class="card-header">
        <div class="card-title">📣 Create Post</div>
      </div>

      <form method="POST" action="{{ route('admin.community.store') }}">
        @csrf

        <div class="form-group">
          <label class="form-label">Title <span class="req">*</span></label>
          <input type="text" name="title" class="form-input" placeholder="This Week's Health Challenge 🌟" value="{{ old('title') }}" required/>
        </div>

        <div class="form-group">
          <label class="form-label">Content <span class="req">*</span></label>
          <textarea name="content" class="form-input" style="min-height:120px;" placeholder="Share a health tip, challenge, or update with parents…" required>{{ old('content') }}</textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Tags</label>
          <div style="display:flex;flex-wrap:wrap;gap:8px;">
            @foreach(['#Hydration','#Nutrition','#Exercise','#Dental','#Mental','#Vision','#Iron','#VitaminD'] as $tag)
              <label style="display:flex;align-items:center;gap:5px;cursor:pointer;">
                <input type="checkbox" name="tags[]" value="{{ $tag }}"
                  {{ is_array(old('tags')) && in_array($tag, old('tags')) ? 'checked' : '' }}
                  style="accent-color:var(--g);">
                <span style="font-size:12px;font-weight:600;color:var(--gr);">{{ $tag }}</span>
              </label>
            @endforeach
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Audience</label>
          <select name="audience" class="form-input">
            <option value="all" {{ old('audience','all')==='all'?'selected':'' }}>All parents</option>
            @foreach($schools as $school)
              <option value="school:{{ $school }}" {{ old('audience')==='school:'.$school?'selected':'' }}>
                {{ $school }} only
              </option>
            @endforeach
          </select>
        </div>

        <button type="submit" class="btn btn-g btn-lg btn-full">Publish to Community →</button>
      </form>
    </div>
  </div>

  {{-- Recent posts --}}
  <div>
    <div class="card">
      <div class="card-header">
        <div class="card-title">📰 Recent Posts</div>
      </div>

      @forelse($posts as $post)
        <div style="padding:14px 0;border-bottom:1px solid var(--lgr);">
          <div style="font-size:14px;font-weight:700;color:var(--dk);margin-bottom:5px;">{{ $post->title }}</div>
          <div style="font-size:12px;color:var(--gr);line-height:1.5;margin-bottom:8px;">
            {{ Str::limit($post->content, 120) }}
          </div>
          @if($post->tags)
            <div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:8px;">
              @foreach($post->tags as $tag)
                <span style="font-size:10px;font-weight:700;color:var(--g);background:#F0FDF9;padding:2px 7px;border-radius:20px;">{{ $tag }}</span>
              @endforeach
            </div>
          @endif
          <div style="display:flex;align-items:center;gap:12px;font-size:11px;color:var(--gr);">
            <span>{{ $post->created_at->diffForHumans() }}</span>
            <span>❤️ {{ $post->likes }}</span>
            <span>💬 {{ $post->comments }}</span>
            <span style="margin-left:auto;">
              <span style="font-size:10px;background:var(--lgr);padding:2px 7px;border-radius:8px;">
                {{ $post->audience === 'all' ? 'All parents' : str_replace('school:','',$post->audience) }}
              </span>
            </span>
            <form method="POST" action="{{ route('admin.community.boost', $post) }}" style="display:inline">
              @csrf
              <button type="submit" class="btn btn-out btn-sm">Boost</button>
            </form>
            <form method="POST" action="{{ route('admin.community.destroy', $post) }}" style="display:inline" onsubmit="return confirm('Remove this post?')">
              @csrf @method('DELETE')
              <button type="submit" class="btn btn-sm" style="background:#FEF2F2;color:var(--r);">Remove</button>
            </form>
          </div>
        </div>
      @empty
        <div style="text-align:center;padding:32px;color:var(--gr);">No posts yet. Create the first one!</div>
      @endforelse

      @if($posts->hasPages())
        <div style="padding-top:14px;">{{ $posts->links() }}</div>
      @endif
    </div>
  </div>
</div>
@endsection
