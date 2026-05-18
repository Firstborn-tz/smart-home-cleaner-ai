<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cleaner_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cleaner_id')->constrained('cleaners')->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->decimal('speed', 5, 2)->nullable();
            $table->decimal('heading', 5, 2)->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Tanzania');
            $table->timestamps();
            
            $table->index('cleaner_id');
            $table->index('created_at');
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cleaner_locations');
    }
};