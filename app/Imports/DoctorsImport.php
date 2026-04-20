<?php

namespace App\Imports;

use App\Models\Doctor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DoctorsImport implements ToCollection, WithHeadingRow
{
    public array $results = ['created' => [], 'skipped' => [], 'errors' => []];

    public function collection(Collection $rows)
    {
        $row_num  = 1;
        $adminId  = Auth::id();
        $validTypes = array_keys(Doctor::DOCTOR_TYPES);

        foreach ($rows as $row) {
            $row_num++;

            $name          = trim($row['name'] ?? '');
            $staffCode     = strtoupper(trim($row['staff_code'] ?? ''));
            $licenseNumber = strtoupper(trim($row['license_number'] ?? ''));
            $doctorType    = strtolower(trim($row['doctor_type'] ?? ''));

            if (!$name || !$staffCode || !$licenseNumber || !$doctorType) {
                $this->results['errors'][] = "Row {$row_num}: name, staff_code, license_number and doctor_type are required.";
                continue;
            }

            if (!in_array($doctorType, $validTypes)) {
                $this->results['errors'][] = "Row {$row_num}: Invalid doctor_type '{$doctorType}'. Valid values: " . implode(', ', $validTypes);
                continue;
            }

            if (Doctor::where('staff_code', $staffCode)->exists()) {
                $this->results['skipped'][] = [
                    'name'           => $name,
                    'staff_code'     => $staffCode,
                    'license_number' => $licenseNumber,
                    'reason'         => 'Staff code already exists',
                ];
                continue;
            }

            if (Doctor::where('license_number', $licenseNumber)->exists()) {
                $this->results['skipped'][] = [
                    'name'           => $name,
                    'staff_code'     => $staffCode,
                    'license_number' => $licenseNumber,
                    'reason'         => 'License number already exists',
                ];
                continue;
            }

            $phone = preg_replace('/\D/', '', trim($row['phone'] ?? ''));
            if ($phone !== '' && strlen($phone) !== 10) {
                $this->results['errors'][] = "Row {$row_num}: phone must be exactly 10 digits if provided.";
                continue;
            }

            $doctor = Doctor::create([
                'admin_id'       => $adminId,
                'name'           => $name,
                'staff_code'     => $staffCode,
                'license_number' => $licenseNumber,
                'doctor_type'    => $doctorType,
                'phone'          => $phone ?: null,
                'is_active'      => true,
            ]);

            $this->results['created'][] = [
                'name'           => $doctor->name,
                'staff_code'     => $doctor->staff_code,
                'license_number' => $doctor->license_number,
                'doctor_type'    => Doctor::DOCTOR_TYPES[$doctor->doctor_type] ?? $doctor->doctor_type,
                'phone'          => $doctor->phone ?? '—',
                'school'         => '—',
            ];
        }
    }
}
