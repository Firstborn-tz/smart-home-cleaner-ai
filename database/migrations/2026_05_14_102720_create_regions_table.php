<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 5)->unique();
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_registration')->default(true);
            $table->timestamps();
        });

        // Seed Tanzania regions
        $regions = [
            ['name' => 'Dar es Salaam', 'code' => 'DAR'],
            ['name' => 'Dodoma', 'code' => 'DOD'],
            ['name' => 'Arusha', 'code' => 'ARU'],
            ['name' => 'Mwanza', 'code' => 'MWZ'],
            ['name' => 'Mbeya', 'code' => 'MBY'],
            ['name' => 'Morogoro', 'code' => 'MOR'],
            ['name' => 'Tanga', 'code' => 'TAN'],
            ['name' => 'Zanzibar', 'code' => 'ZNZ'],
            ['name' => 'Kigoma', 'code' => 'KIG'],
            ['name' => 'Tabora', 'code' => 'TAB'],
            ['name' => 'Iringa', 'code' => 'IRI'],
            ['name' => 'Kilimanjaro', 'code' => 'KIL'],
            ['name' => 'Ruvuma', 'code' => 'RUV'],
            ['name' => 'Mtwara', 'code' => 'MTW'],
            ['name' => 'Shinyanga', 'code' => 'SHI'],
            ['name' => 'Singida', 'code' => 'SIN'],
            ['name' => 'Geita', 'code' => 'GEI'],
            ['name' => 'Manyara', 'code' => 'MAN'],
            ['name' => 'Lindi', 'code' => 'LIN'],
            ['name' => 'Pwani', 'code' => 'PWA'],
        ];

        foreach ($regions as $region) {
            DB::table('regions')->insert(array_merge($region, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};