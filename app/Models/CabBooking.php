<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CabBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'bill_to',
        'bill_to_remarks',
        'booking_type',
        'cab_type',
        'from_location', 
        'to_location', 
        'departure_date', 
        'pickup_date', 
        'pickup_time', 
        'hours',
        'matter_code',
        'traveller',
        'sequence_no',
        'order_no',
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
