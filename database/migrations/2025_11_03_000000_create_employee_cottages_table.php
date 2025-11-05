<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_cottages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('building')->default('Employee Village');
            $table->string('wing')->default('Cottages');
            $table->enum('status', ['available', 'requested', 'occupied', 'maintenance'])->default('available');
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_cottages');
    }
};
