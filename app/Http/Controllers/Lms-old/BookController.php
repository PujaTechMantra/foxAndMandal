<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Office;
use App\Models\BookCategory;
use App\Models\Bookshelve;
use App\Models\IssueBook;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Auth;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\JsonResponse;
class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:book list|book csv upload|book csv export|book status change|book issue list|book issue list csv download', ['only' => ['index']]);
         $this->middleware('permission:create book', ['only' => ['create','store']]);
         $this->middleware('permission:update book', ['only' => ['edit','update']]);
         $this->middleware('permission:delete book', ['only' => ['destroy']]);
         $this->middleware('permission:view book', ['only' => ['show']]);
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
        $data = Book::where(function($q) use ($query, $officeId,$bookshelveId,$categoryId) {
                if ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                      ->orWhere('author', 'LIKE', "%{$query}%")
                      ->orWhere('publisher', 'LIKE', "%{$query}%")
                      ->orWhere('edition', 'LIKE', "%{$query}%")
                      ->orWhere('page', 'LIKE', "%{$query}%")
                      ->orWhere('quantity', 'LIKE', "%{$query}%")->orWhere('uid', 'LIKE', "%{$query}%");
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
            })
            ->latest('id')->paginate(25);
        $office=Office::all();
        $bookshelve=Bookshelve::all();
        $category=BookCategory::where('status',1)->get();
        return view('lms.book.index',compact('data','office','request','bookshelve','category'))
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
        $category=BookCategory::where('status',1)->get();
        return view('lms.book.create',compact('office','request','bookshelve','category'));
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
            $data=new Book();
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
            $data->qrcode=strtoupper(generateUniqueAlphaNumeric(10));
            $data->save();
        }
        return redirect()->route('books.index')
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
        $data = Book::find($id);
        return view('lms.book.view',compact('data'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id): View
    {
        $data = Book::find($id);
        $office=Office::all();
        $bookshelve=Bookshelve::all();
        $category=BookCategory::where('status',1)->get();
        return view('lms.book.edit',compact('data','office','bookshelve','category'));
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
    
        $data = Book::find($id);
        $data->office_id=$request['office_id'];
        $data->bookshelves_id=$request['bookshelves_id']??'';
        $data->category_id=$request['category_id']??'';
        $data->user_id=Auth::user()->id;
        $data->title=$request['title']??'';
        
        $data->author=$request['author']??'';
        $data->publisher=$request['publisher']??'';
        $data->edition=$request['edition']??'';
        $data->page=$request['page']??'';
        $data->quantity=$request['quantity']??'';
        $data->save();
    
        return redirect()->route('books.index')
                        ->with('success','Bookshelve updated successfully');
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): RedirectResponse
    {
        $data = Book::find($id);
        $data->delete();
    
        return redirect()->route('books.index')
                        ->with('success','Book deleted successfully');
    }
    
    public function status($id): RedirectResponse
    {
        $data = Book::find($id);
        $status = ($data->status == 1) ? 0 : 1;
        $data->status = $status;
        $data->save();
    
        return redirect()->route('books.index')
                        ->with('success','Book status changed successfully');
    }
    
    
    //csv export
    
    public function csvExport(Request $request)
	{
		$query = $request->input('keyword');
        $officeId = $request->input('office_id');
        $bookshelve=$request->input('bookshelves_id');
        $cat= $request->input('category_id');
        $data = Book::where(function($q) use ($query, $officeId,$bookshelve,$cat) {
                if ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                      ->orWhere('author', 'LIKE', "%{$query}%")
                      ->orWhere('uid', 'LIKE', "%{$query}%")
                      ->orWhere('publisher', 'LIKE', "%{$query}%")
                      ->orWhere('edition', 'LIKE', "%{$query}%")
                      ->orWhere('page', 'LIKE', "%{$query}%")
                      ->orWhere('quantity', 'LIKE', "%{$query}%");
                }
                if ($officeId) {
                    $q->where('office_id', $officeId);
                }
                if ($bookshelve) {
                    $q->where('bookshelves_id', $bookshelve);
                }
                if ($cat) {
                    $q->where('category_id', $cat);
                }
            })
            ->cursor();
        $book = $data->all();
        if (count($book) > 0) {
            $delimiter = ","; 
            $filename = "books.csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
            $fields = array('SR', 'Office','Office Location','Bookshelf Number','Category','Title','Uid','Author','Publisher','Edition','Pages','Quantity','Created By','Status','DATE'); 
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
		 //dd($request->all());
         if (!empty($request->file)) {
             $file = $request->file('file');
             $filename = $file->getClientOriginalName();
             $extension = $file->getClientOriginalExtension();
             $tempPath = $file->getRealPath();
             $fileSize = $file->getSize();
             $mimeType = $file->getMimeType();
 
             $valid_extension = array("csv");
             $maxFileSize = 50097152;
             if (in_array(strtolower($extension), $valid_extension)) {
                 if ($fileSize <= $maxFileSize) {
                     $location = 'public/uploads/csv';
                     $file->move($location, $filename);
                     // $filepath = public_path($location . "/" . $filename);
                     $filepath = $location . "/" . $filename;
 
                     // dd($filepath);
 
                     $file = fopen($filepath, "r");
                     $importData_arr = array();
                     $i = 0;
                     while (($filedata = fgetcsv($file, 10000, ",")) !== FALSE) {
                         $num = count($filedata);
                         // Skip first row
                         if ($i == 0) {
                             $i++;
                             continue;
                         }
                         for ($c = 0; $c < $num; $c++) {
                             $importData_arr[$i][] = $filedata[$c];
                         }
                         $i++;
                     }
                     fclose($file);
                     $successCount = 0;
                        $userId='';
                     foreach ($importData_arr as $importData) {
                        $count = $total = 0;
                        $officeData = '';
                        $bookshelveData='';
                        $bookcatData='';
                        foreach (explode(',', $importData[0]) as $cateKey => $catVal) {
                            $catExistCheck = Office::where('name', $catVal)->where('address',$importData[1])->first();
                            if ($catExistCheck) {
                                $insertDirCatId = $catExistCheck->id;
                                $officeData = $insertDirCatId;
                            } else {
                                $dirCat = new Office();
                                $dirCat->name = $catVal;
                                $dirCat->address =$importData[1];
                                $dirCat->save();
                                $insertDirCatId = $dirCat->id;

                                $officeData = $insertDirCatId;
                            }
                        }
                        foreach (explode(',', $importData[2]) as $cateKey => $catVal) {
                            $catExistCheck = Bookshelve::where('number', $catVal)->first();
                            if ($catExistCheck) {
                                $insertDirCatId = $catExistCheck->id;
                                $bookshelveData = $insertDirCatId;
                            } else {
                                $dirCat = new Bookshelve();
                                $dirCat->number = $catVal;
                                $dirCat->save();
                                $insertDirCatId = $dirCat->id;

                                $bookshelveData = $insertDirCatId;
                            }
                        }
                        foreach (explode(',', $importData[3]) as $cateKey => $catVal) {
                            $catExistCheck = BookCategory::where('name', $catVal)->first();
                            if ($catExistCheck) {
                                $insertDirCatId = $catExistCheck->id;
                                $bookcatData = $insertDirCatId;
                            } else {
                                $dirCat = new BookCategory();
                                $dirCat->name = $catVal;
                                $dirCat->save();
                                $insertDirCatId = $dirCat->id;

                                $bookcatData = $insertDirCatId;
                            }
                        }
                         function generateUniqueAlphaNumeric($length = 10) {
                            $random_string = '';
                            for ($i = 0; $i < $length; $i++) {
                                $number = random_int(0, 36);
                                $character = base_convert($number, 10, 36);
                                $random_string .= $character;
                            }
                            return $random_string;
                        }
                            for ($i = 0; $i < $importData[9]; $i++) {
                             $insertData = array(
                                 "office_id" => $officeData? $officeData : null,
                                 "user_id" => Auth::user()->id,
                                 "category_id" => $bookcatData ? $bookcatData : null,
                                 "bookshelves_id" => $bookshelveData ? $bookshelveData : null,
                                 "title" => isset($importData[4]) ? $importData[4] : null,
                                 "uid" => strtoupper(generateUniqueAlphaNumeric(10)),
                                 "author" => isset($importData[5]) ? $importData[5] : null,
                                 "publisher" => isset($importData[6]) ? $importData[6] : null,
                                 "edition" => isset($importData[7]) ? $importData[7] : null,
                                 "page" => isset($importData[8]) ? $importData[8] : null,
                                 "quantity" => isset($importData[9]) ? $importData[9] : null,
                                 "status" => 1,
                                 "qrcode" => strtoupper(generateUniqueAlphaNumeric(10)),
                                 "created_at" => date('Y-m-d H:i:s'),
                                 "updated_at" => date('Y-m-d H:i:s'),
                             );
                              $resp = Book::insertData($insertData, $successCount);
                            }
                        }						
                              
                    
                   
                        
                     Session::flash('message', 'CSV Import Complete. Total no of entries: ' . count($importData_arr));
                 } else {
                     Session::flash('message', 'File too large. File must be less than 50MB.');
                 }
             } else {
                 Session::flash('message', 'Invalid File Extension. supported extensions are ' . implode(', ', $valid_extension));
             }
         } else {
             Session::flash('message', 'No file found.');
         }
 
         return redirect()->back();
     }
     
     
    public function bookshelveOffice($id): JsonResponse
    {
       $data = Bookshelve::where('office_id',$id)->get();
       if (count($data)==0) {
                return response()->json(['error'=>true, 'resp'=>'No data found']);
       } else {
                return response()->json(['error'=>false, 'resp'=>'Bookshelves List','data'=>$data]);
       } 
    }
    
    //book issue list
    public function bookIssueList(Request $request,$id)
    {
       $issueDateFrom = $request->input('issue_date_from');
       $issueDateTo = $request->input('issue_date_to');
       $query = IssueBook::where('book_id',$id);
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
       $book=Book::where('id',$id)->first();
       return view('lms.book.book-issue-detail',compact('data','book','request'));
       
       
    }
    
    //book issue list export csv
     public function bookIssuecsvExport(Request $request)
	{
		$bookData=Book::where('id',$id)->first();
        $issueDateFrom = $request->input('issue_date_from');
        $issueDateTo = $request->input('issue_date_to');
        $query = IssueBook::where('book_id',$id);
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
        $data = $query->cursor();
        $book = $data->all();
        if (count($book) > 0) {
            $delimiter = ","; 
            $filename = "issue-lists of-".$bookData->title.".csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
            $fields = array('SR', 'Member Name','Member Mobile','Member Email','Issue request date','Issued date','Returned date','Remarks'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($book as $row) {
                $datetime = date('j F, Y', strtotime($row['approve_date']));
				 $transfer=\App\Model\BookTransfer::where('book_id',$id)->where('from_user_id',$row->user_id)->with('toUser')->first();      
                if($row->return_date==NUll && !empty($transfer)){
                        $value= 'Transfer to '.$transfer->toUser->name;
                                    
                }
                $lineData = array(
                    $count,
					$item->user->name ?? 'NA',
                    $item->user->mobile  ?? 'NA',
					$item->user->email ?? 'NA',
					$item->request_date ?? 'NA',
					$item->approve_date ?? 'NA',
					$item->return_date ?? 'NA',
				    $value
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
    
    //bookshelf details with id
    public function bookshelveDetail($id): JsonResponse
    {
       $data = Bookshelve::where('id',$id)->first();
       if (!$data) {
                return response()->json(['error'=>true, 'resp'=>'No data found']);
       } else {
                return response()->json(['error'=>false, 'resp'=>'Bookshelves List','data'=>$data]);
       } 
    }
    
    
    //total available books per office
    public function availableBookListOffice(Request $request,$id): View
    {
       $data = Book::select('books.*')->with('bookshelves')->leftjoin('issue_books', 'issue_books.book_id', '=', 'books.id')->join('offices', 'offices.id', '=', 'books.office_id')
                                  ->where(function($query) {
                                        $query->whereNull('issue_books.book_id')
                                              ->orWhere('issue_books.is_return',1)->orWhere('issue_books.status',0);
                                    })
                                  
                                  ->where('books.office_id',$id)
                                   ->distinct()
                                  ->paginate(25);
                                 // dd($data);
       $office=Office::where('id',$id)->first();
       $category=BookCategory::all();
       return view('lms.book.book-office-detail',compact('data','office','category','request'));
       
       
    }
    
    
     //total issue books per office
    public function issueBookListOffice(Request $request,$id): View
    {
       $data =Book::select('books.*')->with('bookshelves')
                                    ->join('issue_books', 'issue_books.book_id', '=', 'books.id')
                                    ->join('offices', 'offices.id', '=', 'books.office_id')
                                    ->where(function($query) {
                                        $query->whereNull('issue_books.is_return')
                                             ;
                                    })
                                    
                                    ->where('books.office_id',$id)
                                    ->distinct()
                                    ->get();
                                 // dd($data);
       $office=Office::where('id',$id)->first();
       $category=BookCategory::all();
       return view('lms.book.book-issue-office-detail',compact('data','office','category','request'));
       
       
    }
    
    //unreturned book list
     public function unreturnedBookList(Request $request): View
    {
       $data = IssueBook::select('books.*','users.name','users.mobile','issue_books.approve_date')->join('books', 'issue_books.book_id', '=', 'books.id')->join('users', 'issue_books.user_id', '=', 'users.id')->where('issue_books.status',1)->whereNotNull('issue_books.book_id')->whereNull('issue_books.is_return')->with('bookshelves')->paginate(25);
       $category=BookCategory::all();
       $office=Office::all();
       return view('lms.book.unreturn',compact('data','category','office','request'));
       
       
    }
    
    
     //unreturned book list csv export
    
    public function unreturnedBookcsvExport(Request $request)
	{
		$keyword = $request->input('keyword');
        $officeId = $request->input('office_id');
        $bookshelve=$request->input('bookshelves_id');
        $cat= $request->input('category_id');
        $query = IssueBook::select('books.*','users.name','users.mobile','issue_books.approve_date')->join('books', 'issue_books.book_id', '=', 'books.id')->join('users', 'issue_books.user_id', '=', 'users.id')->where('issue_books.status',1)->whereNotNull('issue_books.book_id')->whereNull('issue_books.is_return')->with('bookshelves');
        // Apply keyword search (e.g., search by book title, user name, or other fields)
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'LIKE', "%{$query}%")
                      ->orWhere('author', 'LIKE', "%{$query}%")
                      ->orWhere('uid', 'LIKE', "%{$query}%")
                      ->orWhere('publisher', 'LIKE', "%{$query}%")
                      ->orWhere('edition', 'LIKE', "%{$query}%")
                      ->orWhere('page', 'LIKE', "%{$query}%")
                      ->orWhere('quantity', 'LIKE', "%{$query}%");
            });
        }
    
        // Apply filters based on inputs
        if (!empty($officeId)) {
            $query->where('books.office_id', $officeId);
        }
    
        if (!empty($bookshelve)) {
            $query->where('books.bookshelves_id', $bookshelve);
        }
    
        if (!empty($cat)) {
            $query->where('books.category_id', $cat);
        }

        // Get the paginated results
        $data = $query->cursor;
        $book = $data->all();
        if (count($book) > 0) {
            $delimiter = ","; 
            $filename = "unreturned-books.csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
            $fields = array('SR', 'Office','Office Location','Bookshelf Number','Category','Title','UID','Author','Member Name','Member Mobile','Issued Date'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($book as $row) {
                $datetime = date('j F, Y', strtotime($row['approve_date']));
				

                $lineData = array(
                    $count,
					$row['office']['name'] ?? 'NA',
                    $row['office']['address'] ?? 'NA',
					$row->bookshelves->number ?? 'NA',
					$row->category->name ?? 'NA',
					$row->title ?? 'NA',
					$row->uid ?? 'NA',
					$row->author ?? 'NA',
					$row->name ?? 'NA',
					$row->mobile ?? 'NA',
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
}
