<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityComment extends Model
{
    protected $fillable = ['post_id', 'parent_id', 'body'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class, 'post_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'parent_id');
    }
}
