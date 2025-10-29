<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaveForm extends Model
{
    use HasFactory;
    
    public function takeOut()
    {
    return $this->hasMany(CaveDoc::class)->where('is_return', NULL)
                ;
    }
    public function location()
    {
         return $this->belongsTo(CaveLocation::class);
    }
    
    public function category()
    {
         return $this->belongsTo(CaveCategory::class);
    }
    
    public function custodian()
    {
         return $this->belongsTo(User::class,'custodian_id');
    }
}
