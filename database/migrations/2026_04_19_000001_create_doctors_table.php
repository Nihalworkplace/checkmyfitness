<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('staff_code')->unique();           // e.g. CMF-DOC-0021
            $table->string('license_number')->unique()->nullable();
            $table->string('doctor_type')->nullable();        // general_physician, dentist, etc.
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('staff_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
