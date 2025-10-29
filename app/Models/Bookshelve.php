<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
class Bookshelve extends Model
{
    use HasFactory;
    protected $fillable = [
        'office_id',
        'user_id',
        'number',  // Add this line
        // ... other fillable fields
    ];
    public function books()
    {
        return $this->hasMany(Book::class, 'bookshelves_id');
    }
    public function office()
    {
        return $this->belongsTo(Office::class);
    }
    
    
     public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    
    public static function insertData($data, $successCount) {
        $id='';
        $value = DB::table('bookshelves')->where('number', $data['number'])->get();
        if($value->count() == 0) {
            $id = DB::table('bookshelves')->insertGetId($data);
           
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
