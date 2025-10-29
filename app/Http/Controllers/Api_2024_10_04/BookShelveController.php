<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bookshelve;

class BookShelveController extends Controller
{
    public function searchByQrCode(Request $request)
    {
  
        $qrcode = $request->input('qrcode');

        $book = Bookshelve::where('qrcode', $qrcode)
            ->get();

        return response()->json([
                                    'message' => 'Book selves by QR-code',
                                    'data' =>$book
                                ], 200);
    }
}
