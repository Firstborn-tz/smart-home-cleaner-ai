<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();
            $table->string('uuid')->unique();
            
            // Booking Type & Status
            $table->enum('booking_type', ['instant', 'scheduled']);
            $table->enum('status', [
                'pending', 'searching_cleaner', 'cleaner_found',
                'cleaner_assigned', 'cleaner_accepted', 'cleaner_en_route',
                'cleaner_arrived', 'in_progress', 'completed',
                'cancelled', 'no_show', 'timeout', 'declined', 'expired'
            ])->default('pending');
            
            // Foreign Keys
            $table->foreignId('homeowner_id')->constrained('homeowners');
            $table->foreignId('cleaner_id')->nullable()->constrained('cleaners');
            $table->foreignId('service_id')->constrained('services');
            $table->foreignId('city_id')->constrained('cities');
            $table->foreignId('accepted_cleaner_id')->nullable()->constrained('cleaners');
            
            // Location Details
            $table->decimal('service_latitude', 10, 7);
            $table->decimal('service_longitude', 10, 7);
            $table->string('google_place_id')->nullable();
            $table->text('service_address');
            $table->string('district')->nullable();
            $table->string('ward')->nullable();
            $table->string('street')->nullable();
            $table->json('location_details')->nullable();
            
            // Distance & ETA (Real Road Distance from Google Maps)
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->integer('estimated_travel_time_minutes')->nullable();
            $table->integer('traffic_delay_minutes')->default(0);
            $table->decimal('route_quality_score', 5, 2)->nullable();
            
            // Timing
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cleaner_assigned_at')->nullable();
            $table->timestamp('cleaner_accepted_at')->nullable();
            $table->timestamp('cleaner_arrived_at')->nullable();
            $table->timestamp('service_started_at')->nullable();
            $table->timestamp('service_ended_at')->nullable();
            
            // AI Recommendation Data
            $table->decimal('ai_recommendation_score', 5, 2)->nullable();
            $table->json('ai_feature_scores')->nullable();
            $table->json('ai_recommendations_list')->nullable();
            $table->integer('ai_rank_position')->nullable();
            
            // Financial Breakdown
            $table->decimal('service_base_price', 10, 2);
            $table->decimal('instant_booking_fee', 10, 2)->default(0.00);
            $table->decimal('distance_fee', 10, 2)->default(0.00);
            $table->decimal('weekend_premium', 10, 2)->default(0.00);
            $table->decimal('peak_hour_surcharge', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('commission_percentage', 5, 2)->default(15.00);
            $table->decimal('commission_amount', 10, 2);
            $table->decimal('cleaner_payout_amount', 10, 2);
            
            // Special Instructions
            $table->text('special_instructions')->nullable();
            $table->json('additional_requirements')->nullable();
            $table->json('access_instructions')->nullable();
            
            // Verification
            $table->string('verification_code_hash')->nullable();
            $table->boolean('verification_completed')->default(false);
            $table->timestamp('verification_completed_at')->nullable();
            
            // Ratings
            $table->decimal('cleaner_rating_given', 3, 2)->nullable();
            $table->decimal('homeowner_rating_given', 3, 2)->nullable();
            $table->text('review_text')->nullable();
            $table->json('review_tags')->nullable();
            
            // Timeout Handling
            $table->integer('response_timeout_seconds')->nullable();
            $table->timestamp('timeout_at')->nullable();
            $table->integer('retry_count')->default(0);
            $table->integer('max_retry_attempts')->default(3);
            
            // Cancellation
            $table->string('cancelled_by')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->decimal('cancellation_fee', 10, 2)->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Performance Indexes
            $table->index('booking_number');
            $table->index(['booking_type', 'status']);
            $table->index('scheduled_at');
            $table->index(['cleaner_id', 'status']);
            $table->index(['homeowner_id', 'status']);
            $table->index('city_id');
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};