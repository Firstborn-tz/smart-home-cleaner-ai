<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('pricing_model', 20)->nullable()->after('booking_type');
            $table->decimal('booked_hours', 4, 1)->nullable()->after('pricing_model');
            $table->decimal('hourly_rate', 10, 2)->nullable()->after('booked_hours');
            $table->decimal('actual_hours', 4, 1)->nullable()->after('hourly_rate');
            $table->decimal('billed_hours', 4, 1)->nullable()->after('actual_hours');
            $table->decimal('final_amount', 10, 2)->nullable()->after('total_amount');
            $table->json('attempted_cleaners')->nullable()->after('cleaner_id');
            $table->integer('attempt_count')->default(0)->after('attempted_cleaners');
            $table->timestamp('cleaner_notified_at')->nullable()->after('cleaner_assigned_at');
            $table->timestamp('cleaner_responded_at')->nullable()->after('cleaner_notified_at');
            $table->timestamp('cleaner_departed_at')->nullable()->after('cleaner_responded_at');
            $table->string('arrival_confirmed_by', 30)->nullable()->after('cleaner_arrived_at');
            $table->string('arrival_verification_code', 10)->nullable()->after('arrival_confirmed_by');
            $table->boolean('was_early')->default(false)->after('arrival_verification_code');
            $table->boolean('was_late')->default(false)->after('was_early');
            $table->integer('minutes_early')->default(0)->after('was_late');
            $table->integer('minutes_late')->default(0)->after('minutes_early');
            $table->integer('grace_window_minutes')->default(15)->after('minutes_late');
        });

        Schema::table('cleaners', function (Blueprint $table) {
            $table->decimal('acceptance_rate', 5, 2)->default(100.00)->after('completion_rate');
            $table->decimal('rejection_rate', 5, 2)->default(0.00)->after('acceptance_rate');
            $table->integer('consecutive_rejections')->default(0)->after('rejection_rate');
            $table->integer('availability_penalty')->default(0)->after('consecutive_rejections');
            $table->timestamp('last_active_at')->nullable()->after('availability_penalty');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'pricing_model', 'booked_hours', 'hourly_rate', 'actual_hours',
                'billed_hours', 'final_amount', 'attempted_cleaners', 'attempt_count',
                'cleaner_notified_at', 'cleaner_responded_at', 'cleaner_departed_at',
                'arrival_confirmed_by', 'arrival_verification_code',
                'was_early', 'was_late', 'minutes_early', 'minutes_late', 'grace_window_minutes'
            ]);
        });

        Schema::table('cleaners', function (Blueprint $table) {
            $table->dropColumn([
                'acceptance_rate', 'rejection_rate', 'consecutive_rejections',
                'availability_penalty', 'last_active_at'
            ]);
        });
    }
};