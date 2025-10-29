<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelBookingGuest extends Model
{
    protected $table = 'hotel_booking_guests';

   

    public function hotelBooking()
    {
        return $this->belongsTo(HotelBooking::class, 'hotel_booking_id');
    }
}
