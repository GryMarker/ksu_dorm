<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'gender') && ! Schema::hasColumn('rooms', 'sex')) {
                $table->renameColumn('gender', 'sex');
            }
        });

        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'gender') && ! Schema::hasColumn('tenants', 'sex')) {
                $table->renameColumn('gender', 'sex');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'sex') && ! Schema::hasColumn('rooms', 'gender')) {
                $table->renameColumn('sex', 'gender');
            }
        });

        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'sex') && ! Schema::hasColumn('tenants', 'gender')) {
                $table->renameColumn('sex', 'gender');
            }
        });
    }
};
