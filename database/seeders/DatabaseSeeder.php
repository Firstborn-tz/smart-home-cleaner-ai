<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\City;
use App\Models\Cleaner;
use App\Models\Homeowner;
use App\Models\Service;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles & Permissions
        $this->call(RolesAndPermissionsSeeder::class);

        // Create Super Admin
        if (!User::where('email', 'superadmin@smartcleaner.co.tz')->exists()) {
            $superAdmin = User::create([
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'email' => 'superadmin@smartcleaner.co.tz',
                'password' => Hash::make('password'),
                'phone' => '+255700000001',
                'user_type' => 'super_admin',
                'status' => 'active',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]);
            $superAdmin->assignRole('super_admin');
            echo "✅ Super Admin created\n";
        }

        // Create Admin
        if (!User::where('email', 'admin@smartcleaner.co.tz')->exists()) {
            $admin = User::create([
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@smartcleaner.co.tz',
                'password' => Hash::make('password'),
                'phone' => '+255700000002',
                'user_type' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]);
            $admin->assignRole('admin');
            echo "✅ Admin created\n";
        }

        // Create Test Cleaners for major cities
        $majorCities = City::whereIn('code', ['DAR', 'DOD', 'ARU', 'MWZ'])->get();
        
        foreach ($majorCities as $city) {
            for ($i = 1; $i <= 3; $i++) {
                $email = "cleaner.{$city->code}{$i}@smartcleaner.co.tz";
                
                if (!User::where('email', $email)->exists()) {
                    $user = User::create([
                        'first_name' => "Cleaner",
                        'last_name' => "{$city->code}{$i}",
                        'email' => $email,
                        'password' => Hash::make('password'),
                        'phone' => "+2557000000{$city->id}{$i}",
                        'user_type' => 'cleaner',
                        'status' => 'active',
                        'email_verified_at' => now(),
                        'phone_verified_at' => now(),
                        'is_online' => true,
                    ]);

                    Cleaner::create([
                        'user_id' => $user->id,
                        'cleaner_id' => 'CLN-' . strtoupper(\Str::random(8)),
                        'city_id' => $city->id,
                        'availability_status' => 'online',
                        'is_verified' => true,
                        'background_checked' => true,
                        'verified_at' => now(),
                        'rating' => rand(35, 50) / 10,
                        'total_completed_jobs' => rand(10, 100),
                        'completion_rate' => rand(85, 100),
                        'cancellation_rate' => rand(0, 10),
                        'experience_days_active' => rand(30, 365),
                        'avg_response_time_seconds' => rand(30, 300),
                        'profile_completion_score' => rand(80, 100),
                        'price_competitiveness' => rand(70, 100),
                        'success_rate' => rand(80, 100),
                        'repeat_customer_rate' => rand(30, 80),
                        'avg_job_duration_minutes' => rand(90, 240),
                        'current_latitude' => $city->latitude + (rand(-50, 50) / 1000),
                        'current_longitude' => $city->longitude + (rand(-50, 50) / 1000),
                        'location_sharing_enabled' => true,
                        'full_address' => "{$city->name}, Tanzania",
                        'district' => "{$city->name} CBD",
                        'region' => $city->region,
                        'service_skills' => json_encode([1, 2, 3]),
                        'wallet_balance' => rand(50000, 500000),
                        'total_earnings' => rand(100000, 1000000),
                        'pending_payout' => rand(0, 100000),
                    ]);

                    $user->assignRole('cleaner');
                }
            }
        }
        echo "✅ Cleaners created\n";

        // Create Test Homeowners
        for ($i = 1; $i <= 5; $i++) {
            $email = "homeowner{$i}@smartcleaner.co.tz";
            
            if (!User::where('email', $email)->exists()) {
                $user = User::create([
                    'first_name' => "Homeowner",
                    'last_name' => "{$i}",
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'phone' => "+2557000001{$i}",
                    'user_type' => 'homeowner',
                    'status' => 'active',
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                ]);

                Homeowner::create([
                    'user_id' => $user->id,
                    'homeowner_id' => 'HMO-' . strtoupper(\Str::random(8)),
                    'full_address' => "Sample Address {$i}, Dar es Salaam",
                    'latitude' => -6.7924 + (rand(-100, 100) / 1000),
                    'longitude' => 39.2083 + (rand(-100, 100) / 1000),
                    'region' => 'Dar es Salaam',
                    'district' => 'Kinondoni',
                    'ward' => 'Sample Ward',
                    'street' => "Sample Street {$i}",
                    'rating' => rand(30, 50) / 10,
                    'total_bookings' => rand(0, 10),
                ]);

                $user->assignRole('homeowner');
            }
        }
        echo "✅ Homeowners created\n";

        echo "\n============================================\n";
        echo " Database Seeded Successfully!\n";
        echo "============================================\n";
        echo "\nTest Accounts:\n";
        echo "  Super Admin: superadmin@smartcleaner.co.tz / password\n";
        echo "  Admin: admin@smartcleaner.co.tz / password\n";
        echo "  Cleaner: cleaner.DAR1@smartcleaner.co.tz / password\n";
        echo "  Homeowner: homeowner1@smartcleaner.co.tz / password\n";
    }
}