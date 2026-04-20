<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorSession;
use App\Models\Guardian;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Roles ──────────────────────────────────────────────
        Role::firstOrCreate(['name' => 'admin',  'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'doctor']);
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'parent']);

        // ── Admin ──────────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@checkmy.fitness'],
            [
                'name'      => 'CMF Admin',
                'password'  => Hash::make('Admin@2026'),
                'is_active' => true,
            ]
        );
        $admin->syncRoles(['admin']);

        // ── Schools ────────────────────────────────────────────
        $school1 = School::firstOrCreate(
            ['name' => 'DPS Vadodara'],
            [
                'admin_id'       => $admin->id,
                'city'           => 'Vadodara',
                'board'          => 'CBSE',
                'contact_person' => 'Principal Mrs. Sharma',
                'contact_phone'  => '2651234567',
                'is_active'      => true,
            ]
        );

        School::firstOrCreate(
            ['name' => 'Ryan International'],
            ['admin_id' => $admin->id, 'city' => 'Surat', 'board' => 'CBSE', 'is_active' => true]
        );

        School::firstOrCreate(
            ['name' => 'Billabong High School'],
            ['admin_id' => $admin->id, 'city' => 'Vadodara', 'board' => 'CBSE', 'is_active' => true]
        );

        // ── Doctors ────────────────────────────────────────────
        $doctor1 = Doctor::firstOrCreate(
            ['staff_code' => 'CMF-DOC-0021'],
            [
                'admin_id'       => $admin->id,
                'name'           => 'Dr. Priya Kapoor',
                'doctor_type'    => 'general_physician',
                'phone'          => '9876543210',
                'is_active'      => true,
            ]
        );
        $doctor1->syncRoles(['doctor']);

        $doctor2 = Doctor::firstOrCreate(
            ['staff_code' => 'CMF-DOC-0022'],
            [
                'admin_id'    => $admin->id,
                'name'        => 'Dr. Raj Mehta',
                'doctor_type' => 'general_physician',
                'phone'       => '8765432109',
                'is_active'   => true,
            ]
        );
        $doctor2->syncRoles(['doctor']);

        // ── Parents ────────────────────────────────────────────
        $parent1 = Guardian::firstOrCreate(
            ['email' => 'rajesh.shah@example.com'],
            [
                'admin_id'  => $admin->id,
                'name'      => 'Rajesh Shah',
                'password'  => Hash::make('Parent@2026'),
                'phone'     => '9988776655',
                'is_active' => true,
            ]
        );
        $parent1->syncRoles(['parent']);

        $parent2 = Guardian::firstOrCreate(
            ['email' => 'meena.patel@example.com'],
            [
                'admin_id'  => $admin->id,
                'name'      => 'Meena Patel',
                'password'  => Hash::make('Parent@2026'),
                'phone'     => '8877665544',
                'is_active' => true,
            ]
        );
        $parent2->syncRoles(['parent']);

        // ── Students ───────────────────────────────────────────
        $student1 = Student::firstOrCreate(
            ['reference_code' => 'CMF-2024-06B-042'],
            [
                'admin_id'      => $admin->id,
                'parent_id'     => $parent1->id,
                'name'          => 'Aarav Shah',
                'gender'        => 'M',
                'date_of_birth' => '2013-06-15',
                'class_section' => '6B',
                'school_name'   => 'DPS Vadodara',
                'school_city'   => 'Vadodara',
                'blood_group'   => 'B+',
                'is_active'     => true,
            ]
        );

        $student2 = Student::firstOrCreate(
            ['reference_code' => 'CMF-2024-06B-019'],
            [
                'admin_id'      => $admin->id,
                'parent_id'     => $parent2->id,
                'name'          => 'Riya Patel',
                'gender'        => 'F',
                'date_of_birth' => '2013-03-22',
                'class_section' => '6B',
                'school_name'   => 'DPS Vadodara',
                'school_city'   => 'Vadodara',
                'blood_group'   => 'O+',
                'is_active'     => true,
            ]
        );

        // ── Demo Doctor Session ────────────────────────────────
        $demoSession = DoctorSession::firstOrCreate(
            ['session_code' => 'SESS-DPS-DEMO-2026'],
            [
                'doctor_id'        => $doctor1->id,
                'created_by'       => $admin->id,
                'school_name'      => 'DPS Vadodara',
                'school_city'      => 'Vadodara',
                'classes_assigned' => ['6A', '6B', '7A', '7B'],
                'visit_date'       => now()->toDateString(),
                'expires_at'       => now()->addHours(12),
                'status'           => 'pending',
                'admin_notes'      => 'Demo session for testing',
            ]
        );

        // ── Demo Checkup ───────────────────────────────────────
        \App\Models\Checkup::firstOrCreate(
            ['student_id' => $student1->id, 'doctor_session_id' => $demoSession->id],
            [
                'doctor_id'          => $doctor1->id,
                'checkup_date'       => now()->toDateString(),
                'status'             => 'completed',
                'height_cm'          => 148,
                'weight_kg'          => 42,
                'bmi'                => 19.2,
                'heart_rate_bpm'     => 82,
                'bp_systolic'        => '110',
                'bp_diastolic'       => '70',
                'temperature_f'      => 98.4,
                'spo2_percent'       => 98,
                'vision_left'        => '18/20',
                'vision_right'       => '20/20',
                'hearing'            => 'Normal',
                'dental_score'       => 5,
                'eye_strain'         => 'Mild',
                'haemoglobin_gdl'    => 10.2,
                'vitamin_d_ngml'     => 22,
                'iron_level'         => 'Low',
                'blood_sugar_mgdl'   => 88,
                'posture'            => 'Good',
                'grip_strength_score'=> 7,
                'flexibility'        => 'Average',
                'flat_feet'          => 'None',
                'mental_score'       => 7,
                'stress_level'       => 'Moderate',
                'sleep_quality'      => 'Average',
                'skin_health'        => 'Healthy',
                'hair_health'        => 'Healthy',
                'overall_score'      => 74,
                'alerts'             => [
                    'Low Haemoglobin (10.2 g/dL)',
                    'Dental Health Needs Attention (Score: 5/10)',
                    'Vitamin D Deficient (22 ng/mL)',
                ],
                'doctor_notes'       => 'Student appears generally healthy. Low haemoglobin — recommend iron-rich diet. Dental hygiene needs improvement.',
                'recommendations'    => 'Iron-rich diet (spinach, rajma, dates). Dentist visit within 4 weeks. Vitamin D supplement advised.',
            ]
        );

        $this->command->info('✅  Database seeded successfully!');
        $this->command->newLine();
        $this->command->table(
            ['Role', 'Login', 'Password / Code'],
            [
                ['Admin',  'admin@checkmy.fitness',    'Admin@2026'],
                ['Parent', 'rajesh.shah@example.com',  'Parent@2026'],
                ['Parent', 'CMF-2024-06B-042 (code)',  '— (no password needed)'],
                ['Doctor', 'Staff: CMF-DOC-0021',      'Session Code: SESS-DPS-DEMO-2026'],
            ]
        );
    }
}
