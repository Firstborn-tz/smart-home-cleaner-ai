<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->unsignedBigInteger('reviewer_id');
            $table->string('reviewer_type'); // 'homeowner' or 'cleaner'
            $table->unsignedBigInteger('reviewee_id');
            $table->string('reviewee_type'); // 'cleaner' or 'homeowner'
            $table->decimal('rating', 2, 1)->default(5.0);
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('helpful_count')->default(0);
            $table->integer('reported_count')->default(0);
            $table->string('status')->default('approved'); // pending, approved, rejected
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['reviewer_type', 'reviewer_id']);
            $table->index(['reviewee_type', 'reviewee_id']);
            $table->index('rating');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};