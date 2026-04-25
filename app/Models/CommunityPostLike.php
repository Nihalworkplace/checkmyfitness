<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityPostLike extends Model
{
    protected $table = 'community_post_likes';

    protected $fillable = ['post_id', 'parent_id'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class, 'post_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'parent_id');
    }
}
