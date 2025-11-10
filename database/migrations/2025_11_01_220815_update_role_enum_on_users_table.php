<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('dorm_master','student_director','tenant','employee','president') NOT NULL DEFAULT 'tenant'");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('dorm_master','student_director','tenant','employee','president'))");
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'tenant'");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('dorm_master','student_director','tenant') NOT NULL DEFAULT 'tenant'");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('dorm_master','student_director','tenant'))");
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'tenant'");
        }
    }
};
