<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $schools = School::when($request->search, fn($q, $s) =>
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('city', 'like', "%{$s}%")
            )
            ->when($request->board, fn($q) => $q->where('board', $request->board))
            ->when($request->status !== null, fn($q) =>
                $q->where('is_active', $request->status === 'active')
            )
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.schools.index', compact('schools'));
    }

    public function create()
    {
        return view('admin.schools.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255|unique:schools,name',
            'city'            => 'required|string|max:100',
            'board'           => 'required|in:CBSE,ICSE,GSEB,IB,IGCSE,State,Other',
            'contact_person'  => 'nullable|string|max:150',
            'contact_phone'   => 'nullable|string|max:20',
            'notes'           => 'nullable|string|max:1000',
        ]);

        $school = School::create($data + ['is_active' => true]);

        return redirect()->route('admin.schools.index')
                         ->with('success', "School \"{$school->name}\" added successfully.");
    }

    public function show(School $school)
    {
        $school->load([]);
        $sessions = \App\Models\DoctorSession::with('doctor')
            ->where('school_name', $school->name)
            ->latest('visit_date')
            ->take(10)
            ->get();

        return view('admin.schools.show', compact('school', 'sessions'));
    }

    public function edit(School $school)
    {
        return view('admin.schools.edit', compact('school'));
    }

    public function update(Request $request, School $school)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255|unique:schools,name,'.$school->id,
            'city'            => 'required|string|max:100',
            'board'           => 'required|in:CBSE,ICSE,GSEB,IB,IGCSE,State,Other',
            'contact_person'  => 'nullable|string|max:150',
            'contact_phone'   => 'nullable|string|max:20',
            'notes'           => 'nullable|string|max:1000',
        ]);

        $school->update($data);

        return redirect()->route('admin.schools.show', $school)
                         ->with('success', 'School updated successfully.');
    }

    public function toggle(School $school)
    {
        $school->update(['is_active' => ! $school->is_active]);
        $status = $school->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "School {$status} successfully.");
    }
}
