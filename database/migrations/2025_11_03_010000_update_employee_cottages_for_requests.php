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

        DB::statement("ALTER TABLE employee_cottages MODIFY status ENUM('available','requested','occupied','maintenance') NOT NULL DEFAULT 'available'");
    }

    public function down(): void
    {
        Schema::table('employee_cottages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('requested_tenant_id');
            $table->dropColumn(['requested_at', 'family_members']);
        });

        DB::statement("ALTER TABLE employee_cottages MODIFY status ENUM('available','occupied','maintenance') NOT NULL DEFAULT 'available'");
    }
};
