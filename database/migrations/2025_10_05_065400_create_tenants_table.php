<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['student', 'employee']);
            $table->string('university_id_no')->unique();
            $table->string('program')->nullable();
            $table->string('year_level')->nullable();
            $table->string('phone');
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone');
            $table->text('medical_notes')->nullable();
            $table->enum('admission_status', ['draft', 'for_interview', 'approved', 'rejected', 'recheck'])->default('draft');
            $table->json('admission_form_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
