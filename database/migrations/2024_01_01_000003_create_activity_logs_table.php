<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('doctor_session_id')->nullable()->constrained('doctor_sessions')->onDelete('set null');
            $table->string('role'); // admin | doctor | parent
            $table->string('action'); // login, logout, create_checkup, update_checkup, view_student, etc.
            $table->string('model_type')->nullable(); // App\Models\Checkup
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['doctor_session_id', 'created_at']);
            $table->index('action');
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
