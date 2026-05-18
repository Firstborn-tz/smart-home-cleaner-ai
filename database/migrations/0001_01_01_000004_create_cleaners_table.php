<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cleaners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('cleaner_id')->unique();
            $table->foreignId('city_id')->constrained('cities');
            
            // Availability Status (Critical)
            $table->enum('availability_status', ['online', 'online_busy', 'offline', 'scheduled_only'])
                  ->default('offline');
            
            // Verification & Background
            $table->boolean('is_verified')->default(false);
            $table->boolean('background_checked')->default(false);
            $table->timestamp('verified_at')->nullable();
            
            // Performance Metrics
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('total_completed_jobs')->default(0);
            $table->integer('total_cancellations')->default(0);
            $table->integer('total_no_shows')->default(0);
            $table->decimal('completion_rate', 5, 2)->default(0.00);
            $table->decimal('cancellation_rate', 5, 2)->default(0.00);
            $table->integer('complaints_count')->default(0);
            $table->integer('experience_days_active')->default(0);
            $table->decimal('avg_response_time_seconds', 10, 2)->default(0.00);
            $table->decimal('price_competitiveness', 5, 2)->default(0.00);
            $table->decimal('profile_completion_score', 5, 2)->default(0.00);
            $table->decimal('success_rate', 5, 2)->default(0.00);
            $table->decimal('repeat_customer_rate', 5, 2)->default(0.00);
            $table->decimal('avg_job_duration_minutes', 8, 2)->default(0.00);
            $table->timestamp('last_booking_at')->nullable();
            
            // Location
            $table->decimal('current_latitude', 10, 7)->nullable();
            $table->decimal('current_longitude', 10, 7)->nullable();
            $table->string('google_place_id')->nullable();
            $table->string('region')->nullable();
            $table->string('district')->nullable();
            $table->string('ward')->nullable();
            $table->string('street')->nullable();
            $table->text('full_address')->nullable();
            $table->boolean('location_sharing_enabled')->default(false);
            
            // Service Skills
            $table->json('service_skills')->nullable();
            $table->json('certifications')->nullable();
            $table->json('languages')->nullable();
            
            // Financial
            $table->decimal('wallet_balance', 12, 2)->default(0.00);
            $table->decimal('total_earnings', 12, 2)->default(0.00);
            $table->decimal('pending_payout', 12, 2)->default(0.00);
            
            // Shift Management
            $table->time('shift_start_time')->nullable();
            $table->time('shift_end_time')->nullable();
            $table->integer('max_service_radius_km')->default(30);
            $table->json('working_days')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['availability_status', 'city_id']);
            $table->index('rating');
            $table->index('cleaner_id');
            $table->index(['current_latitude', 'current_longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cleaners');
    }
};