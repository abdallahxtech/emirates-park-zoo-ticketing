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
        $roles = ['super_admin', 'operations', 'sales', 'finance', 'restaurant'];

        foreach ($roles as $roleName) {
            \App\Models\Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }
    }
}
