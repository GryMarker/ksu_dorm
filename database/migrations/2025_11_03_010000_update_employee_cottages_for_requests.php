<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_cottages', function (Blueprint $table) {
            $table->foreignId('requested_tenant_id')->nullable()->after('tenant_id')->constrained('tenants')->nullOnDelete();
            $table->timestamp('requested_at')->nullable()->after('requested_tenant_id');
            $table->json('family_members')->nullable()->after('requested_at');
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE employee_cottages MODIFY status ENUM('available','requested','occupied','maintenance') NOT NULL DEFAULT 'available'");
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE employee_cottages DROP CONSTRAINT IF EXISTS employee_cottages_status_check');
            DB::statement("ALTER TABLE employee_cottages ADD CONSTRAINT employee_cottages_status_check CHECK (status IN ('available','requested','occupied','maintenance'))");
            DB::statement("ALTER TABLE employee_cottages ALTER COLUMN status SET DEFAULT 'available'");
        }
    }

    public function down(): void
    {
        Schema::table('employee_cottages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('requested_tenant_id');
            $table->dropColumn(['requested_at', 'family_members']);
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE employee_cottages MODIFY status ENUM('available','occupied','maintenance') NOT NULL DEFAULT 'available'");
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE employee_cottages DROP CONSTRAINT IF EXISTS employee_cottages_status_check');
            DB::statement("ALTER TABLE employee_cottages ADD CONSTRAINT employee_cottages_status_check CHECK (status IN ('available','occupied','maintenance'))");
            DB::statement("ALTER TABLE employee_cottages ALTER COLUMN status SET DEFAULT 'available'");
        }
    }
};
