<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->canPerform('view_bookings');
    }

    public function view(User $user, Booking $booking): bool
    {
        return $user->canPerform('view_bookings') || $booking->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->canPerform('create_bookings');
    }

    public function update(User $user, Booking $booking): bool
    {
        return $user->canPerform('edit_bookings');
    }

    public function delete(User $user, Booking $booking): bool
    {
        return $user->canPerform('cancel_bookings'); // Using cancel permission for delete action
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $user->canPerform('cancel_bookings');
    }

    public function refund(User $user, Booking $booking): bool
    {
        return $user->canPerform('refund_payments');
    }
}
