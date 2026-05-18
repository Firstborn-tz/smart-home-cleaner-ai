<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions (skip if exists)
        $permissions = [
            'view_users', 'create_users', 'edit_users', 'delete_users',
            'view_cleaners', 'approve_cleaners', 'suspend_cleaners', 'delete_cleaners',
            'view_bookings', 'create_bookings', 'cancel_bookings', 'manage_bookings',
            'view_cities', 'create_cities', 'edit_cities', 'delete_cities', 'manage_city_pricing',
            'view_commissions', 'record_commission_payments', 'manage_commissions',
            'view_ai_performance', 'trigger_ai_training',
            'view_reports', 'export_reports',
            'view_reviews', 'moderate_reviews',
            'view_services', 'create_services', 'edit_services',
            'manage_settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions (skip if exists)
        
        // Super Admin - has all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());

        // Admin
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'view_users', 'create_users', 'edit_users',
            'view_cleaners', 'approve_cleaners', 'suspend_cleaners',
            'view_bookings', 'manage_bookings', 'cancel_bookings',
            'view_cities', 'edit_cities', 'manage_city_pricing',
            'view_commissions', 'record_commission_payments',
            'view_ai_performance', 'trigger_ai_training',
            'view_reports', 'export_reports',
            'view_reviews', 'moderate_reviews',
            'view_services', 'create_services', 'edit_services',
            'manage_settings',
        ]);

        // Cleaner
        $cleaner = Role::firstOrCreate(['name' => 'cleaner']);
        $cleaner->syncPermissions([
            'view_bookings', 'create_bookings',
        ]);

        // Homeowner
        $homeowner = Role::firstOrCreate(['name' => 'homeowner']);
        $homeowner->syncPermissions([
            'view_bookings', 'create_bookings', 'cancel_bookings',
            'view_cleaners', 'view_services',
        ]);

        echo "✅ Roles and permissions seeded successfully\n";
    }
}