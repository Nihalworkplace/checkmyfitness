<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SampleDoctorsExport;
use App\Exports\SampleStudentsExport;
use App\Http\Controllers\Controller;
use App\Imports\DoctorsImport;
use App\Imports\StudentsImport;
use App\Models\School;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    // ── Students + Parents (school-level) ─────────────────────────

    public function studentsForm(School $school)
    {
        return view('admin.import.students', compact('school'));
    }

    public function studentsImport(Request $request, School $school)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,ods|max:5120',
        ]);

        $import = new StudentsImport($school);
        Excel::import($import, $request->file('import_file'));

        return view('admin.import.students-result', [
            'school'  => $school,
            'results' => $import->results,
        ]);
    }

    // ── Doctors ────────────────────────────────────────────────────

    public function doctorsForm()
    {
        return view('admin.import.doctors');
    }

    public function doctorsImport(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,ods|max:2048',
        ]);

        $import = new DoctorsImport();
        Excel::import($import, $request->file('import_file'));

        return view('admin.import.doctors-result', [
            'results' => $import->results,
        ]);
    }

    // ── Sample Excel downloads ─────────────────────────────────────

    public function sampleStudents()
    {
        return Excel::download(new SampleStudentsExport(), 'students_import_sample.xlsx');
    }

    public function sampleDoctors()
    {
        return Excel::download(new SampleDoctorsExport(), 'doctors_import_sample.xlsx');
    }
}
