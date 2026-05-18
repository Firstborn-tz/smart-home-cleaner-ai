<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('code_hash');
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_used')->default(false);
            $table->integer('generation_count')->default(1);
            $table->integer('attempt_count')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->string('delivery_method')->default('sms');
            $table->boolean('delivery_confirmed')->default(false);
            $table->timestamps();
            
            $table->index(['booking_id', 'is_used']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_codes');
    }
};