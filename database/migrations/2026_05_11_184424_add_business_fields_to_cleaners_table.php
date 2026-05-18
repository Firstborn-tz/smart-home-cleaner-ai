<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('cleaners', function (Blueprint $table) {
        $table->string('business_name')->nullable()->after('working_days');
        $table->text('business_description')->nullable()->after('business_name');
        $table->string('business_phone')->nullable()->after('business_description');
        $table->string('business_email')->nullable()->after('business_phone');
        $table->integer('years_experience')->default(0)->after('business_email');
        $table->integer('team_size')->default(1)->after('years_experience');
        $table->string('cover_photo')->nullable()->after('team_size');
        $table->json('portfolio_images')->nullable()->after('cover_photo');
        $table->json('service_areas')->nullable()->after('portfolio_images');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cleaners', function (Blueprint $table) {
            //
        });
    }
};
