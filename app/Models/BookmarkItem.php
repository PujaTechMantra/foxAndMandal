<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Book;

class BookmarkItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id','bookmark_id','user_id',
    ];
    public function bookmark()
    {
        return $this->belongsTo(Bookmark::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
