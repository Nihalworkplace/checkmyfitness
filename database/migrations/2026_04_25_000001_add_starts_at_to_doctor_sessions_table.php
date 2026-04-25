<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_sessions', function (Blueprint $table) {
            // When the session becomes valid for doctor login (stored UTC).
            // Null means "valid immediately from creation" (backward compat).
            $table->timestamp('starts_at')->nullable()->after('visit_date');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_sessions', function (Blueprint $table) {
            $table->dropColumn('starts_at');
        });
    }
};
