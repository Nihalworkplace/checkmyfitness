<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Guardian extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $table = 'parents';

    protected $fillable = [
        'admin_id',
        'name',
        'email',
        'password',
        'reference_code',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'  => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────────────────────
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'parent_id');
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'actor');
    }
}
