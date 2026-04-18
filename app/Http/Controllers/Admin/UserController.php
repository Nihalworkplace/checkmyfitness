<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    // ── Doctors ───────────────────────────────────────────────
    public function doctors(Request $request)
    {
        $doctors = User::role('doctor')
            ->withCount('doctorSessions')
            ->when($request->search, fn($q, $s) =>
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('staff_code', 'like', "%{$s}%")
            )
            ->when($request->status, fn($q) =>
                $q->where('is_active', $request->status === 'active')
            )
            ->latest()->paginate(20);

        return view('admin.users.doctors', compact('doctors'));
    }

    public function createDoctor()
    {
        return view('admin.users.create-doctor');
    }

    public function storeDoctor(Request $request)
    {
        $validTypes = implode(',', array_keys(User::DOCTOR_TYPES));

        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'staff_code'     => 'required|string|unique:users,staff_code|max:50',
            'license_number' => 'required|string|unique:users,license_number|max:100',
            'doctor_type'    => "required|in:{$validTypes}",
            'phone'          => 'nullable|string|max:20',
        ]);

        $doctor = User::create([
            'name'           => $data['name'],
            'staff_code'     => strtoupper($data['staff_code']),
            'license_number' => strtoupper($data['license_number']),
            'doctor_type'    => $data['doctor_type'],
            'phone'          => $data['phone'] ?? null,
            'is_active'      => true,
        ]);

        $doctor->assignRole('doctor');

        return redirect()->route('admin.doctors')
                         ->with('success', "Doctor {$doctor->name} created with Staff Code: {$doctor->staff_code}");
    }

    public function toggleDoctorStatus(User $doctor)
    {
        $doctor->update(['is_active' => ! $doctor->is_active]);
        $status = $doctor->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Dr. {$doctor->name} has been {$status}.");
    }

    // ── Parents ───────────────────────────────────────────────
    public function parents(Request $request)
    {
        $parents = User::role('parent')
            ->withCount('students')
            ->when($request->search, fn($q, $s) =>
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
            )
            ->latest()->paginate(20);

        return view('admin.users.parents', compact('parents'));
    }

    public function createParent()
    {
        return view('admin.users.create-parent');
    }

    public function storeParent(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', Password::min(8)],
            'phone'    => 'nullable|string|max:20',
        ]);

        $parent = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'],
            'phone'    => $data['phone'] ?? null,
            'is_active'=> true,
        ]);

        $parent->assignRole('parent');

        return redirect()->route('admin.parents')
                         ->with('success', "Parent {$parent->name} created successfully.");
    }

    // ── Students ──────────────────────────────────────────────
    public function students(Request $request)
    {
        $students = Student::with('parent')
            ->when($request->search, fn($q, $s) =>
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('reference_code', 'like', "%{$s}%")
                  ->orWhere('school_name', 'like', "%{$s}%")
            )
            ->when($request->school, fn($q) =>
                $q->where('school_name', $request->school)
            )
            ->latest()->paginate(30);

        return view('admin.users.students', compact('students'));
    }

    public function createStudent()
    {
        $parents = User::role('parent')->where('is_active', true)->orderBy('name')->get();
        $schools = School::where('is_active', true)->orderBy('name')->get();
        return view('admin.users.create-student', compact('parents', 'schools'));
    }

    public function storeStudent(Request $request)
    {
        $data = $request->validate([
            'parent_id'     => 'required|exists:users,id',
            'name'          => 'required|string|max:255',
            'gender'        => 'required|in:M,F,Other',
            'date_of_birth' => 'required|date|before:today',
            'class_section' => 'required|string|max:10',
            'school_id'     => 'required|exists:schools,id',
            'blood_group'   => 'nullable|string|max:5',
            'known_conditions' => 'nullable|string|max:500',
        ]);

        $school = School::findOrFail($data['school_id']);
        unset($data['school_id']);

        $student = Student::create([
            ...$data,
            'school_name'    => $school->name,
            'school_city'    => $school->city,
            'reference_code' => Student::generateReferenceCode($data['class_section']),
            'is_active'      => true,
        ]);

        return redirect()->route('admin.students')
                         ->with('success', "Student {$student->name} created. Reference Code: {$student->reference_code}");
    }

    public function showStudent(Student $student)
    {
        $student->load(['parent', 'checkups' => fn($q) => $q->latest('checkup_date')]);
        return view('admin.users.show-student', compact('student'));
    }

    public function showParent(User $parent)
    {
        $parent->load(['students.checkups' => fn($q) => $q->latest('checkup_date')->limit(1)]);
        return view('admin.users.show-parent', compact('parent'));
    }
}
