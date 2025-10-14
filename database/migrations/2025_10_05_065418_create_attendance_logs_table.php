<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['in', 'out']);
            $table->timestamp('timestamp');
            $table->enum('mode', ['qr', 'rfid', 'manual'])->default('manual');
            $table->string('device_id')->nullable();
            $table->string('ip')->nullable();
            $table->text('remarks')->nullable();

            $table->index(['tenant_id', 'timestamp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
