<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IssueBook;
use App\Models\Bookshelve;
use App\Models\Book;
use App\Models\BookTransfer;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class IssueBookController extends Controller
{
    public function store(Request $request)
    {

        // $validated = $request->validate([
        //     'book_id' => 'required|array',
        //     'request_date' => 'required|date',
        //     'approve_date' => 'nullable|date',
        // ]);

        // $bookIds = $request->book_id; 
        // foreach ($bookIds as $bookId) {
        // $data = IssueBook::create([
        //         'user_id' => $request->user_id,
        //         'book_id' => $bookId,
        //         'request_date' => $request->request_date,
        //         'status' => $request->status,
        //         // Uncomment and use the status field if needed
        //         // 'status' => $validated['status'],
        //         'approve_date' => $request->approve_date,
        //     ]);
        //     $insertedData[] = $data;
        // }

  
        // return response()->json([
        //     'message' => 'Books issued successfully.',
        //    'data'=>$insertedData
        // ]);


        // Validate the input data
        $validated = $request->validate([
            'book_id' => 'required|array',
            'user_id' => 'required', 
            'request_date' => 'required',
            //  'approve_date' => 'nullable',
        ]);

        $insertedData = [];

        try {
            
            foreach ($validated['book_id'] as $bookId) {
                $data = IssueBook::create([
                    'user_id' => $validated['user_id'],
                    'book_id' => $bookId,
                    'request_date' => $validated['request_date'],
                    // 'approve_date' => $validated['approve_date'],
                ]);
                $insertedData[] = $data;
            }

            return response()->json([
                'message' => 'Books issued successfully.',
                'data' => $insertedData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while issuing books.',
                'error' => $e->getMessage(),
            ], 500);
        }

    }
    public function listByUser(Request $request)
    {
  
        $userId = $request->input('user_id');

        $books = IssueBook::where('user_id', $userId)
            ->get();

       
        return response()->json([
                                    'message' => 'List of book of user',
                                    'data' =>$books
                                ], 200);
    }

    
    public function returnBook(Request $request)
    {
        
        $validated = $request->validate([
            'qrcode' => 'required|string',          
            'user_id' => 'required',          
            'book_id' => 'required|integer|exists:books,id', 
            // 'number' =>'required',
            // 'area' =>'required',
            // 'approve_date' =>'required',    
        ]);

        $bookshelf = Bookshelve::where('qrcode', $validated['qrcode'])
        // ->where('number', $validated['number'])
        //    -> where('area', $validated['area'])
        ->first();

        if (!$bookshelf) {
            return response()->json(['message' => 'No bookshelf found for the provided QR code.'], 404);
        }

        $book = Book::where('bookshelves_id', $bookshelf->id)
                    ->where('id', $validated['book_id']) 
                    ->first();

        if (!$book) {
            return response()->json(['message' => 'No book found on the provided bookshelf.'], 404);
        }

        $issueBook = IssueBook::where([
            'book_id' => $book->id, 
            'user_id' => $request->user_id,
            // 'approve_date' => $request->approve_date,
        ])->first();

        if (!$issueBook) {
            return response()->json(['message' => 'No active issue record found for this book or the book has already been returned.'], 404);
        }

        $issueBook->update([
            'is_return' => 1,                 
            'return_date' => Carbon::now()->toDateString(), 
        ]);

        return response()->json([
            'message' => 'Book return status updated successfully.',
            'data' => $issueBook,
            'shelve_data'=>$bookshelf
        ]);
    }


    public function transferBook(Request $request)
    {
       
        $validated = $request->validate([
            'book_id' => 'required',
            'from_user_id' => 'required', 
            'to_user_id' => 'required',   
        ]);
   
        $data = BookTransfer::create([
            'is_transfer' => 1,
            'from_user_id' => $validated['from_user_id'],
            'to_user_id' => $validated['to_user_id'],
            'transfer_date' => now()->toDateString(),
        ]);
        
        return response()->json([
            'message' => 'Book transfer status updated successfully.',
            'data' => $issueBook
        ]);
    }
    
    
    
}
