<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoctorSession extends Model
{
    protected $fillable = [
        'doctor_id',
        'created_by',
        'parent_session_id',
        'session_code',
        'school_name',
        'school_city',
        'classes_assigned',
        'starts_at',
        'expires_at',
        'activated_at',
        'last_activity_at',
        'status',
        'is_reopened',
        'admin_notes',
    ];

    protected $casts = [
        'starts_at'         => 'datetime',
        'expires_at'        => 'datetime',
        'activated_at'      => 'datetime',
        'last_activity_at'  => 'datetime',
        'classes_assigned'  => 'array',
        'is_reopened'       => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parentSession(): BelongsTo
    {
        return $this->belongsTo(DoctorSession::class, 'parent_session_id');
    }

    public function childSessions(): HasMany
    {
        return $this->hasMany(DoctorSession::class, 'parent_session_id');
    }

    public function checkups(): HasMany
    {
        return $this->hasMany(Checkup::class, 'doctor_session_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'doctor_session_id');
    }

    // ── Scopes ─────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now())
                     ->whereIn('status', ['pending', 'active']);
    }

    // ── Helpers ────────────────────────────────────────────
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ! $this->isExpired();
    }

    public function isRevoked(): bool
    {
        return $this->status === 'revoked';
    }

    public function isStarted(): bool
    {
        return ! $this->starts_at || $this->starts_at->isPast();
    }

    public function canBeUsed(): bool
    {
        return in_array($this->status, ['pending', 'active'])
            && ! $this->isExpired()
            && $this->isStarted();
    }

    public function markActivated(): void
    {
        $this->update([
            'status'       => 'active',
            'activated_at' => now(),
            'last_activity_at' => now(),
        ]);
    }

    public function touchActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    public function revoke(): void
    {
        $this->update(['status' => 'revoked']);
    }

    public function complete(): void
    {
        $this->update(['status' => 'completed']);
    }

    public function getStatusBadgeAttribute(): string
    {
        // Auto-mark expired
        if ($this->isExpired() && in_array($this->status, ['pending', 'active'])) {
            return 'expired';
        }
        return $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status_badge) {
            'active'    => 'green',
            'pending'   => 'blue',
            'expired'   => 'gray',
            'revoked'   => 'red',
            'completed' => 'purple',
            default     => 'gray',
        };
    }

    public function getDurationHoursAttribute(): float
    {
        if ($this->activated_at) {
            return round($this->activated_at->diffInMinutes(now()) / 60, 1);
        }
        return 0;
    }
}
