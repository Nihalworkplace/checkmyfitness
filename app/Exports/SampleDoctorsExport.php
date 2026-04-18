<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SampleDoctorsExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function headings(): array
    {
        return ['name', 'staff_code', 'license_number', 'doctor_type', 'phone'];
    }

    public function array(): array
    {
        // doctor_type valid values: general_physician, dentist, eye_specialist,
        // audiologist_ent, physiotherapist, psychologist, lab_technician
        return [
            ['Dr. Priya Nair',  'CMF-DOC-001', 'MCI-GUJ-2018-10234', 'general_physician', '9876543210'],
            ['Dr. Amit Shah',   'CMF-DOC-002', 'MCI-GUJ-2020-20511', 'dentist',           '9876543211'],
            ['Dr. Rekha Joshi', 'CMF-DOC-003', 'MCI-RJ-2017-30892',  'lab_technician',    '9876543212'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF3B82F6']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, // name
            'B' => 18, // staff_code
            'C' => 24, // license_number
            'D' => 22, // doctor_type
            'E' => 16, // phone
        ];
    }
}
