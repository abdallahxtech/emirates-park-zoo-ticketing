<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->canPerform('manage_staff');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->canPerform('manage_staff');
    }

    public function create(User $user): bool
    {
        return $user->canPerform('manage_staff');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->canPerform('manage_staff');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->canPerform('manage_staff');
    }
}
