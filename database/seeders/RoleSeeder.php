<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role; // Filament Shield or Custom Role model
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Using firstOrCreate to avoid duplicates
        $roles = [
            'super_admin' => [
                'name' => 'Super Admin',
                'slug' => 'super-admin', // Matches hasPermission check
                'permissions' => ['*'],
            ],
            'operations' => [
                'name' => 'Operations Manager',
                'slug' => 'operations',
                'permissions' => ['view_bookings', 'manage_capacity'],
            ],
            'sales' => [
                'name' => 'Sales Staff',
                'slug' => 'sales',
                'permissions' => ['create_bookings', 'view_bookings'],
            ],
            'finance' => [
                'name' => 'Finance',
                'slug' => 'finance',
                'permissions' => ['view_payments', 'refund_payments'],
            ],
            'restaurant' => [
                'name' => 'Restaurant Staff',
                'slug' => 'restaurant',
                'permissions' => ['view_kitchen_orders'],
            ],
        ];

        foreach ($roles as $key => $data) {
            \App\Models\Role::firstOrCreate(
                ['slug' => $data['slug']], 
                [
                    'name' => $data['name'],
                    'display_name' => $data['name'],
                    'permissions' => $data['permissions'],
                    'description' => $data['name'] . ' Role'
                ]
            );
        }
    }
}
