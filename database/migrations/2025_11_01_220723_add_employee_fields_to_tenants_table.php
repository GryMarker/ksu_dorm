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

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE tenants MODIFY type ENUM('student','employee') NOT NULL DEFAULT 'student'");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE tenants ALTER COLUMN type SET DEFAULT 'student'");
            DB::statement("ALTER TABLE tenants ALTER COLUMN type SET NOT NULL");
        }

        DB::statement("UPDATE tenants SET onboarding_status = admission_status");
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE tenants MODIFY type ENUM('student','employee') NOT NULL");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE tenants ALTER COLUMN type DROP DEFAULT");
        }

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['onboarding_status', 'employee_id_number']);
        });
    }
};
