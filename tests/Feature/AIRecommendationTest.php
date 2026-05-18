<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Cleaner;
use App\Models\Homeowner;
use App\Models\City;
use App\Models\Service;
use App\Models\Booking;
use App\Services\AI\XGBoostRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AIRecommendationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** @test */
    public function ai_recommends_only_online_cleaners()
    {
        $city = City::where('code', 'DAR')->first();
        
        // Create 3 online cleaners and 2 offline cleaners
        $onlineCleaners = Cleaner::where('city_id', $city->id)
            ->where('availability_status', 'online')
            ->take(3)
            ->get();
            
        $offlineCleaners = Cleaner::where('city_id', $city->id)
            ->where('availability_status', 'offline')
            ->take(2)
            ->get();

        $this->assertCount(3, $onlineCleaners);
        $this->assertCount(2, $offlineCleaners);

        // Test that offline cleaners are excluded from recommendations
        $homeowner = Homeowner::first();
        $service = Service::first();

        $booking = new Booking([
            'service_id' => $service->id,
            'city_id' => $city->id,
            'service_latitude' => $city->latitude,
            'service_longitude' => $city->longitude,
            'booking_type' => 'instant',
            'service_price' => $service->base_price,
            'total_amount' => $service->base_price,
        ]);
        $booking->homeowner = $homeowner;

        $recommendations = app(XGBoostRecommendationService::class)
            ->recommendCleaners($booking, 10);

        // Verify only online cleaners are recommended
        $recommendedIds = collect($recommendations)->pluck('cleaner_id');
        
        foreach ($offlineCleaners as $offline) {
            $this->assertNotContains(
                $offline->id, 
                $recommendedIds,
                'Offline cleaner should not be in recommendations'
            );
        }
    }

    /** @test */
    public function cleaner_can_toggle_availability()
    {
        $user = User::where('email', 'cleaner.DAR1@smartcleaner.co.tz')->first();
        $this->actingAs($user);

        $cleaner = $user->cleaner;
        $originalStatus = $cleaner->availability_status;

        // Toggle to offline
        $response = $this->postJson('/cleaner/availability/toggle', [
            'status' => 'offline',
        ]);

        $response->assertOk();
        $this->assertEquals('offline', $cleaner->fresh()->availability_status);

        // Toggle back to online
        $response = $this->postJson('/cleaner/availability/toggle', [
            'status' => 'online',
            'latitude' => -6.7924,
            'longitude' => 39.2083,
        ]);

        $response->assertOk();
        $this->assertEquals('online', $cleaner->fresh()->availability_status);
    }

    /** @test */
    public function homeowner_can_create_booking()
    {
        $user = User::where('email', 'homeowner1@smartcleaner.co.tz')->first();
        $this->actingAs($user);

        $service = Service::first();
        $city = City::where('code', 'DAR')->first();

        $response = $this->postJson('/homeowner/bookings', [
            'booking_type' => 'scheduled',
            'service_id' => $service->id,
            'city_id' => $city->id,
            'latitude' => -6.7924,
            'longitude' => 39.2083,
            'address' => '123 Test Street',
            'district' => 'Kinondoni',
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('bookings', [
            'homeowner_id' => $user->homeowner->id,
            'booking_type' => 'scheduled',
        ]);
    }
}