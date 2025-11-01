<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('employee_id_number')->unique()->nullable()->after('university_id_no');
            $table->enum('onboarding_status', ['draft', 'for_interview', 'for_approval', 'approved', 'rejected', 'recheck'])
                ->default('draft')
                ->after('admission_status');
        });

        DB::statement("ALTER TABLE tenants MODIFY type ENUM('student','employee') NOT NULL DEFAULT 'student'");
        DB::statement("UPDATE tenants SET onboarding_status = admission_status");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tenants MODIFY type ENUM('student','employee') NOT NULL");
        DB::statement("ALTER TABLE tenants DROP COLUMN onboarding_status");
        DB::statement("ALTER TABLE tenants DROP COLUMN employee_id_number");
    }
};
