<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->canPerform('view_products') || $user->canPerform('manage_products');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->canPerform('view_products') || $user->canPerform('manage_products');
    }

    public function create(User $user): bool
    {
        return $user->canPerform('manage_products');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->canPerform('manage_products');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->canPerform('manage_products');
    }

    public function updateCapacity(User $user, Product $product): bool
    {
        return $user->canPerform('manage_capacity');
    }
}
