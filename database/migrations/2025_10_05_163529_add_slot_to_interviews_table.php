<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            $table->foreignId('slot_id')
                ->nullable()
                ->after('tenant_id')
                ->constrained('interview_slots')
                ->nullOnDelete();

            $table->index(['slot_id', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            $table->dropIndex('interviews_slot_id_tenant_id_index');
            $table->dropConstrainedForeignId('slot_id');
        });
    }
};
