<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('service_categories');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->decimal('base_price', 10, 2);
            $table->decimal('instant_booking_premium', 10, 2)->default(0.00);
            $table->decimal('weekend_premium', 10, 2)->default(0.00);
            $table->decimal('holiday_premium', 10, 2)->default(0.00);
            $table->integer('estimated_duration_minutes')->default(120);
            $table->integer('min_duration_minutes')->default(60);
            $table->integer('max_duration_minutes')->default(480);
            $table->json('required_skills')->nullable();
            $table->json('equipment_required')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('is_active');
        });

        // Seed default service categories and services
        $categories = [
            ['id' => 1, 'name' => 'Home Cleaning', 'slug' => 'home-cleaning', 'sort_order' => 1],
            ['id' => 2, 'name' => 'Commercial Cleaning', 'slug' => 'commercial-cleaning', 'sort_order' => 2],
            ['id' => 3, 'name' => 'Specialized Cleaning', 'slug' => 'specialized-cleaning', 'sort_order' => 3],
        ];

        foreach ($categories as $category) {
            DB::table('service_categories')->insert(array_merge($category, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $services = [
            [
                'category_id' => 1,
                'name' => 'Standard Home Cleaning',
                'slug' => 'standard-home-cleaning',
                'description' => 'Regular cleaning for homes including dusting, mopping, and bathroom cleaning',
                'base_price' => 50000.00,
                'instant_booking_premium' => 10000.00,
                'estimated_duration_minutes' => 180,
                'sort_order' => 1,
            ],
            [
                'category_id' => 1,
                'name' => 'Deep Cleaning',
                'slug' => 'deep-cleaning',
                'description' => 'Intensive deep cleaning covering all corners, appliances, and fixtures',
                'base_price' => 100000.00,
                'instant_booking_premium' => 20000.00,
                'estimated_duration_minutes' => 300,
                'sort_order' => 2,
            ],
            [
                'category_id' => 2,
                'name' => 'Office Cleaning',
                'slug' => 'office-cleaning',
                'description' => 'Professional office space cleaning and sanitization',
                'base_price' => 80000.00,
                'instant_booking_premium' => 15000.00,
                'estimated_duration_minutes' => 240,
                'sort_order' => 3,
            ],
            [
                'category_id' => 1,
                'name' => 'Move In/Out Cleaning',
                'slug' => 'move-in-out-cleaning',
                'description' => 'Complete cleaning for property moving transitions',
                'base_price' => 120000.00,
                'instant_booking_premium' => 25000.00,
                'estimated_duration_minutes' => 360,
                'sort_order' => 4,
            ],
            [
                'category_id' => 3,
                'name' => 'Post-Construction Cleaning',
                'slug' => 'post-construction-cleaning',
                'description' => 'Thorough cleaning after renovation or construction work',
                'base_price' => 150000.00,
                'instant_booking_premium' => 30000.00,
                'estimated_duration_minutes' => 480,
                'sort_order' => 5,
            ],
        ];

        foreach ($services as $service) {
            DB::table('services')->insert(array_merge($service, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
        Schema::dropIfExists('service_categories');
    }
};