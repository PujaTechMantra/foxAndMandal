<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Bookmark;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function index(Request $request)
    {
        $wishlists = Bookmark::where('user_id', $request->user_id)->with('book')->get();
        return response()->json(['message' => 'Bookmark list', 'data' => $wishlists], 200);
    }


    public function store(Request $request)
    {
        $request->validate([
            'book_id' => 'required',
        ]);

        $wishlist = Bookmark::create([
            'user_id' => $request->user_id,
            'book_id' => $request->book_id,
        ]);

        return response()->json(['message' => 'Book added to wishlist', 'wishlist' => $wishlist], 200);
    }
}
