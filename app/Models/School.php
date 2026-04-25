<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class School extends Model
{
    protected $fillable = [
        'admin_id',
        'name',
        'city',
        'board',
        'contact_person',
        'contact_phone',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /** All doctor sessions linked to this school */
    public function doctorSessions(): HasMany
    {
        return $this->hasMany(DoctorSession::class, 'school_name', 'name');
    }

    /** Count of students at this school */
    public function getStudentCountAttribute(): int
    {
        return Student::where('school_name', $this->name)->where('is_active', true)->count();
    }

    /** Last session date */
    public function getLastSessionDateAttribute(): ?string
    {
        $session = DoctorSession::where('school_name', $this->name)
            ->whereIn('status', ['completed', 'active'])
            ->latest('starts_at')
            ->first();

        return $session ? ($session->starts_at ?? $session->created_at)->inDisplayTz()->format('M Y') : null;
    }

    /** Count of health alerts from students at this school */
    public function getAlertCountAttribute(): int
    {
        return Checkup::completed()
            ->whereNotNull('alerts')
            ->whereHas('student', fn($q) => $q->where('school_name', $this->name))
            ->get()
            ->flatMap(fn($c) => $c->alerts ?? [])
            ->count();
    }

    /** Avg overall score from latest checkups */
    public function getAvgScoreAttribute(): int
    {
        $scores = Checkup::completed()
            ->whereNotNull('overall_score')
            ->whereHas('student', fn($q) => $q->where('school_name', $this->name))
            ->pluck('overall_score');

        return $scores->count() ? (int) round($scores->avg()) : 0;
    }
}
