<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('cleaner_id')->constrained('cleaners');
            $table->foreignId('service_id')->constrained('services');
            $table->decimal('expected_total_amount', 10, 2);
            $table->decimal('actual_submitted_amount', 10, 2)->default(0.00);
            $table->decimal('remaining_unpaid_amount', 10, 2)->default(0.00);
            $table->decimal('overpayment_amount', 10, 2)->default(0.00);
            $table->decimal('commission_percentage', 5, 2);
            $table->decimal('commission_amount', 10, 2);
            $table->decimal('cleaner_balance', 10, 2)->default(0.00);
            $table->enum('payment_status', ['pending', 'partially_paid', 'fully_paid', 'overpaid', 'disputed'])
                  ->default('pending');
            $table->json('payment_history')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('recorded_by_admin_id')->nullable()->constrained('users');
            $table->timestamp('last_payment_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('payment_status');
            $table->index(['cleaner_id', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};