<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Checkup;
use App\Models\Student;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $parent   = auth()->user();
        $students = $parent->students()->with([
            'checkups' => fn($q) => $q->with('doctor')->latest('checkup_date'),
        ])->get();

        // Default to first student if only one
        $student = $students->first();

        return view('parent.dashboard', compact('students', 'student'));
    }

    public function report(Student $student)
    {
        $this->authorizeStudent($student);

        $allCheckups = $student->checkups()
            ->with('doctor')
            ->completed()
            ->latest('checkup_date')
            ->get();

        return view('parent.report', compact('student', 'allCheckups'));
    }

    public function timeline(Student $student)
    {
        $this->authorizeStudent($student);

        $checkups = $student->checkups()->completed()->latest('checkup_date')->get();

        return view('parent.timeline', compact('student', 'checkups'));
    }

    public function rewards(Student $student)
    {
        $this->authorizeStudent($student);

        return view('parent.rewards', compact('student'));
    }

    private function authorizeStudent(Student $student): void
    {
        if ($student->parent_id !== auth()->id()) {
            abort(403, 'You are not authorised to view this student\'s records.');
        }
    }
}
