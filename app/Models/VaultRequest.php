<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaultRequest extends Model
{
    use HasFactory;
    
    public function user()
    {
         return $this->belongsTo(User::class);
    }
    
    
    
    public function custodian()
    {
         return $this->belongsTo(User::class,'custodian_id');
    }
    
     public function protem()
    {
         return $this->belongsTo(User::class,'protem_id');
    }
    
   
    public function vault()
    {
        return $this->belongsTo(CaveForm::class,'vault_id');
    }
    
    public function issue()
    {
        return $this->hasMany(CaveDoc::class);
    }
    
}
