<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnRequest extends Model
{
    use HasFactory;
    
    
    public function fromuser()
    {
        return $this->belongsTo(User::class,'from_user_id');
    }
    
    public function touser()
    {
        return $this->belongsTo(User::class,'to_user_id');
    }
    
    
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}