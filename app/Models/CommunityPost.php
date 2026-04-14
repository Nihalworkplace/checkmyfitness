<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
