<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityPost extends Model
{
    protected $fillable = [
        'created_by',
        'title',
        'content',
        'tags',
        'audience',
        'likes',
        'comments',
        'is_published',
    ];

    protected $casts = [
        'tags'         => 'array',
        'is_published' => 'boolean',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postLikes(): HasMany
    {
        return $this->hasMany(CommunityPostLike::class, 'post_id');
    }

    public function postComments(): HasMany
    {
        return $this->hasMany(CommunityComment::class, 'post_id')->with('author')->latest();
    }

    public function isLikedBy(int $parentId): bool
    {
        return $this->postLikes()->where('parent_id', $parentId)->exists();
    }
}
