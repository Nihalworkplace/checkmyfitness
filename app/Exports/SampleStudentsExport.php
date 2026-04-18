<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SampleStudentsExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function headings(): array
    {
        return [
            'student_name',
            'gender',
            'date_of_birth',
            'class_section',
            'blood_group',
            'known_conditions',
            'parent_name',
            'parent_email',
            'parent_phone',
        ];
    }

    public function array(): array
    {
        return [
            ['Rahul Sharma',  'M', '2012-05-15', '5A', 'A+', '',       'Rajesh Sharma',   'rajesh.sharma@gmail.com',  '9876543210'],
            ['Priya Patel',   'F', '2013-08-22', '4B', 'B+', '',       'Meena Patel',     'meena.patel@gmail.com',    '9876543211'],
            ['Arjun Singh',   'M', '2011-03-10', '6A', 'O+', 'Asthma', 'Harpreet Singh',  'harpreet.singh@gmail.com', '9876543212'],
            ['Sneha Mehta',   'F', '2014-11-30', '3C', 'AB+','',       'Pooja Mehta',     'pooja.mehta@gmail.com',    '9876543213'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Bold + colored header row
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1D9E75']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 22, // student_name
            'B' => 10, // gender
            'C' => 16, // date_of_birth
            'D' => 14, // class_section
            'E' => 12, // blood_group
            'F' => 20, // known_conditions
            'G' => 22, // parent_name
            'H' => 30, // parent_email
            'I' => 16, // parent_phone
        ];
    }
}
