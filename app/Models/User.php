<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
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
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────────────────────
    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'admin_id');
    }

    public function guardians()
    {
        return $this->hasMany(Guardian::class, 'admin_id');
    }

    public function schools()
    {
        return $this->hasMany(School::class, 'admin_id');
    }

    public function createdSessions()
    {
        return $this->hasMany(DoctorSession::class, 'created_by');
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'actor');
    }

    // ── Helpers ────────────────────────────────────────────
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
}
