<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',        
        'from',            
        'to',             
        'travel_date', 
        'departure_time', 
        'bill_to',
        'matter_code',
        'type',
        'traveller',
        'sequence_no',
        'order_no',
        'trip_type',
        "return_date",
        "return_time",
        'seat_preference',
        'purpose',
        'status',
        'description',
        'food_preference'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
      public function matter()
    {
         return $this->belongsTo(MatterCode::class, 'matter_code', 'id');
    }
}
