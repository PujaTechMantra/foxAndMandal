<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LostBook;
use App\Models\Book;
use App\Models\Office;
use App\Models\BookCategory;
use App\Models\Bookshelve;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Auth;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\JsonResponse;
class LostBookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:lost book list|lost book list csv upload|lost book list csv export|lost book list status change', ['only' => ['index']]);
         $this->middleware('permission:create lost book list', ['only' => ['create','store']]);
         $this->middleware('permission:update lost book list', ['only' => ['edit','update']]);
         $this->middleware('permission:delete lost book list', ['only' => ['destroy']]);
         $this->middleware('permission:view lost book list', ['only' => ['show']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): View
{
    $query = $request->input('keyword');
    $officeId = $request->input('office_id');
    $bookshelveId = $request->input('bookshelves_id');
    $categoryId = $request->input('category_id');
    $issueDateFrom = $request->input('issue_date_from');
    $issueDateTo = $request->input('issue_date_to');

    $data = LostBook::where(function($q) use ($query, $officeId, $bookshelveId, $categoryId, $issueDateFrom, $issueDateTo) {
        if ($query) {
            $q->where('title', 'LIKE', "%{$query}%")
              ->orWhere('author', 'LIKE', "%{$query}%")
              ->orWhere('publisher', 'LIKE', "%{$query}%")
              ->orWhere('edition', 'LIKE', "%{$query}%")
              ->orWhere('page', 'LIKE', "%{$query}%")
              ->orWhere('quantity', 'LIKE', "%{$query}%")
              ->orWhere('uid', 'LIKE', "%{$query}%");
        }
        if ($officeId) {
            $q->where('office_id', $officeId);
        }
        if ($bookshelveId) {
            $q->where('bookshelves_id', $bookshelveId);
        }
        if ($categoryId) {
            $q->where('category_id', $categoryId);
        }
        if (!empty($issueDateFrom) && !empty($issueDateTo)) {
            $q->whereBetween('created_at', [
                Carbon::parse($issueDateFrom)->startOfDay(),
                Carbon::parse($issueDateTo)->endOfDay()
            ]);
        } elseif (!empty($issueDateFrom)) {
            $q->whereDate('created_at', '>=', Carbon::parse($issueDateFrom)->startOfDay());
        } elseif (!empty($issueDateTo)) {
            $q->whereDate('created_at', '<=', Carbon::parse($issueDateTo)->endOfDay());
        }
    })
    ->where('is_deleted', 0)
    ->latest('id')
    ->paginate(25);

    $office = Office::all();
    $bookshelve = Bookshelve::all();
    $category = BookCategory::all();

    return view('lms.lost-book.index', compact('data', 'office', 'request', 'bookshelve', 'category'))
        ->with('i', (request()->input('page', 1) - 1) * 5);
}
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request): View
    {
        $office=Office::all();
        $bookshelve=Bookshelve::all();
        $category=BookCategory::all();
        return view('lms.lost-book.create',compact('office','request','bookshelve','category'));
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): RedirectResponse
    {
        request()->validate([
            'office_id' => 'required',
            'bookshelves_id' => 'required',
           
            'category_id' => 'required',
            'title' => 'required',
        ]);
        function generateUniqueAlphaNumeric($length = 10) {
            $random_string = '';
            for ($i = 0; $i < $length; $i++) {
                $number = random_int(0, 36);
                $character = base_convert($number, 10, 36);
                $random_string .= $character;
            }
            return $random_string;
        }
        for ($i = 0; $i < $request['quantity']; $i++) {
            $data=new LostBook();
            $data->office_id=$request['office_id'];
            $data->bookshelves_id=$request['bookshelves_id'];
            $data->category_id=$request['category_id'];
            $data->user_id=Auth::user()->id;
            $data->title=$request['title']??'';
            $data->uid=strtoupper(generateUniqueAlphaNumeric(10));
            $data->author=$request['author']??'';
            $data->publisher=$request['publisher']??'';
            $data->edition=$request['edition']??'';
            $data->page=$request['page']??'';
            $data->quantity=$request['quantity']??'';
            $data->book_no=$request['book_no']??'';
            $data->year=$request['year']??'';
            $data->qrcode=strtoupper(generateUniqueAlphaNumeric(10));
            $data->is_deleted=0;
            $data->save();
        }
        return redirect()->route('lostbooks.index')
                        ->with('success','Book created successfully.');
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($id): View
    {
        $data = LostBook::find($id);
        return view('lms.lost-book.view',compact('data'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id): View
    {
        $data = LostBook::find($id);
        $office=Office::all();
        $bookshelve=Bookshelve::all();
        $category=BookCategory::all();
        return view('lms.lost-book.edit',compact('data','office','bookshelve','category'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,  $id): RedirectResponse
    {
         $request->validate([
            'office_id' => [
                'required',
                'string'
            ]
        ]);
    
        $data = LostBook::find($id);
        $data->office_id=$request['office_id'];
        $data->bookshelves_id=$request['bookshelves_id'];
        $data->category_id=$request['category_id'];
        $data->user_id=Auth::user()->id;
        $data->title=$request['title']??'';
        $data->book_no=$request['book_no']??'';
        $data->author=$request['author']??'';
        $data->publisher=$request['publisher']??'';
        $data->edition=$request['edition']??'';
        $data->page=$request['page']??'';
        $data->year=$request['year']??'';
        $data->quantity=$request['quantity']??'';
        $data->save();
    
        return redirect()->back()
                        ->with('success','Books updated successfully');
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): RedirectResponse
    {
        //dd('hi');
        $data = LostBook::find($id);
        $data->is_deleted=1;
        $data->deleted_at=now();
        $data->save();
    
        return redirect()->route('lostbooks.index')
                        ->with('success','Book deleted successfully');
    }
    
    public function status($id): RedirectResponse
    {
        $data = LostBook::find($id);
        $status = ($data->status == 1) ? 0 : 1;
        $data->status = $status;
        $data->save();
    
        return redirect()->route('lostbooks.index')
                        ->with('success','Book status changed successfully');
    }
    
    
    //csv export
    
    public function csvExport(Request $request)
	{
		 $query = $request->input('keyword');
    $officeId = $request->input('office_id');
    $bookshelveId = $request->input('bookshelves_id');
    $categoryId = $request->input('category_id');
    $issueDateFrom = $request->input('issue_date_from');
    $issueDateTo = $request->input('issue_date_to');

    $data = LostBook::where(function($q) use ($query, $officeId, $bookshelveId, $categoryId, $issueDateFrom, $issueDateTo) {
        if ($query) {
            $q->where('title', 'LIKE', "%{$query}%")
              ->orWhere('author', 'LIKE', "%{$query}%")
              ->orWhere('publisher', 'LIKE', "%{$query}%")
              ->orWhere('edition', 'LIKE', "%{$query}%")
              ->orWhere('page', 'LIKE', "%{$query}%")
              ->orWhere('quantity', 'LIKE', "%{$query}%")
              ->orWhere('uid', 'LIKE', "%{$query}%");
        }
        if ($officeId) {
            $q->where('office_id', $officeId);
        }
        if ($bookshelveId) {
            $q->where('bookshelves_id', $bookshelveId);
        }
        if ($categoryId) {
            $q->where('category_id', $categoryId);
        }
        if (!empty($issueDateFrom) && !empty($issueDateTo)) {
            $q->whereBetween('created_at', [
                Carbon::parse($issueDateFrom)->startOfDay(),
                Carbon::parse($issueDateTo)->endOfDay()
            ]);
        } elseif (!empty($issueDateFrom)) {
            $q->whereDate('created_at', '>=', Carbon::parse($issueDateFrom)->startOfDay());
        } elseif (!empty($issueDateTo)) {
            $q->whereDate('created_at', '<=', Carbon::parse($issueDateTo)->endOfDay());
        }
    })
    ->where('is_deleted', 0)
    ->latest('id')->cursor();
        $book = $data->all();
        if (count($book) > 0) {
            $delimiter = ","; 
            $filename = "books.csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
            $fields = array('SR', 'Office','Office Location','Bookshelf Number','Category','Title','Uid','Author','Publisher','Edition','Pages','Quantity','Book No','Created By','Status','DATE'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($book as $row) {
                $datetime = date('j F, Y h:i A', strtotime($row['created_at']));
				

                $lineData = array(
                    $count,
					$row['office']['name'] ?? 'NA',
                    $row['office']['address'] ?? 'NA',
					$row->bookshelves->number ?? 'NA',
					$row->category->name ?? 'NA',
					$row->title ?? 'NA',
					$row->uid ?? 'NA',
					$row->author ?? 'NA',
					$row->publisher ?? 'NA',
					$row->edition ?? 'NA',
					$row->page ?? 'NA',
					$row->quantity ?? 'NA',
					$row->book_no ?? 'NA',
					$row->user->name ?? 'NA',
					($row->status == 1) ? 'active' : 'inactive',
					$datetime,
                );

                fputcsv($f, $lineData, $delimiter);

                $count++;
            }

            // Move back to beginning of file
            fseek($f, 0);

            // Set headers to download file rather than displayed
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '";');

            //output all remaining data on a file pointer
            fpassthru($f);
        }
	}
	
	//csv upload
	
	public function csvImport(Request $request)
{
    
    if (!empty($request->file)) {
        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileSize = $file->getSize();
        
        // Validate CSV extension and file size
        $valid_extension = ["csv"];
        $maxFileSize = 50097152; // Max 50MB

        if (in_array(strtolower($extension), $valid_extension)) {
            if ($fileSize <= $maxFileSize) {
                // Upload the file to the storage location
                $location = 'public/uploads/csv';
                $file->move($location, $filename);
                $filepath = $location . "/" . $filename;

                // Open the CSV file and read it
                $file = fopen($filepath, "r");
                $importData_arr = [];
                $i = 0;
                
                // Read the CSV file row by row
                while (($filedata = fgetcsv($file, 10000, ",")) !== false) {
                    // Skip the header row
                    if ($i == 0) {
                        $i++;
                        continue;
                    }

                    // Store each row in $importData_arr
                    $importData_arr[] = $filedata;
                    $i++;
                }
                fclose($file);
                
                $successCount = 0;
                foreach ($importData_arr as $importData) {
                    //dd($importData_arr);
                    // Handling Office Data
                    $office = Office::firstOrCreate(
                        ['name' => $importData[0], 'address' => $importData[1]],
                        ['created_at' => now(), 'updated_at' => now()]
                    );

                    // Handling Bookshelve Data
                    $bookshelve = Bookshelve::firstOrCreate(
                        ['number' => $importData[2]],
                        ['office_id' => $office->id, 'user_id' => Auth::user()->id]
                    );

                    // Handling Book Category Data
                    $bookCategory = BookCategory::firstOrCreate(
                        ['name' => $importData[3]],
                        ['created_at' => now(), 'updated_at' => now()]
                    );

                    // Insert the book data based on quantity
                    $quantity = isset($importData[9]) ? $importData[9] : 1; // Default quantity 1 if not provided
                    for ($i = 0; $i < $quantity; $i++) {
                        $bookData = [
                            "office_id" => $office->id,
                            "user_id" => Auth::user()->id,
                            "category_id" => $bookCategory->id,
                            "bookshelves_id" => $bookshelve->id,
                            "title" => isset($importData[4]) ? $importData[4] : null,
                            "uid" => strtoupper(generateUniqueAlphaNumericValue(10)),
                            "author" => isset($importData[5]) ? $importData[5] : null,
                            "publisher" => isset($importData[6]) ? $importData[6] : null,
                            "edition" => isset($importData[7]) ? $importData[7] : null,
                            "year" => isset($importData[8]) ? $importData[8] : null,
                            "quantity" => 1, // Inserting single entry per loop iteration
                            "page" => isset($importData[10]) ? $importData[10] : null,
                            "book_no" => isset($importData[11]) ? $importData[11] : null,
                            "status" => 1,
                            "is_deleted" => 0,
                            "qrcode" => strtoupper(generateUniqueAlphaNumericValue(10)),
                            "created_at" => now(),
                            "updated_at" => now(),
                        ];

                        // Insert the book data
                        LostBook::create($bookData);
                        $successCount++;
                    }
                }

                Session::flash('message', 'CSV Import Complete. Total number of entries: ' . $successCount);
            } else {
                Session::flash('message', 'File too large. File must be less than 50MB.');
            }
        } else {
            Session::flash('message', 'Invalid File Extension. Supported extensions are ' . implode(', ', $valid_extension));
        }
    } else {
        Session::flash('message', 'No file found.');
    }

    return redirect()->back();
}
}
