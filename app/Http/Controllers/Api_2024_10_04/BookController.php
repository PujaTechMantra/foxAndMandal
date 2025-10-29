<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Bookshelve;

class BookController extends Controller
{
    

    public function index(Request $request)
    {
  
        $query = $request->input('query');

        $books = Book::get();

        return response()->json(['message' => 'List of book',$books], 200);
    }
    public function bookDetails(Request $request)
    {
  
        $books = Book::findOrFail($request->id);

        return response()->json(['message' => 'Detail of book',$books], 200);
    }
    // public function store(Request $request)
    // {
  
    //     $query = $request->input('query');

    //     $books = Book::get();

    //     return response()->json(['message' => 'List of book',$books], 200);
    // }
    public function search(Request $request)
    {
  
        $query = $request->input('query');

        $books = Book::where('title', 'LIKE', "%{$query}%")
            ->orWhere('publisher', 'LIKE', "%{$query}%")
            ->orWhere('author', 'LIKE', "%{$query}%")
            ->orWhere('uid', 'LIKE', "%{$query}%")
            ->get();

        // return response()->json($books);
        return response()->json(['message' => 'List of search data',$books], 200);
    }

    public function searchDetailsByQrCode(Request $request)
    {
  
        $qrcode = $request->input('qrcode');

        $book = Book::where('qrcode', $qrcode)
            ->first();

        // return response()->json($books);
        return response()->json([
                                    'message' => 'Details of book by Qr-Code',
                                    'data' =>$book
                                ], 200);
    }
    public function CategoryWiseBookList(Request $request)
    {
  
        // $qrcode = $request->input('qrcode');

        $book = Book::where('category_id', $request->category_id)
            ->get();

        // return response()->json($books);
        return response()->json([
                                    'message' => 'Book list by category wise',
                                    'data' =>$book
                                ], 200);
    }

    // public function showBooksByBookShelveQRCode(Request $request)
    // {
    //     // Find the bookshelf by its QR code
    //     $bookshelve = Bookshelve::where('qrcode', $request->qrcode)->first();

    //     // Check if the bookshelf exists
    //     if (!$bookshelve) {
    //         return response()->json(['message' => 'Bookshelf not found'], 404);
    //     }

    //     // Retrieve the books related to the found bookshelf
    //     $books = $bookshelve->books;

    //     // Format and return the book details as a JSON response
    //     return response()->json([
    //         // 'bookshelve' => [
    //         //     'id' => $bookshelve->id,
    //         //     'qrcode' => $bookshelve->qrcode,
    //         //     // 'location' => $bookshelve->location,
    //         //     // 'description' => $bookshelve->description,
    //         // ],
    //         'books' => $books->map(function ($book) {
    //             return [
    //                 'data'=>$book
    //                 // 'title' => $book->title,
    //                 // 'author' => $book->author,
    //                 // 'publisher' => $book->publisher,
    //                 // 'edition' => $book->edition,
    //                 // 'quantity' => $book->quantity,
    //             ];
    //         }),
    //     ]);
    // }
    public function showBooksByBookShelveQRCode(Request $request)
    {
        // Find the bookshelf by its QR code
        $bookshelve = Bookshelve::where('qrcode', $request->qrcode)->first();

        // Check if the bookshelf exists
        if (!$bookshelve) {
            return response()->json(['message' => 'Bookshelf not found'], 404);
        }

        // Retrieve the books related to the found bookshelf, with their office and category details
        $books = $bookshelve->books()->with(['office', 'category'])->get();

        // Format and return the book details as a JSON response
        return response()->json([
            'books' => $books->map(function ($book) {
                return [
                    'message' => 'Book list by shelve QR-code wise',
                    'data'=> $book
                    // 'id' => $book->id,
                    // 'title' => $book->title,
                    // 'author' => $book->author,
                    // 'publisher' => $book->publisher,
                    // 'edition' => $book->edition,
                    // 'quantity' => $book->quantity,
                    // 'office' => [
                    //     'id' => $book->office->id,
                    //     'name' => $book->office->name,
                    //     'location' => $book->office->location,
                    // ],
                    // 'category' => [
                    //     'id' => $book->category->id,
                    //     'name' => $book->category->name,
                    //     'description' => $book->category->description,
                    // ],
                ];
            })
        ],200);
    }

    public function showBooksByBookShelve(Request $request)
    {
        $bookshelve = Bookshelve::where('number', $request->number)->first();

        if (!$bookshelve) {
            return response()->json(['message' => 'Bookshelf not found'], 404);
        }

        $books = $bookshelve->books()->get();

        return response()->json([
            'books' => $books->map(function ($book) {
                return [
                    'message' => 'Book list by shelve QR-code wise',
                    'data'=> $book
                ];
            })
        ],200);

       

        // $books = Book::where('bookshelves_id', $request->bookshelves_id)->get();

        // return response()->json([
        //             'message' => 'Book list by shelve wise',
        //             'data'=> $books
        // ],200);
    }


}
 