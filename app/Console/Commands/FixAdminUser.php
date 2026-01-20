<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class FixAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes the super admin user permissions and password';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = 'abdallah@emiratespark.ae';
        $password = 'ZooCMS@2026A';
        
        $this->info("Fixing user: $email");

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User not found! Creating user...");
            $user = new User();
            $user->name = 'Abdallah Emam';
            $user->email = $email;
            $user->status = 'active'; // Ensure active status
        }

        // Force password update
        $user->password = Hash::make($password);
        $user->status = 'active';
        
        // Find Role
        $role = Role::where('slug', 'super_admin')->first();
        if (!$role) {
             $this->error("Super Admin role not found! Running seeder...");
             $this->call('db:seed', ['--class' => 'RoleSeeder']);
             $role = Role::where('slug', 'super_admin')->firstOrFail();
        }

        // Assign Role
        $user->role_id = $role->id;
        $user->save();

        $this->info("✅ SUCCESS: User verified.");
        $this->info("   - User ID: " . $user->id);
        $this->info("   - Role: " . $role->name);
        $this->info("   - Status: " . $user->status);
        $this->info("   - Password: " . $password);
        
        $this->comment("👉 You can now login at /admin/login");
    }
}
