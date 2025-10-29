<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaveDoc extends Model
{
    use HasFactory;
    protected $fillable = [
        'cave_form_id',
        'user_id',
        'status',  // Add this line
        // ... other fillable fields
    ];
    public function user()
    {
         return $this->belongsTo(User::class);
    }
    public function request()
    {
         return $this->belongsTo(VaultRequest::class,'request_id');
    }
    
     public function user2()
    {
         return $this->belongsTo(User::class,'user_id2');
    }
    
    
    public function vault()
    {
         return $this->belongsTo(CaveForm::class,'cave_form_id');
    }
    
    public function returnuser()
    {
         return $this->belongsTo(User::class,'return_user_id');
    }
    
}
