<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Bookshelve;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Auth;
class BookshelveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:bookshelve list|bookshelve csv upload|bookshelve csv export', ['only' => ['index']]);
         $this->middleware('permission:create bookshelve', ['only' => ['create','store']]);
         $this->middleware('permission:update bookshelve', ['only' => ['edit','update']]);
         $this->middleware('permission:delete bookshelve', ['only' => ['destroy']]);
         $this->middleware('permission:view bookshelve', ['only' => ['show']]);
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
        $data = Bookshelve::where(function($q) use ($query, $officeId) {
                if ($query) {
                    $q->where('number', 'LIKE', "%{$query}%")
                      ->orWhere('manager', 'LIKE', "%{$query}%")->orWhere('area', 'LIKE', "%{$query}%");
                }
                if ($officeId) {
                    $q->where('office_id', $officeId);
                }
            })
            ->paginate(25);
        $office=Office::all();
        return view('lms.bookshelve.index',compact('data','office','request'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): View
    {
        $office=Office::all();
        return view('lms.bookshelve.create',compact('office'));
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
            'number' => 'required',
            'area' => 'nullable',
            'manager' => 'nullable',
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
        $data=new Bookshelve();
        $data->office_id=$request['office_id'];
        $data->user_id=Auth::user()->id;
        $data->number=$request['number'];
        $data->area=$request['area'];
        $data->manager=$request['manager'];
        $data->qrcode=strtoupper(generateUniqueAlphaNumeric(10));
        $data->save();
        return redirect()->route('bookshelves.index')
                        ->with('success','Bookshelve created successfully.');
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($id): View
    {
        $data = Bookshelve::find($id);
        return view('lms.bookshelve.view',compact('data'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id): View
    {
        $data = Bookshelve::find($id);
        $office=Office::all();
        return view('lms.bookshelve.edit',compact('data','office'));
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
    
        $data = Bookshelve::find($id);
        $data->office_id=$request['office_id'];
        $data->number=$request['number'];
        $data->area=$request['area'];
        $data->manager=$request['manager'];
        $data->save();
    
        return redirect()->route('bookshelves.index')
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
        $data = Bookshelve::find($id);
        $data->delete();
    
        return redirect()->route('bookshelves.index')
                        ->with('success','Bookshelve deleted successfully');
    }
    
    
    //csv export
    
    public function csvExport(Request $request)
	{
		$query = $request->input('keyword');
        $officeId = $request->input('office_id');
        $data = Bookshelve::where(function($q) use ($query, $officeId) {
                if ($query) {
                    $q->where('number', 'LIKE', "%{$query}%")
                      ->orWhere('manager', 'LIKE', "%{$query}%")->orWhere('area', 'LIKE', "%{$query}%");
                }
                if ($officeId) {
                    $q->where('office_id', $officeId);
                }
            })
            ->cursor();
        $book = $data->all();
        if (count($book) > 0) {
            $delimiter = ","; 
            $filename = "bookshelves.csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
            $fields = array('SR', 'Office','Office Location','Office Area','Bookshelf Number','Manager','Created By','DATE'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($book as $row) {
                $datetime = date('j F, Y h:i A', strtotime($row['created_at']));
				

                $lineData = array(
                    $count,
					$row['office']['name'] ?? 'NA',
                    $row['office']['address'] ?? 'NA',
					$row->area ?? 'NA',
					$row->number ?? 'NA',
					$row->manager ?? 'NA',
					$row->user->name ?? 'NA',
					
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
                        //foreach ($importData[0] as $cateKey => $catVal) {
                            $catExistCheck = Office::where('name', $importData[0])->where('address',$importData[1])->first();
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
                        //}
                          
                         $insertData = array(
                             "office_id" => $officeData? $officeData : null,
                             "user_id" => Auth::user()->id,
                             "number" => isset($importData[2]) ? $importData[2] : null,
                             //"area" => isset($importData[2]) ? $importData[2] : null,
                             //"manager" => isset($importData[3]) ? $importData[3] : null,
                             "qrcode" => strtoupper(generateUniqueAlphaNumericValue(10)),
                         );
                         $resp = Bookshelve::insertData($insertData, $successCount);
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
}
