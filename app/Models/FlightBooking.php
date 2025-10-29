<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',        
        'trip_type',      
        'from',            
        'to',             
        'departure_date', 
        'return_date',   
        'return_time',     
        'traveler_number',  
        'bill_to',  
        'arrival_time',  
      
        'traveller',
        'matter_code',
        'sequence_no',
        'order_no',
        'seat_preference',
        'food_preference',
        'purpose',
        'description'
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
