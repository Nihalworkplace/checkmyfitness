<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Checkup extends Model
{
    use SoftDeletes;

    protected $table = 'checkups';

    protected $fillable = [
        'student_id',
        'doctor_id',
        'doctor_session_id',
        'checkup_date',
        'status',
        'height_cm',
        'weight_kg',
        'bmi',
        'heart_rate_bpm',
        'bp_systolic',
        'bp_diastolic',
        'temperature_f',
        'spo2_percent',
        'vision_left',
        'vision_right',
        'hearing',
        'dental_score',
        'eye_strain',
        'haemoglobin_gdl',
        'vitamin_d_ngml',
        'iron_level',
        'blood_sugar_mgdl',
        'posture',
        'grip_strength_score',
        'flexibility',
        'flat_feet',
        'mental_score',
        'stress_level',
        'sleep_quality',
        'skin_health',
        'hair_health',
        'overall_score',
        'alerts',
        'doctor_notes',
        'recommendations',
    ];

    protected $casts = [
        'checkup_date' => 'date',
        'status' => 'string',
        'height_cm' => 'float',
        'weight_kg' => 'float',
        'bmi' => 'float',
        'heart_rate_bpm' => 'integer',
        'temperature_f' => 'float',
        'spo2_percent' => 'integer',
        'dental_score' => 'integer',
        'haemoglobin_gdl' => 'float',
        'vitamin_d_ngml' => 'float',
        'blood_sugar_mgdl' => 'float',
        'grip_strength_score' => 'integer',
        'mental_score' => 'integer',
        'overall_score' => 'integer',
        'alerts' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the student associated with this checkup.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Get the doctor who performed this checkup.
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Get the doctor session associated with this checkup.
     */
    public function doctorSession()
    {
        return $this->belongsTo(DoctorSession::class, 'doctor_session_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function calculateOverallScore(): int
    {
        $scores = [];
        if ($this->bmi) {
            $scores[] = ($this->bmi >= 18.5 && $this->bmi <= 24.9) ? 100 : max(0, 100 - abs($this->bmi - 21.7) * 5);
        }
        if ($this->haemoglobin_gdl) {
            $normal = $this->student->gender === 'F' ? [11.5, 16] : [13, 17];
            $scores[] = ($this->haemoglobin_gdl >= $normal[0] && $this->haemoglobin_gdl <= $normal[1]) ? 100
                      : max(0, 100 - abs($this->haemoglobin_gdl - array_sum($normal) / 2) * 10);
        }
        if ($this->dental_score)        $scores[] = $this->dental_score * 10;
        if ($this->mental_score)        $scores[] = $this->mental_score * 10;
        if ($this->grip_strength_score) $scores[] = $this->grip_strength_score * 10;
        if ($this->vitamin_d_ngml) {
            $scores[] = ($this->vitamin_d_ngml >= 30) ? 100 : max(0, ($this->vitamin_d_ngml / 30) * 100);
        }
        return $scores ? (int) round(array_sum($scores) / count($scores)) : 0;
    }

    public function generateAlerts(): array
    {
        $alerts = [];
        if ($this->haemoglobin_gdl) {
            $min = $this->student->gender === 'F' ? 11.5 : 13;
            if ($this->haemoglobin_gdl < $min) {
                $prefix   = $this->haemoglobin_gdl < ($min * 0.7) ? 'CRITICAL: ' : '';
                $alerts[] = "{$prefix}Low Haemoglobin ({$this->haemoglobin_gdl} g/dL)";
            }
        }
        if ($this->vitamin_d_ngml && $this->vitamin_d_ngml < 30) {
            $alerts[] = "Vitamin D Deficient ({$this->vitamin_d_ngml} ng/mL)";
        }
        if ($this->dental_score && $this->dental_score < 6) {
            $alerts[] = "Dental Health Needs Attention (Score: {$this->dental_score}/10)";
        }
        if ($this->mental_score && $this->mental_score < 6) {
            $alerts[] = "Low Mental Well-being (Score: {$this->mental_score}/10)";
        }
        if ($this->bmi && ($this->bmi < 16 || $this->bmi > 30)) {
            $label    = $this->bmi < 16 ? 'Severely Underweight' : 'Obese';
            $alerts[] = "BMI: {$label} ({$this->bmi})";
        }
        if ($this->stress_level === 'High') {
            $alerts[] = "High Stress Level — Counsellor referral suggested";
        }
        if ($this->posture === 'Scoliosis Risk') {
            $alerts[] = "Posture: Scoliosis Risk — Orthopaedic review recommended";
        }
        return $alerts;
    }
}
