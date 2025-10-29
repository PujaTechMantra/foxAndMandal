<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
class LostBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','bookshelves_id', 'office_id', 'uid', 'qrcode', 'category_id',
        'title', 'author', 'publisher', 'edition', 'page', 'quantity',
    ];


	
    public function bookshelve()
    {
        return $this->belongsTo(Bookshelve::class, 'bookshelves_id');
    }
     public function user()
     {
         return $this->belongsTo(User::class);
     }
     
     public function office()
    {
        return $this->belongsTo(Office::class);
    }
     
      public function bookshelves()
     {
         return $this->belongsTo(Bookshelve::class);
     }
     
      public function category()
     {
         return $this->belongsTo(BookCategory::class);
     }
     
     
     public function issueBooks()
    {
        return $this->hasMany(IssueBook::class, 'book_id', 'id');
    }
    
    
     public static function insertData($data, $successCount) {
        $id='';
        $value = DB::table('lost_books')->where('title', $data['title'])->where('bookshelves_id',$data['bookshelves_id'])->where('category_id',$data['category_id'])->where('edition',$data['edition'])->where('year',$data['year'])->where('book_no',$data['book_no'])->get();
        if($value->count() == 0) {
            $id = DB::table('lost_books')->insertGetId($data);
           
           //DB::table('users')->insert($data);
            $successCount++;
        $resp = [
            "successCount" => $successCount,
            "id" => $id,
        ];
        
         return $resp;
         } else {
             $resp = [
             "successCount" => 0,
             "id" => $value[0]->id,
             ];
            
             return $resp;
         }

        // return $count;

       
        
    }
}
