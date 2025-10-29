<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Bookshelve;
use App\Models\User;
use App\Models\Office;
use App\Models\CabBooking;
use App\Models\FlightBooking;
use App\Models\TrainBooking;
use App\Models\HotelBooking;
use App\Models\CaveLocation;
use App\Models\CaveForm;
use App\Models\CaveDoc;
use Auth;
use DB;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
  public function index()
{
    $data = (object)[];
    $userOfficeId = Auth::user()->office_id ?? null;

    // Books per Office
    $booksPerOfficeQuery = DB::table('books')
        ->join('offices', 'offices.id', '=', 'books.office_id')
        ->select('offices.name', 'offices.address', 'offices.id', DB::raw('count(*) as total_books'))
        ->groupBy('offices.id', 'offices.name', 'offices.address');

    if ($userOfficeId) {
        $booksPerOfficeQuery->where('books.office_id', $userOfficeId);
    }
    $booksPerOffice = $booksPerOfficeQuery->get();

    // Books per Shelf
    $booksPerShelfQuery = DB::table('books')
        ->join('bookshelves', 'bookshelves.id', '=', 'books.bookshelves_id')
        ->select('bookshelves.number', 'bookshelves.id', DB::raw('count(*) as total_books'))
        ->groupBy('bookshelves.id', 'bookshelves.number');

    if ($userOfficeId) {
        $booksPerShelfQuery->where('books.office_id', $userOfficeId);
    }
    $booksPerShelf = $booksPerShelfQuery->get();

    // Issued Books per Office
    $issuedBooksPerOfficeQuery = DB::table('issue_books')
        ->join('books', 'issue_books.book_id', '=', 'books.id')
        ->join('offices', 'offices.id', '=', 'books.office_id')
        ->select('books.office_id', 'offices.name', 'offices.address', DB::raw('count(issue_books.id) as total_issued'))
        ->where(function ($query) {
            $query->whereNull('issue_books.is_return')
                ->orWhere('issue_books.status', 1);
        })
        ->groupBy('books.office_id', 'offices.name', 'offices.address');

    if ($userOfficeId) {
        $issuedBooksPerOfficeQuery->where('books.office_id', $userOfficeId);
    }
    $issuedBooksPerOffice = $issuedBooksPerOfficeQuery->get();

    // Available Books per Office
    $availableBooksPerOfficeQuery = DB::table('books')
        ->leftJoin('issue_books', 'books.id', '=', 'issue_books.book_id')
        ->join('offices', 'offices.id', '=', 'books.office_id')
        ->select('books.office_id', 'offices.name', 'offices.id', 'offices.address', DB::raw('count(books.id) as total_available'))
        ->where(function ($query) {
            $query->whereNull('issue_books.book_id')
                ->orWhere('issue_books.is_return', 1)
                ->orWhere('issue_books.status', 0);
        })
        ->groupBy('books.office_id', 'offices.name', 'offices.id', 'offices.address');

    if ($userOfficeId) {
        $availableBooksPerOfficeQuery->where('books.office_id', $userOfficeId);
    }
    $availableBooksPerOffice = $availableBooksPerOfficeQuery->get();

    // Summary Stats
    $data->totalbooks = Book::count();
    $data->totalbookshelf = Bookshelve::count();
    $data->totalmember = DB::table('user_permission_categories')->where('name', 'lms')->count();
    $data->totaloffice = Office::count();

    // Total Issued Books
    $data->totalissuebooks = DB::table('issue_books')
        ->join('books', 'issue_books.book_id', '=', 'books.id')
        ->where(function ($query) {
            $query->whereNull('issue_books.is_return')
                ->orWhere('issue_books.status', 1);
        })
        ->select(DB::raw('count(issue_books.id) as total_issued'))
        ->first();

    // Total Available Books
    $data->totalavailablebooks = DB::table('books')
        ->leftJoin('issue_books', 'books.id', '=', 'issue_books.book_id')
        ->where(function ($query) {
            $query->whereNull('issue_books.book_id')
                ->orWhere('issue_books.is_return', 1)
                ->orWhere('issue_books.status', 0);
        })
        ->select(DB::raw('count(books.id) as total_available'))
        ->first();

    // Other Modules
    $data->totalfmsmember = DB::table('user_permission_categories')->where('name', 'fms')->count();
    
    $data->totalcabbook = CabBooking::count();
    $data->totalflightbook = FlightBooking::count();
    $data->totaltrainbook = TrainBooking::where('type', 1)->count();
    $data->totalbusbook = TrainBooking::where('type', 2)->count();
    $data->totalhotelbook = HotelBooking::count();
    $data->totalrequest = $data->totalcabbook + $data->totalflightbook + $data->totaltrainbook + $data->totalbusbook + $data->totalhotelbook;
    
    
    $data->pending_cab = CabBooking::where('status', 1)->count();
    $data->pending_flight = FlightBooking::where('status', 1)->count();
    $data->pending_train = TrainBooking::where('status', 1)->where('type', 1)->count();
    $data->pending_bus = TrainBooking::where('status', 1)->where('type', 2)->count();
    $data->pending_hotel = HotelBooking::where('status', 1)->count();

    $data->pending_total = $data->pending_cab + $data->pending_flight + $data->pending_train + $data->pending_bus + $data->pending_hotel;
    
    
    $data->confirm_cab = CabBooking::where('status', 2)->count();
    $data->confirm_flight = FlightBooking::where('status', 2)->count();
    $data->confirm_train = TrainBooking::where('status', 2)->where('type', 1)->count();
    $data->confirm_bus = TrainBooking::where('status', 2)->where('type', 2)->count();
    $data->confirm_hotel = HotelBooking::where('status', 2)->count();

    $data->confirm_total = $data->confirm_cab + $data->confirm_flight + $data->confirm_train + $data->confirm_bus + $data->confirm_hotel;
    
    $data->book_cab = CabBooking::where('status', 3)->count();
    $data->book_flight = FlightBooking::where('status', 3)->count();
    $data->book_train = TrainBooking::where('status', 3)->where('type', 1)->count();
    $data->book_bus = TrainBooking::where('status', 3)->where('type', 2)->count();
    $data->book_hotel = HotelBooking::where('status', 3)->count();

    $data->book_total = $data->book_cab + $data->book_flight + $data->book_train + $data->book_bus + $data->book_hotel;
    
    
    $data->cancel_cab = CabBooking::where('status', 4)->count();
    $data->cancel_flight = FlightBooking::where('status', 4)->count();
    $data->cancel_train = TrainBooking::where('status', 4)->where('type', 1)->count();
    $data->cancel_bus = TrainBooking::where('status', 4)->where('type', 2)->count();
    $data->cancel_hotel = HotelBooking::where('status', 4)->count();

    $data->cancel_total = $data->cancel_cab + $data->cancel_flight + $data->cancel_train + $data->cancel_bus + $data->cancel_hotel;

    
    
    // Status breakdown
    $data->pending_flight = FlightBooking::where('status', 1)->count();
    $data->pending_hotel = HotelBooking::where('status', 1)->count();
    $data->pending_cab = CabBooking::where('status', 1)->count();
    $data->pending_train = TrainBooking::where('status', 1)->where('type', 1)->count();
    $data->pending_bus = TrainBooking::where('status', 1)->where('type', 2)->count();



    $data->totalvaultloc = CaveLocation::count();
    $data->totalvault = CaveForm::count();
    $data->totaloutsidevault = CaveDoc::where('scan_status', 1)->count();

    return view('home', compact(
        'booksPerOffice',
        'booksPerShelf',
        'issuedBooksPerOffice',
        'availableBooksPerOffice',
        'data'
    ));
}

}
