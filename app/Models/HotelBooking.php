<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelBooking extends Model
{
    protected $table = 'hotel_bookings';

    protected $fillable = [
        'user_id','room_id', 'property_id',  'checkin_date', 'checkin_time', 'checkout_date', 'guest_number','room_number', 'status', 'bill_to','bill_to_remarks','matter_code','guest_type','hotel_type','text','sequence_no',
        'order_no', 'seat_preference','food_preference','purpose','description'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function guests()
    {
        return $this->hasMany(HotelBookingGuest::class, 'hotel_booking_id');
    }
     public function matter()
    {
         return $this->belongsTo(MatterCode::class, 'matter_code', 'id');
    }
}
