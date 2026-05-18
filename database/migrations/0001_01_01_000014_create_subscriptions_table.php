<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('plan_name');
            $table->string('plan_type');
            $table->decimal('price', 10, 2)->default(0);
            $table->string('billing_cycle')->default('monthly');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_renew')->default(true);
            $table->json('features')->nullable();
            $table->integer('max_cleaners')->nullable();
            $table->integer('max_bookings_per_month')->nullable();
            $table->decimal('commission_discount', 5, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('user_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};