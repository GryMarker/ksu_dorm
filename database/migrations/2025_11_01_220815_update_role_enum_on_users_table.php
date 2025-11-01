<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('dorm_master','student_director','tenant','employee','president') NOT NULL DEFAULT 'tenant'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('dorm_master','student_director','tenant') NOT NULL DEFAULT 'tenant'");
    }
};
