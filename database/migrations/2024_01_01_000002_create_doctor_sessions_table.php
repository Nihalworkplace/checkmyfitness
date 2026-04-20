<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Admin who created
            $table->foreignId('parent_session_id')->nullable()->constrained('doctor_sessions')->onDelete('set null'); // For reopened sessions
            $table->string('session_code')->unique(); // e.g. SESS-DPS-20260329-A7X2
            $table->string('school_name');
            $table->string('school_city')->nullable();
            $table->text('classes_assigned')->nullable(); // JSON: ["6A","6B","7A"]
            $table->date('visit_date');
            $table->timestamp('expires_at');
            $table->timestamp('activated_at')->nullable(); // When doctor first logged in
            $table->timestamp('last_activity_at')->nullable();
            $table->enum('status', ['pending', 'active', 'expired', 'revoked', 'completed'])->default('pending');
            $table->boolean('is_reopened')->default(false);
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['doctor_id', 'status']);
            $table->index('session_code');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_sessions');
    }
};
