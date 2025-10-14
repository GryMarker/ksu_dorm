<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('building');
            $table->string('floor');
            $table->string('wing')->nullable();
            $table->enum('gender', ['male', 'female', 'mixed'])->default('mixed');
            $table->unsignedInteger('capacity')->default(6);
            $table->enum('status', ['open', 'closed', 'maintenance'])->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
