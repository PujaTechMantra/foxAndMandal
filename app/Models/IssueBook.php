<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssueBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'user_id',
        'request_date',
        'status',
        'approve_date',
        'is_transfer',
        'user_id_to_transfer',
        'transfer_date',
        'transfer_approve_status',
        'transfer_approve_date',
        'book_holder_user_id',
        'is_return',
        'return_date',
        'issue_type'
    ];
    
    
    public function user()
    {
         return $this->belongsTo(User::class);
    }
    
    public function bookmark()
    {
         return $this->belongsTo(Bookmark::class);
    }
    
     public function user2()
    {
         return $this->belongsTo(User::class,'user_id2');
    }
    
   
    public function book()
    {
         return $this->belongsTo(Book::class);
    }
}
