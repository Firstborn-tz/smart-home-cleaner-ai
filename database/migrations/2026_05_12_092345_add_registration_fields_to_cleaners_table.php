<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cleaners', function (Blueprint $table) {
            if (!Schema::hasColumn('cleaners', 'registration_status')) {
                $table->string('registration_status')->default('pending'); // pending, approved, rejected
            }
            if (!Schema::hasColumn('cleaners', 'registration_notes')) {
                $table->text('registration_notes')->nullable();
            }
            if (!Schema::hasColumn('cleaners', 'national_id')) {
                $table->string('national_id')->nullable();
            }
            if (!Schema::hasColumn('cleaners', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable();
            }
            if (!Schema::hasColumn('cleaners', 'gender')) {
                $table->string('gender', 10)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('cleaners', function (Blueprint $table) {
            $table->dropColumn([
                'registration_status', 'registration_notes', 
                'national_id', 'date_of_birth', 'gender'
            ]);
        });
    }
};