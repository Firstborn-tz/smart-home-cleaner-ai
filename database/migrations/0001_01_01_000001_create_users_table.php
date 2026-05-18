<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 15)->unique();
            $table->timestamp('phone_verified_at')->nullable();
            $table->enum('user_type', ['super_admin', 'admin', 'cleaner', 'homeowner']);
            $table->string('avatar_url')->nullable();
            $table->string('firebase_uid')->nullable()->index();
            $table->string('device_token')->nullable();
            $table->string('fcm_token')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended', 'banned'])->default('active');
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->boolean('is_online')->default(false);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_type', 'status']);
            $table->index('email');
            $table->index('phone');
            $table->index('uuid');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};