<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_sessions', function (Blueprint $table) {
            $table->dropColumn('visit_date');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_sessions', function (Blueprint $table) {
            $table->date('visit_date')->nullable()->after('classes_assigned');
        });
    }
};
