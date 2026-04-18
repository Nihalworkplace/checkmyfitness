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

    // Doctor specialist types with their labels
    public const DOCTOR_TYPES = [
        'general_physician' => 'General Physician / MBBS',
        'dentist'           => 'Dentist',
        'eye_specialist'    => 'Eye Specialist / Optometrist',
        'audiologist_ent'   => 'Audiologist / ENT',
        'physiotherapist'   => 'Physiotherapist',
        'psychologist'      => 'Psychologist / Counselor',
        'lab_technician'    => 'Lab Technician / Phlebotomist',
    ];

    // Which checkup parameter groups each doctor type can access
    public const DOCTOR_TYPE_SECTIONS = [
        'general_physician' => ['physical', 'skin'],
        'dentist'           => ['dental'],
        'eye_specialist'    => ['eye'],
        'audiologist_ent'   => ['hearing'],
        'physiotherapist'   => ['musculoskeletal'],
        'psychologist'      => ['mental'],
        'lab_technician'    => ['lab'],
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'staff_code',
        'license_number',
        'doctor_type',
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
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────────────────────
    public function doctorSessions()
    {
        return $this->hasMany(DoctorSession::class, 'doctor_id');
    }

    public function createdSessions()
    {
        return $this->hasMany(DoctorSession::class, 'created_by');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'parent_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // ── Helpers ────────────────────────────────────────────
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isDoctor(): bool
    {
        return $this->hasRole('doctor');
    }

    public function isParent(): bool
    {
        return $this->hasRole('parent');
    }

    public function activeSession(): ?DoctorSession
    {
        return $this->doctorSessions()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    public function getActiveSessionAttribute(): ?DoctorSession
    {
        return $this->activeSession();
    }

    public function getDashboardRoute(): string
    {
        return match (true) {
            $this->isAdmin()  => 'admin.dashboard',
            $this->isDoctor() => 'doctor.session.active',
            $this->isParent() => 'parent.dashboard',
            default           => 'login',
        };
    }
}
