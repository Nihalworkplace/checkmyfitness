<?php

namespace App\Imports;

use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentsImport implements ToCollection, WithHeadingRow
{
    public School $school;
    public array  $results = ['created' => [], 'skipped' => [], 'errors' => []];

    public function __construct(School $school)
    {
        $this->school = $school;
    }

    public function collection(Collection $rows)
    {
        $row_num = 1; // header is row 1, data starts at 2

        foreach ($rows as $row) {
            $row_num++;

            $studentName  = trim($row['student_name'] ?? '');
            $parentEmail  = strtolower(trim($row['parent_email'] ?? ''));
            $classSection = strtoupper(str_replace('-', '', trim($row['class_section'] ?? '')));

            // ── Required fields ───────────────────────────────────
            if (!$studentName || !$parentEmail || !$classSection) {
                $this->results['errors'][] = "Row {$row_num}: student_name, class_section and parent_email are required.";
                continue;
            }

            if (!filter_var($parentEmail, FILTER_VALIDATE_EMAIL)) {
                $this->results['errors'][] = "Row {$row_num}: Invalid email address '{$parentEmail}'.";
                continue;
            }

            // ── Gender ────────────────────────────────────────────
            $genderRaw = strtoupper(trim($row['gender'] ?? ''));
            $gender    = match(true) {
                in_array($genderRaw, ['M', 'MALE'])   => 'M',
                in_array($genderRaw, ['F', 'FEMALE']) => 'F',
                default                               => 'Other',
            };

            // ── Date of birth ─────────────────────────────────────
            $dobRaw = trim($row['date_of_birth'] ?? '');
            $dob    = date_create($dobRaw);
            if (!$dob || $dob >= new \DateTime()) {
                $this->results['errors'][] = "Row {$row_num}: Invalid date_of_birth '{$dobRaw}' — use YYYY-MM-DD.";
                continue;
            }

            // ── Find or create parent by email ────────────────────
            $tempPassword = null;
            $parent       = User::where('email', $parentEmail)->first();

            if (!$parent) {
                $tempPassword = config('app.parent_default_password');
                $parent = User::create([
                    'name'      => trim($row['parent_name'] ?? '') ?: $parentEmail,
                    'email'     => $parentEmail,
                    'password'  => Hash::make($tempPassword),
                    'phone'     => trim($row['parent_phone'] ?? '') ?: null,
                    'is_active' => true,
                ]);
                $parent->assignRole('parent');
            }

            // ── Skip duplicate student ────────────────────────────
            if (Student::where('name', $studentName)
                ->where('class_section', $classSection)
                ->where('school_name', $this->school->name)
                ->exists()) {
                $this->results['skipped'][] = [
                    'student' => $studentName,
                    'class'   => $classSection,
                    'reason'  => 'Already exists in this school & class',
                ];
                continue;
            }

            // ── Create student ────────────────────────────────────
            $student = Student::create([
                'parent_id'        => $parent->id,
                'name'             => $studentName,
                'gender'           => $gender,
                'date_of_birth'    => $dob->format('Y-m-d'),
                'class_section'    => $classSection,
                'school_name'      => $this->school->name,
                'school_city'      => $this->school->city,
                'blood_group'      => trim($row['blood_group'] ?? '') ?: null,
                'known_conditions' => trim($row['known_conditions'] ?? '') ?: null,
                'reference_code'   => Student::generateReferenceCode($classSection),
                'is_active'        => true,
            ]);

            $this->results['created'][] = [
                'student'       => $student->name,
                'class'         => $student->class_section,
                'ref'           => $student->reference_code,
                'parent_name'   => $parent->name,
                'parent_email'  => $parent->email,
                'temp_password' => $tempPassword,
            ];
        }
    }
}
