<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('full_name')->after('user_id');
            $table->string('nickname')->nullable()->after('full_name');
            $table->date('dob')->nullable()->after('nickname');
            $table->text('home_address')->nullable()->after('dob');
            $table->unsignedSmallInteger('age')->nullable()->after('home_address');
            $table->string('place_of_birth')->nullable()->after('age');
            $table->string('father_name')->nullable()->after('place_of_birth');
            $table->string('father_contact')->nullable()->after('father_name');
            $table->string('mother_name')->nullable()->after('father_contact');
            $table->string('mother_contact')->nullable()->after('mother_name');
            $table->string('course_year')->nullable()->after('mother_contact');
            $table->string('cellphone')->nullable()->after('course_year');
            $table->timestamp('policy_accepted_at')->nullable()->after('cellphone');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'full_name',
                'nickname',
                'dob',
                'home_address',
                'age',
                'place_of_birth',
                'father_name',
                'father_contact',
                'mother_name',
                'mother_contact',
                'course_year',
                'cellphone',
                'policy_accepted_at',
            ]);
        });
    }
};
