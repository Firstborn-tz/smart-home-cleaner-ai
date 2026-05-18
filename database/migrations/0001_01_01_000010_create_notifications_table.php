<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable();
            $table->string('icon')->nullable();
            $table->string('action_url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('channel')->default('push'); // push, sms, email, in-app
            $table->string('status')->default('sent'); // pending, sent, delivered, failed
            $table->integer('priority')->default(0); // 0=normal, 1=high, 2=urgent
            $table->timestamps();
            
            $table->index(['user_id', 'read_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};