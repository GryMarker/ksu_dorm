<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->enum('bed_label', ['A', 'B', 'C', 'D', 'E', 'F']);
            $table->boolean('is_occupied')->default(false);
            $table->foreignId('occupant_tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->timestamps();

            $table->unique(['room_id', 'bed_label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beds');
    }
};
