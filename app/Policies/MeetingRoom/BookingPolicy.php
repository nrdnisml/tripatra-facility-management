<?php

namespace App\Policies\MeetingRoom;

use App\Models\MeetingRoom\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function update(User $user, Booking  $booking)
    {
        return $user->id == $booking->booked_by;
    }

    public function delete(User $user, Booking  $booking)
    {
        return $user->id == $booking->booked_by;
    }
}