<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('doctor_type')->nullable()->after('license_number')
                  ->comment('Specialist type: general_physician, dentist, eye_specialist, audiologist_ent, physiotherapist, psychologist, lab_technician');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('doctor_type');
        });
    }
};
