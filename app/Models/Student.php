<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'reference_code',
        'name',
        'gender',
        'date_of_birth',
        'class_section',
        'school_name',
        'school_city',
        'blood_group',
        'known_conditions',
        'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_active'     => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function checkups(): HasMany
    {
        return $this->hasMany(Checkup::class)->orderBy('checkup_date', 'desc');
    }

    public function latestCheckup(): ?Checkup
    {
        return $this->checkups()->completed()->latest('checkup_date')->first();
    }

    public function getAgeAttribute(): int
    {
        return $this->date_of_birth->age;
    }

    public static function generateReferenceCode(string $classSection, int $year = null): string
    {
        $year   = $year ?? date('Y');
        $class  = strtoupper(str_replace([' ', '/'], '', $classSection));
        $seq    = str_pad(Student::count() + 1, 3, '0', STR_PAD_LEFT);
        return "CMF-{$year}-{$class}-{$seq}";
    }
}
