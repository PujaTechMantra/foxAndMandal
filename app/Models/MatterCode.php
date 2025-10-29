<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatterCode extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'matter_code',
        'client_name',
    ];
}
