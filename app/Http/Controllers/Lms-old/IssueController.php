<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IssueBook;
use App\Models\Book;
use App\Models\Office;
use App\Models\BookCategory;
use App\Models\Bookshelve;
use Carbon\Carbon;
class IssueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:view all book issue|view all book issue csv export', ['only' => ['index']]);
         $this->middleware('permission:issue approve', ['only' => ['store']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $issueDateFrom = $request->input('issue_date_from');
        $issueDateTo = $request->input('issue_date_to');
        $query = IssueBook::whereNull('issue_books.is_return');
                                             
                                    
         // Apply date filter
        if (!empty($issueDateFrom) && !empty($issueDateTo)) {
            $query->whereBetween('issue_books.approve_date', [
                Carbon::parse($issueDateFrom)->startOfDay(),
                Carbon::parse($issueDateTo)->endOfDay()
            ]);
        } elseif (!empty($issueDateFrom)) {
            $query->whereDate('issue_books.approve_date', '>=', Carbon::parse($issueDateFrom)->startOfDay());
        } elseif (!empty($issueDateTo)) {
            $query->whereDate('issue_books.approve_date', '<=', Carbon::parse($issueDateTo)->endOfDay());
        }
        // Get the paginated results
        $data = $query->paginate(25);
            
        $office=Office::all();
        $bookshelve=Bookshelve::all();
        $category=BookCategory::all();
        return view('lms.issue.index',compact('data','office','request','bookshelve','category'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }
}
