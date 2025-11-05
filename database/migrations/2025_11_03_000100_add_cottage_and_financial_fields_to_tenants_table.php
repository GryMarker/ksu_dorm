<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->decimal('monthly_rate', 8, 2)->nullable()->after('employee_id_number');
            $table->boolean('salary_deduction')->default(false)->after('monthly_rate');
            $table->json('family_members')->nullable()->after('salary_deduction');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['monthly_rate', 'salary_deduction', 'family_members']);
        });
    }
};

