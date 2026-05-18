<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('region');
            $table->string('code', 5)->unique();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->integer('service_radius_km')->default(30);
            $table->decimal('instant_booking_fee_percentage', 5, 2)->default(15.00);
            $table->decimal('traffic_multiplier', 3, 2)->default(1.00);
            $table->json('peak_hours_multiplier')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['is_active', 'sort_order']);
        });

        // Seed all Tanzania cities
        $cities = [
            ['Dar es Salaam', 'Dar es Salaam', 'DAR', -6.7924, 39.2083, 35, 15.00, 1.50, json_encode(['morning' => 1.5, 'evening' => 1.8]), 1],
            ['Dodoma', 'Dodoma', 'DOD', -6.1630, 35.7516, 40, 12.00, 1.20, json_encode(['morning' => 1.2, 'evening' => 1.3]), 2],
            ['Arusha', 'Arusha', 'ARU', -3.3667, 36.6833, 30, 15.00, 1.30, json_encode(['morning' => 1.3, 'evening' => 1.4]), 3],
            ['Mwanza', 'Mwanza', 'MWZ', -2.5167, 32.9000, 35, 15.00, 1.25, json_encode(['morning' => 1.25, 'evening' => 1.35]), 4],
            ['Mbeya', 'Mbeya', 'MBY', -8.9000, 33.4500, 30, 12.00, 1.10, json_encode(['morning' => 1.1, 'evening' => 1.2]), 5],
            ['Morogoro', 'Morogoro', 'MOR', -6.8167, 37.6667, 30, 12.00, 1.15, json_encode(['morning' => 1.15, 'evening' => 1.25]), 6],
            ['Tanga', 'Tanga', 'TAN', -5.0667, 39.1000, 25, 15.00, 1.20, json_encode(['morning' => 1.2, 'evening' => 1.3]), 7],
            ['Zanzibar', 'Zanzibar', 'ZNZ', -6.1659, 39.2026, 20, 18.00, 1.40, json_encode(['morning' => 1.4, 'evening' => 1.5]), 8],
            ['Kigoma', 'Kigoma', 'KIG', -4.8767, 29.6267, 30, 12.00, 1.00, json_encode(['morning' => 1.0, 'evening' => 1.1]), 9],
            ['Tabora', 'Tabora', 'TAB', -5.0167, 32.8000, 35, 12.00, 1.05, json_encode(['morning' => 1.05, 'evening' => 1.15]), 10],
            ['Iringa', 'Iringa', 'IRI', -7.7667, 35.7000, 30, 12.00, 1.10, json_encode(['morning' => 1.1, 'evening' => 1.2]), 11],
            ['Moshi', 'Kilimanjaro', 'MOS', -3.3500, 37.3333, 25, 15.00, 1.25, json_encode(['morning' => 1.25, 'evening' => 1.35]), 12],
            ['Songea', 'Ruvuma', 'SON', -10.6833, 35.6500, 30, 12.00, 1.00, json_encode(['morning' => 1.0, 'evening' => 1.1]), 13],
            ['Mtwara', 'Mtwara', 'MTW', -10.2667, 40.1833, 25, 12.00, 1.10, json_encode(['morning' => 1.1, 'evening' => 1.2]), 14],
            ['Shinyanga', 'Shinyanga', 'SHI', -3.6667, 33.4333, 30, 12.00, 1.05, json_encode(['morning' => 1.05, 'evening' => 1.15]), 15],
            ['Singida', 'Singida', 'SIN', -4.8167, 34.7500, 35, 12.00, 1.00, json_encode(['morning' => 1.0, 'evening' => 1.1]), 16],
            ['Geita', 'Geita', 'GEI', -2.8667, 32.1667, 30, 12.00, 1.00, json_encode(['morning' => 1.0, 'evening' => 1.1]), 17],
            ['Manyara', 'Manyara', 'MAN', -4.0000, 35.7333, 30, 12.00, 1.00, json_encode(['morning' => 1.0, 'evening' => 1.1]), 18],
            ['Lindi', 'Lindi', 'LIN', -9.9969, 39.7144, 25, 12.00, 1.00, json_encode(['morning' => 1.0, 'evening' => 1.1]), 19],
            ['Pwani', 'Pwani', 'PWA', -7.0000, 39.0000, 30, 12.00, 1.15, json_encode(['morning' => 1.15, 'evening' => 1.25]), 20],
        ];

        $timestamp = now();
        foreach ($cities as $city) {
            DB::table('cities')->insert([
                'name' => $city[0],
                'region' => $city[1],
                'code' => $city[2],
                'latitude' => $city[3],
                'longitude' => $city[4],
                'service_radius_km' => $city[5],
                'instant_booking_fee_percentage' => $city[6],
                'traffic_multiplier' => $city[7],
                'peak_hours_multiplier' => $city[8],
                'sort_order' => $city[9],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};