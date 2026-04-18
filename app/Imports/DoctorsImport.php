<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DoctorsImport implements ToCollection, WithHeadingRow
{
    public array $results = ['created' => [], 'skipped' => [], 'errors' => []];

    public function collection(Collection $rows)
    {
        $row_num = 1;

        foreach ($rows as $row) {
            $row_num++;

            $name          = trim($row['name'] ?? '');
            $staffCode     = strtoupper(trim($row['staff_code'] ?? ''));
            $licenseNumber = strtoupper(trim($row['license_number'] ?? ''));
            $doctorType    = strtolower(trim($row['doctor_type'] ?? ''));

            // ── Required fields ───────────────────────────────────
            $validTypes = array_keys(\App\Models\User::DOCTOR_TYPES);

            if (!$name || !$staffCode || !$licenseNumber || !$doctorType) {
                $this->results['errors'][] = "Row {$row_num}: name, staff_code, license_number and doctor_type are required.";
                continue;
            }

            // ── Validate doctor_type ──────────────────────────────
            if (!in_array($doctorType, $validTypes)) {
                $this->results['errors'][] = "Row {$row_num}: Invalid doctor_type '{$doctorType}'. Valid values: " . implode(', ', $validTypes);
                continue;
            }

            // ── Duplicate staff code check ────────────────────────
            if (User::where('staff_code', $staffCode)->exists()) {
                $this->results['skipped'][] = [
                    'name'           => $name,
                    'staff_code'     => $staffCode,
                    'license_number' => $licenseNumber,
                    'reason'         => 'Staff code already exists',
                ];
                continue;
            }

            // ── Duplicate license number check ────────────────────
            if (User::where('license_number', $licenseNumber)->exists()) {
                $this->results['skipped'][] = [
                    'name'           => $name,
                    'staff_code'     => $staffCode,
                    'license_number' => $licenseNumber,
                    'reason'         => 'License number already exists',
                ];
                continue;
            }

            $doctor = User::create([
                'name'           => $name,
                'staff_code'     => $staffCode,
                'license_number' => $licenseNumber,
                'doctor_type'    => $doctorType,
                'phone'          => trim($row['phone'] ?? '') ?: null,
                'is_active'      => true,
            ]);
            $doctor->assignRole('doctor');

            $this->results['created'][] = [
                'name'           => $doctor->name,
                'staff_code'     => $doctor->staff_code,
                'license_number' => $doctor->license_number,
                'doctor_type'    => $doctor->doctor_type
                                      ? (\App\Models\User::DOCTOR_TYPES[$doctor->doctor_type] ?? $doctor->doctor_type)
                                      : '—',
                'phone'          => $doctor->phone ?? '—',
                'school'         => $doctor->school_name ?? '—',
            ];
        }
    }
}
