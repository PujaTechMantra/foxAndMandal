<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Book;

class Bookmark extends Model
{
    use HasFactory;

     protected $fillable = [
        'sequence_no','order_no','from_user_id','to_user_id','status'
    ];

    public function fromuser()
    {
        return $this->belongsTo(User::class,'from_user_id');
    }
    
    public function touser()
    {
        return $this->belongsTo(User::class,'to_user_id');
    }
    public function item()
    {
        return $this->hasMany(BookmarkItem::class);
    }
    
    public function issue()
    {
        return $this->hasMany(IssueBook::class);
    }
}
