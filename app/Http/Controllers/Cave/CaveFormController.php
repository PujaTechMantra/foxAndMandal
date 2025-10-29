<?php

namespace App\Http\Controllers\Cave;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CaveForm;
use App\Models\CaveLocation;
use App\Models\CaveCategory;
use App\Models\CaveDoc;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use DB;
class CaveFormController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:view cave form|take out list|vault take out list csv download|vault csv download|outside vault csv export', ['only' => ['index']]);
         $this->middleware('permission:create cave form', ['only' => ['create','store']]);
         $this->middleware('permission:update cave form', ['only' => ['edit','update']]);
         $this->middleware('permission:delete cave form', ['only' => ['destroy']]);
         ;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    // Validate the request
    $request->validate([
        'keyword' => 'nullable|string|min:1',
        'issue_date_from' => 'nullable|date',
        'issue_date_to' => 'nullable|date',
    ]);

    $keyword = $request->input('keyword');
    $issueDateFrom = $request->input('issue_date_from');
    $issueDateTo = $request->input('issue_date_to');

    $data = CaveForm::with(['takeOut.user', 'location', 'category'])
        ->when($keyword, function ($query, $keyword) {
            $query->where(function ($query) use ($keyword) {
                $query->where('client_name', 'LIKE', "%$keyword%")
                    ->orWhere('remarks', 'LIKE', "%$keyword%")
                    ->orWhere('room', 'LIKE', "%$keyword%")
                    ->orWhere('sub_location', 'LIKE', "%$keyword%")
                    ->orWhere('description', 'LIKE', "%$keyword%")
                    ->orWhere('name', 'LIKE', "%$keyword%")
                    ->orWhere('movement', 'LIKE', "%$keyword%");
            })
            ->orWhereHas('location', function ($query) use ($keyword) {
                $query->where('location', 'LIKE', "%$keyword%");
            })
            ;
        })
        ->when($issueDateFrom && $issueDateTo, function ($query) use ($issueDateFrom, $issueDateTo) {
            $query->whereBetween('created_at', [
                Carbon::parse($issueDateFrom)->startOfDay(),
                Carbon::parse($issueDateTo)->endOfDay(),
            ]);
        })
        ->when($issueDateFrom && !$issueDateTo, function ($query) use ($issueDateFrom) {
            $query->whereDate('created_at', '>=', Carbon::parse($issueDateFrom)->startOfDay());
        })
        ->when(!$issueDateFrom && $issueDateTo, function ($query) use ($issueDateTo) {
            $query->whereDate('created_at', '<=', Carbon::parse($issueDateTo)->endOfDay());
        })
        ->latest('id')
        ->paginate(25);

    // Return the results to the view
    return view('cave.form.index', compact('data','request'))
        ->with('i', (request()->input('page', 1) - 1) * 5);
}

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $location=CaveLocation::all();
        $category=CaveCategory::all();
        $custodian=DB::table('user_permission_categories')->join('users','users.id','=','user_permission_categories.user_id')->where('user_permission_categories.sub_cat', 'custodian')->get();
        return view('cave.form.create',compact('location','request','category','custodian'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        request()->validate([
            'location_id' => 'required',
             'document' => 'nullable|file|max:2048',
            //'category_id' => 'required',
            //'client_name' => 'required',
        ]);
        $uploadedPaths = [];
         $path = public_path('uploads/cave');
         function generateUniqueAlphaNumeric($length = 10) {
            $random_string = '';
            for ($i = 0; $i < $length; $i++) {
                $number = random_int(0, 36);
                $character = base_convert($number, 10, 36);
                $random_string .= $character;
            }
            return $random_string;
        }
            $data=new CaveForm();
            $data->location_id=$request['location_id'];
            $data->custodian_id = $request->input('custodian_id');
            $data->client_name=$request['client_name']??'';
            $data->unique_code=$request['unique_code']??'';
            $data->remarks=$request['remarks']??'';
            $data->movement=$request['movement']??'';
            $data->room=$request['room']??'';
            //$data->name=$request['name']??'';
            $data->sub_location=$request['sub_location']??'';
            $data->description=$request['description']??'';
            $data->qrcode=strtoupper(generateUniqueAlphaNumeric(10));
            if (!file_exists($path)) {
                            mkdir($path, 0777, true); // Create the directory if it doesn't exist
                        }

                if ($request->hasFile('document')) {
                    foreach ($request->file('document') as $file) {
                        // Generate a unique filename
                        $fileName = $file->getClientOriginalName();
            
                        // Move the file to the 'public/uploads/cave' directory
                        $file->move($path, $fileName);
            
                        // Save the relative path for database storage
                        $uploadedPaths[] = 'uploads/cave/' . $fileName;
                        $commaSeparatedPaths = implode(',', $uploadedPaths);
                        $data->document=$commaSeparatedPaths;
                    }
                }

                // Convert paths to a comma-separated string
                
                
               $data->save();
        
        return redirect()->route('vaults.index')
                        ->with('success','Vault created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $data = CaveForm::find($id);
        return view('cave.form.view',compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $data = CaveForm::find($id);
        $location=CaveLocation::all();
        $category=CaveCategory::all();
        $custodian=DB::table('user_permission_categories')->join('users','users.id','=','user_permission_categories.user_id')->where('user_permission_categories.sub_cat', 'custodian')->get();
        return view('cave.form.edit',compact('data','location','category','custodian'));
    }

    /**
     * Update the specified resource in storage.
     */
public function update(Request $request, $id): RedirectResponse
{
    // Validate the input
    $request->validate([
        'location_id' => [
            'required'
        ],
          'document' => 'nullable|file|max:2048',
    ]);

    $data = CaveForm::findOrFail($id); // Find the record or throw a 404 error if not found

    // Update fields
    $data->location_id = $request->input('location_id');
    $data->custodian_id = $request->input('custodian_id');
    $data->client_name = $request->input('client_name', '');
    $data->room = $request->input('room', '');
   // $data->name = $request->input('name', '');
    $data->movement=$request->input('movement', '');
     $data->remarks=$request->input('remarks', '');
    $data->sub_location = $request->input('sub_location', '');
    $data->description = $request->input('description', '');
    $data->unique_code=$request['unique_code']??'';
    // File upload path
    $uploadDir = 'uploads/cave'; // Define the relative directory
    $path = public_path($uploadDir);

    // Create the directory if it doesn't exist
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }

    // Handle existing document paths
    $existingPaths = $data->document ?? '';
    $existingPathsArray = !empty($existingPaths) ? explode(',', $existingPaths) : [];

    // Process uploaded files
    $uploadedPaths = [];
    if ($request->hasFile('document')) {
        foreach ($request->file('document') as $file) {
            // Generate the file name
            $fileName = $file->getClientOriginalName();

            // Move the file to the upload directory
            $file->move($path, $fileName);

            // Save the relative file path
            $uploadedPaths[] = $uploadDir . '/' . $fileName;
        }
    }

    // Merge and update document paths
    $updatedPaths = array_merge($existingPathsArray, $uploadedPaths);
    $data->document = implode(',', $updatedPaths);

    // Save the updated record
    $data->save();

    // Redirect with a success message
    return redirect()->route('vaults.index')
        ->with('success', 'Vault updated successfully');
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): RedirectResponse
    {
        $data = CaveForm::find($id);
        $data->delete();
    
        return redirect()->route('vaults.index')
                        ->with('success','Vault deleted successfully');
    }
    
    
public function csvExport(Request $request)
{
		 // Capture inputs
    $keyword = $request->input('keyword');
    $issueDateFrom = $request->input('issue_date_from');
    $issueDateTo = $request->input('issue_date_to');
    //dd($issueDateFrom);
    // Eloquent query with relationships and search conditions
    $data = CaveForm::with(['takeOut.user', 'location', 'category'])
        ->when($keyword, function ($query, $keyword) {
            $query->where(function ($query) use ($keyword) {
                $query->where('client_name', 'LIKE', "%$keyword%")
                    ->orWhere('remarks', 'LIKE', "%$keyword%")
                    ->orWhere('room', 'LIKE', "%$keyword%")
                    ->orWhere('sub_location', 'LIKE', "%$keyword%")
                    ->orWhere('description', 'LIKE', "%$keyword%")
                    ->orWhere('name', 'LIKE', "%$keyword%")
                    ->orWhere('movement', 'LIKE', "%$keyword%");
            })
            ->orWhereHas('location', function ($query) use ($keyword) {
                $query->where('location', 'LIKE', "%$keyword%");
            })
            ->orWhereHas('category', function ($query) use ($keyword) {
                $query->where('name', 'LIKE', "%$keyword%");
            });
        })
        ->when($issueDateFrom && $issueDateTo, function ($query) use ($issueDateFrom, $issueDateTo) {
            $query->whereBetween('created_at', [
                Carbon::parse($issueDateFrom)->startOfDay(),
                Carbon::parse($issueDateTo)->endOfDay(),
            ]);
        })
        ->when($issueDateFrom && !$issueDateTo, function ($query) use ($issueDateFrom) {
            $query->whereDate('created_at', '>=', Carbon::parse($issueDateFrom)->startOfDay());
        })
        ->when(!$issueDateFrom && $issueDateTo, function ($query) use ($issueDateTo) {
            $query->whereDate('created_at', '<=', Carbon::parse($issueDateTo)->endOfDay());
        })
        ->latest('id')->cursor();

    // Execute the query and paginate results
       // $data = $query->cursor();
        $book = $data->all();
        if (count($book) > 0) {
            $delimiter = ","; 
            $filename = "vault list.csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
            $fields = array('SR', 'Unique Code','Location','Room','Sub Location','Particulars','Matter Code','Movement','Remarks','Date'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($book as $row) {
                $datetime = date('j F, Y h:i A', strtotime($row['created_at']));
				

                $lineData = array(
                    $count,
                    $row->unique_code,
					$row->location->location ?? 'NA',
                    $row['room'] ?? 'NA',
                    $row->sub_location?? 'NA',
					$row->description ?? 'NA',
					$row->client_name ?? 'NA',
				    strtoupper($row->movement) ?? 'NA',
					$row->remarks ?? 'NA',
					
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
    public function takeoutList(Request $request,$id)
    {
       $issueDateFrom = $request->input('issue_date_from');
       $issueDateTo = $request->input('issue_date_to');
       $query = CaveDoc::where('cave_form_id',$id)->with('request','user','user2','vault');
       if (!empty($issueDateFrom) && !empty($issueDateTo)) {
            $query->whereBetween('cave_docs.request_date', [
                Carbon::parse($issueDateFrom)->startOfDay(),
                Carbon::parse($issueDateTo)->endOfDay()
            ]);
        } elseif (!empty($issueDateFrom)) {
            $query->whereDate('issue_books.request_date', '>=', Carbon::parse($issueDateFrom)->startOfDay());
        } elseif (!empty($issueDateTo)) {
            $query->whereDate('issue_books.request_date', '<=', Carbon::parse($issueDateTo)->endOfDay());
        }
        // Get the paginated results
        $data = $query->paginate(25);
        $form=CaveForm::where('id',$id)->first();
       return view('cave.form.issue-detail',compact('data','form','request'));
       
       
    }
    
    
    
     public function takeoutListcsvExport(Request $request,$id)
	{
	    $bookData=CaveForm::where('id',$id)->first();
	   $issueDateFrom = $request->input('issue_date_from');
       $issueDateTo = $request->input('issue_date_to');
       $query = CaveDoc::where('cave_form_id',$id)->where('scan_status',1)->with('request','user','user2','vault');
       if (!empty($issueDateFrom) && !empty($issueDateTo)) {
            $query->whereBetween('cave_docs.created_at', [
                Carbon::parse($issueDateFrom)->startOfDay(),
                Carbon::parse($issueDateTo)->endOfDay()
            ]);
        } elseif (!empty($issueDateFrom)) {
            $query->whereDate('cave_docs.created_at', '>=', Carbon::parse($issueDateFrom)->startOfDay());
        } elseif (!empty($issueDateTo)) {
            $query->whereDate('cave_docs.created_at', '<=', Carbon::parse($issueDateTo)->endOfDay());
        }
        // Get the paginated results
        $data = $query->cursor();
        $book = $data->all();
        if (count($book) > 0) {
            $delimiter = ","; 
            $filename = "take-out-lists of ".$bookData->unique_code."-".$bookData->location->location.".csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
            $fields = array('SR','Cavity Unique Code','Cavity Location','Cavity Room','Cavity Custodian Name', 'Requested By','Requested Date','Request Sent To','Authorized Member Issued On','Request Accept Date by Member','Expected Return Date','Returned By','Returned Date','Remarks'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($book as $row) {
                 $returnDate=\App\Models\CaveDoc::where('cave_form_id',$row->id)->where('user_id',$row->user->id)->where('scan_status',1)->first();  
                                $expectedReturn = strtotime($row->expected_return_date);
                                $returnDate = strtotime($row->return_date);
                                
                                $overdueDays = 0;
                                if ($returnDate > $expectedReturn) {
                                    $overdueDays = ($returnDate - $expectedReturn) / (60 * 60 * 24);
                                }
                                if(!empty($row->request->user)){
                                $requestUser=$row->request->user->name;
                                }else{
                                     $requestUser='';
                                }
                                if($row->request){
                                    $requestDate=date('d-m-Y', strtotime($row->request->created_at));
                                }else{
                                    $requestDate='';
                                }
                                
                                if(!empty($row->request->custodian) || !empty($row->request->protem))
                                {
                                    $member=$row->request->custodian->name.','.$row->request->protem->name;
                                }else{
                                    $member='';
                                }
                                
                                if($row->status_for_requested_user==1){
                                    $statusDate=date('d-m-Y', strtotime($row->status_change_date));
                                }else{
                                    $statusDate=date('d-m-Y', strtotime($row->request_date));
                                }
                                
                                if(!empty($row->returnuser))
                                {
                                    $returnUser=$row->returnuser->name;
                                }else{
                                    $returnUser='';
                                }
                                
                                if($row->is_return==1){
                                    $isReturn=date('d-m-Y', strtotime( $row->return_date));
                                }else{
                                    $isReturn='';
                                }
                                
                                if($overdueDays==0){
                                
                                    $remarks='The document was returned on time';
                                }
                                else{
                                    $remarks='The document has been delayed by '. $overdueDays. ' days';
                                }
				
                $lineData = array(
                    $count,
					$row->vault->unique_code ?? 'NA',
                    $row->vault->location->location  ?? 'NA',
					$row->vault->room ?? 'NA',
					$row->vault->custodian->name ?? 'NA',
					$requestUser ?? 'NA',
					$requestDate ?? 'NA',
					$member ?? 'NA',
					date('d-m-Y', strtotime($row->request_date)) ?? 'NA',
					$statusDate ?? 'NA',
					date('d-m-Y', strtotime($row->expected_return_date)) ?? 'NA',
					$returnUser ?? 'NA',
					$isReturn ?? 'NA',
					$remarks ?? 'NA'
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
	
	
	
	 //unreturned vault list
     public function unreturnedVaultList(Request $request): View
{
    $request->validate([
        'keyword' => 'nullable|string|min:1',
    ]);

    $keyword = $request->keyword;
    $issueDateFrom = $request->issue_date_from;
    $issueDateTo = $request->issue_date_to;

    // Querying CaveDoc with required conditions
    $data = CaveDoc::with(['vault', 'vault.location'])
        ->join('cave_forms', 'cave_docs.cave_form_id', '=', 'cave_forms.id') // Join Cave Forms
        ->join('users', 'cave_docs.user_id', '=', 'users.id')               // Join Users
        ->leftJoin('cave_locations', 'cave_forms.location_id', '=', 'cave_locations.id') // Join Locations
        ->whereNull('cave_docs.is_return') // Ensure is_return is NULL
        ->where('cave_docs.scan_status', 1) // Ensure scan_status is 1
        ->when($keyword, function ($query) use ($keyword) {
            $query->where(function ($query) use ($keyword) {
                $query->where('cave_forms.client_name', 'LIKE', "%$keyword%")
                    ->orWhere('cave_forms.remarks', 'LIKE', "%$keyword%")
                    ->orWhere('cave_locations.location', 'LIKE', "%$keyword%");
            })
            ->orWhereHas('user', function ($query) use ($keyword) {
                $query->where('name', 'LIKE', "%$keyword%");
            })
            ->orWhereHas('vault.custodian', function ($query) use ($keyword) {
                $query->where('name', 'LIKE', "%$keyword%");
            });
        })
        ->when($issueDateFrom && $issueDateTo, function ($query) use ($issueDateFrom, $issueDateTo) {
            $query->whereBetween('cave_docs.created_at', [
                Carbon::parse($issueDateFrom)->startOfDay(),
                Carbon::parse($issueDateTo)->endOfDay(),
            ]);
        })
        ->when($issueDateFrom && !$issueDateTo, function ($query) use ($issueDateFrom) {
            $query->whereDate('cave_docs.created_at', '>=', Carbon::parse($issueDateFrom)->startOfDay());
        })
        ->when(!$issueDateFrom && $issueDateTo, function ($query) use ($issueDateTo) {
            $query->whereDate('cave_docs.created_at', '<=', Carbon::parse($issueDateTo)->endOfDay());
        })
        ->latest('cave_docs.id')
        ->paginate(25);

    return view('cave.form.unreturn', compact('data', 'request'))
        ->with('i', (request()->input('page', 1) - 1) * 5);
}

    
    
     //unreturned book list csv export
    
    public function unreturnedVaultListcsvExport(Request $request)
	{
		$keyword = $request->keyword;
        $issueDateFrom=$request->issue_date_from;
        $issueDateTo=$request->issue_date_to;
        // Eloquent query with relationships and search conditions
         $request->validate([
            'keyword' => 'nullable|string|min:1',
        ]);
    
        $keyword = $request->keyword;
        $issueDateFrom=$request->issue_date_from;
        $issueDateTo=$request->issue_date_to;
        // Eloquent query with relationships and search conditions
        $data = CaveDoc::with(['vault', 'vault.location'])
            ->join('cave_forms', 'cave_docs.cave_form_id', '=', 'cave_forms.id') // Join Cave Forms
            ->join('users', 'cave_docs.user_id', '=', 'users.id')               // Join Users
            ->leftJoin('cave_locations', 'cave_forms.location_id', '=', 'cave_locations.id') // Join Locations
            // Join Categories
            ->whereNull('cave_docs.is_return') // Ensure is_return is NULL
            ->where('cave_docs.scan_status', 1) 
            ->when($keyword, function ($query) use ($keyword) {
            $query->where(function ($query) use ($keyword) {
                $query->where('cave_forms.client_name', 'LIKE', "%$keyword%")
                    ->orWhere('cave_forms.remarks', 'LIKE', "%$keyword%")
                    ->orWhere('cave_locations.location', 'LIKE', "%$keyword%");
            })
            ->orWhereHas('user', function ($query) use ($keyword) {
                $query->where('name', 'LIKE', "%$keyword%");
            })
            ->orWhereHas('vault.custodian', function ($query) use ($keyword) {
                $query->where('name', 'LIKE', "%$keyword%");
            });
        })
        ->when($issueDateFrom && $issueDateTo, function ($query) use ($issueDateFrom, $issueDateTo) {
            $query->whereBetween('cave_docs.created_at', [
                Carbon::parse($issueDateFrom)->startOfDay(),
                Carbon::parse($issueDateTo)->endOfDay(),
            ]);
        })
        ->when($issueDateFrom && !$issueDateTo, function ($query) use ($issueDateFrom) {
            $query->whereDate('cave_docs.created_at', '>=', Carbon::parse($issueDateFrom)->startOfDay());
        })
        ->when(!$issueDateFrom && $issueDateTo, function ($query) use ($issueDateTo) {
            $query->whereDate('cave_docs.created_at', '<=', Carbon::parse($issueDateTo)->endOfDay());
        })
            ->whereNotNull('cave_docs.cave_form_id');
            

        // Get the paginated results
        $data = $query->get();
        //$book = $data->all();
        if (count($data) > 0) {
            $delimiter = ","; 
            $filename = "outside-vault-list.csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
            $fields = array('SR', 'Unique Code','Location','Custodian','Room','Particulars','Take out Date'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($data as $row) {
                //$datetime = date('j F, Y', strtotime($row['created_at_date']));
				
                if($datetime){
                                   $datetime =      date('d-m-Y', strtotime($row->status_change_date));
                                        }else{
                                        $datetime = date('d-m-Y', strtotime($row->request_date));
                                }
                                       
                $lineData = array(
                    $count,
					$row->unique_code ?? 'NA',
				    $item->vault->location->location?? '',
					$row->vault->custodian->name ?? 'NA',
					$row->room ?? 'NA',
					$row->description ?? 'NA',
					$datetime
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
