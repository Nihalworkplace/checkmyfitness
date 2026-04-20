<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Doctor extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $table = 'doctors';

    public const DOCTOR_TYPES = [
        'general_physician' => 'General Physician / MBBS',
        'dentist'           => 'Dentist',
        'eye_specialist'    => 'Eye Specialist / Optometrist',
        'audiologist_ent'   => 'Audiologist / ENT',
        'physiotherapist'   => 'Physiotherapist',
        'psychologist'      => 'Psychologist / Counselor',
        'lab_technician'    => 'Lab Technician / Phlebotomist',
    ];

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
        'admin_id',
        'name',
        'email',
        'password',
        'staff_code',
        'license_number',
        'doctor_type',
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

    public function doctorSessions()
    {
        return $this->hasMany(DoctorSession::class, 'doctor_id');
    }

    public function checkups()
    {
        return $this->hasMany(Checkup::class, 'doctor_id');
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'actor');
    }

    // ── Helpers ────────────────────────────────────────────
    public function activeSession(): ?DoctorSession
    {
        return $this->doctorSessions()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }
}
