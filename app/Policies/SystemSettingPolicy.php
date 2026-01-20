<?php

namespace App\Policies;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SystemSettingPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole('super-admin'); // Only super-admin can manage system settings
    }

    public function view(User $user, SystemSetting $setting): bool
    {
        return $user->hasRole('super-admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super-admin');
    }

    public function update(User $user, SystemSetting $setting): bool
    {
        return $user->hasRole('super-admin');
    }

    public function delete(User $user, SystemSetting $setting): bool
    {
        return $user->hasRole('super-admin');
    }
}
