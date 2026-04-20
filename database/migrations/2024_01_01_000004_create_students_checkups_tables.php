<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Students
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('parent_id')->constrained('parents')->onDelete('cascade');
            $table->string('reference_code')->unique(); // CMF-2024-06B-042
            $table->string('name');
            $table->enum('gender', ['M', 'F', 'Other']);
            $table->date('date_of_birth');
            $table->string('class_section'); // 6B
            $table->string('school_name');
            $table->string('school_city')->nullable();
            $table->string('blood_group', 5)->nullable();
            $table->text('known_conditions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Checkups
        Schema::create('checkups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->foreignId('doctor_session_id')->constrained('doctor_sessions')->onDelete('cascade');
            $table->date('checkup_date');
            $table->enum('status', ['draft', 'completed'])->default('draft');

            // Physical & Vitals
            $table->decimal('height_cm', 5, 1)->nullable();
            $table->decimal('weight_kg', 5, 1)->nullable();
            $table->decimal('bmi', 4, 1)->nullable();
            $table->unsignedSmallInteger('heart_rate_bpm')->nullable();
            $table->string('bp_systolic', 5)->nullable();
            $table->string('bp_diastolic', 5)->nullable();
            $table->decimal('temperature_f', 4, 1)->nullable();
            $table->unsignedTinyInteger('spo2_percent')->nullable();

            // Sensory
            $table->string('vision_left', 10)->nullable();  // e.g. "20/20"
            $table->string('vision_right', 10)->nullable();
            $table->enum('hearing', ['Normal', 'Mild Issue', 'Needs Test'])->nullable();
            $table->unsignedTinyInteger('dental_score')->nullable(); // 1-10
            $table->enum('eye_strain', ['None', 'Mild', 'Severe'])->nullable();

            // Lab & Biochemical
            $table->decimal('haemoglobin_gdl', 4, 1)->nullable();
            $table->decimal('vitamin_d_ngml', 4, 1)->nullable();
            $table->enum('iron_level', ['Normal', 'Low', 'Very Low'])->nullable();
            $table->decimal('blood_sugar_mgdl', 5, 1)->nullable();

            // Musculoskeletal
            $table->enum('posture', ['Good', 'Mild Curve', 'Scoliosis Risk'])->nullable();
            $table->unsignedTinyInteger('grip_strength_score')->nullable(); // 1-10
            $table->enum('flexibility', ['Good', 'Average', 'Poor'])->nullable();
            $table->enum('flat_feet', ['None', 'Mild', 'Moderate'])->nullable();

            // Wellness & Mental
            $table->unsignedTinyInteger('mental_score')->nullable(); // 1-10
            $table->enum('stress_level', ['Low', 'Moderate', 'High'])->nullable();
            $table->enum('sleep_quality', ['Good', 'Average', 'Poor'])->nullable();

            // Skin & Hair
            $table->enum('skin_health', ['Healthy', 'Mild Issue', 'Needs Attention'])->nullable();
            $table->enum('hair_health', ['Healthy', 'Mild Issue', 'Needs Attention'])->nullable();

            // Scores & Alerts
            $table->unsignedTinyInteger('overall_score')->nullable(); // 0-100
            $table->json('alerts')->nullable(); // ["Low Haemoglobin", ...]
            $table->text('doctor_notes')->nullable();
            $table->text('recommendations')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'checkup_date']);
            $table->index(['doctor_session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkups');
        Schema::dropIfExists('students');
    }
};
