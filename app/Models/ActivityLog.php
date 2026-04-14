<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'doctor_session_id',
        'role',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'method',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function doctorSession(): BelongsTo
    {
        return $this->belongsTo(DoctorSession::class, 'doctor_session_id');
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'login'           => '🔐 Logged In',
            'logout'          => '🚪 Logged Out',
            'view_student'    => '👁️ Viewed Student',
            'create_checkup'  => '✅ Created Checkup',
            'update_checkup'  => '✏️ Updated Checkup',
            'delete_checkup'  => '🗑️ Deleted Checkup',
            'complete_checkup'=> '✓ Marked Complete',
            'session_expired' => '⏰ Session Expired',
            'view_dashboard'  => '📊 Viewed Dashboard',
            default           => ucwords(str_replace('_', ' ', $this->action)),
        };
    }
}
