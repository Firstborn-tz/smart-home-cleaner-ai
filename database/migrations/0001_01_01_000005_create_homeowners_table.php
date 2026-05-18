<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homeowners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('homeowner_id')->unique();
            $table->string('region')->nullable();
            $table->string('district')->nullable();
            $table->string('ward')->nullable();
            $table->string('street')->nullable();
            $table->text('full_address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('google_place_id')->nullable();
            $table->json('saved_addresses')->nullable();
            $table->json('favorite_cleaners')->nullable();
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('total_bookings')->default(0);
            $table->integer('total_completed_bookings')->default(0);
            $table->integer('total_cancellations')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('homeowner_id');
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homeowners');
    }
};