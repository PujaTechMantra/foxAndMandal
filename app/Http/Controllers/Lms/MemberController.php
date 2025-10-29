<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\IssueBook;
use App\Models\UserPermissionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use DB;
class MemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view member|member csv upload|member csv export|member status change|member issue list|give app permission', ['only' => ['index']]);
        $this->middleware('permission:create member', ['only' => ['create','store']]);
        $this->middleware('permission:update member', ['only' => ['update','edit']]);
        $this->middleware('permission:delete member', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $query = User::whereDoesntHave('roles');
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                      ->orWhere('mobile', 'LIKE', "%{$keyword}%")
                      ->orWhere('email', 'LIKE', "%{$keyword}%");
            });
        }
        $users = $query->paginate(25);
        return view('lms.member.index', ['users' => $users,'request'=>$request]);
    }

    public function create()
    {
        $user=DB::table('user_permission_categories')->join('users','users.id','=','user_permission_categories.user_id')->where('user_permission_categories.sub_cat', 'custodian')->get();
        return view('lms.member.create',compact('user'));
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'mobile' => 'required|unique:users,mobile',
            'type' => 'nullable',
             'image' => 'nullable|image|max:2048',
        ]);
        
         $path = public_path('uploads/member');
         $mediumInput = $request->input('medium', []);
         $name = is_array($mediumInput) ? array_unique($mediumInput) : [];
        function generateUniqueAlphaNumeric($length = 10) {
            $random_string = '';
            for ($i = 0; $i < $length; $i++) {
                $number = random_int(0, 36);
                $character = base_convert($number, 10, 36);
                $random_string .= $character;
            }
            return $random_string;
        }
        if (!file_exists($path)) {
                            mkdir($path, 0777, true); // Create the directory if it doesn't exist
                        }

                if ($request->hasFile('image')) {
                   $file=$request->file('image');
                        // Generate a unique filename
                        $fileName = $file->getClientOriginalName();
            
                        // Move the file to the 'public/uploads/cave' directory
                        $file->move($path, $fileName);
            
                        // Save the relative path for database storage
                        $uploadedPaths = 'uploads/member/' . $fileName;
                        
                       
                    
                }
                if($request->type=='authorized member'){
                    $qr=strtoupper(generateUniqueAlphaNumeric(10));
                }else{
                    $qr='';
                }
              $user=new User();
              $user->name=  $request->name;
              $user->email=  $request->email;
              $user->mobile=  $request->mobile;
              $user->designation=  $request->designation;
              $user->type=  $request->type;
              $user->qrcode=  $qr;
              $user->image=  $uploadedPaths ??'';
              
              $user->save();
               // Assign user permissions
                foreach ($name as $item) {
                    if (!is_string($item)) {
                        continue; // Ensure $item is a string
                    }
                     $sub_cat = ''; // Default value
                     $cus_user_id = 0;
                    if ($item === 'Lms') {
                        $sub_cat = $request->type ?? '';
                        
                    } else if ($item === 'Cave') {
                        $sub_cat = $request->cave_type ?? '';
                        $cus_user_id = $request->protem_details ?? 0;
                    }
                    DB::table('user_permission_categories')->updateOrInsert(
                    ['user_id' => $user->id, 'name' => $item],
                    ['sub_cat' => $sub_cat, 'cus_user_id' => $cus_user_id],
                    ['created_at' => now(), 'updated_at' => now()]
                    );
                    // $userPermission = new UserPermissionCategory([
                    //     'user_id' => $user->id,
                    //     'name' => $item, // This must be a string
                    // ]);
                
                    
               // dd($userPermission);
                    
                }
               // $userPermission->save();
        return redirect('/members')->with('success','Member created successfully');
    }

    public function edit($id)
    {
        $user=User::findOrfail($id);
        $userPer=DB::table('user_permission_categories')->join('users','users.id','=','user_permission_categories.user_id')->where('user_permission_categories.sub_cat', 'custodian')->get();
        return view('lms.member.edit', [
            'user' => $user,
            'userPer' => $userPer
        ]);
    }
    
    public function show($id)
    {
        $user=User::findOrfail($id);
        return view('lms.member.view', [
            'user' => $user
        ]);
    }

    public function update(Request $request, $id)
{
    //dd($request->all());
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'nullable|email',
        'mobile' => 'nullable|regex:/^[0-9]{10}$/',
        'type' => 'required|string',
        'image' => 'nullable|image|max:2048'
    ]);
     $name = array_unique($request->input('medium'));
    $user = User::find($id);

    // Generate unique QR code if required
    $qr = ($request->type === 'authorized member' && empty($user->qrcode)) 
        ? strtoupper($this->generateUniqueAlphaNumeric(10)) 
        : '';

    // File upload handling
    $uploadedPaths = null;
    if ($request->hasFile('image')) {
        $uploadDir = 'uploads/member';
        $path = public_path($uploadDir);

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $file = $request->file('image');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->move($path, $fileName);
        $uploadedPaths = $uploadDir . '/' . $fileName;
    }

    // Prepare data for update
    $data = [
        'name' => $request->name,
        'email' => $request->email,
        'mobile' => $request->mobile,
        'designation' => $request->designation,
        'type' => $request->type,
        'qrcode' => $qr,
    ];

    if ($uploadedPaths) {
        $data['image'] = $uploadedPaths;
    }

    $user->update($data);
    $cus_user_id=0;
    // First, remove existing permissions for this user
        DB::table('user_permission_categories')->where('user_id', $user->id)->delete();
        
        // Now, loop and insert new permissions
        foreach (array_unique($name) as $item) {
            if (!is_string($item)) {
                continue; // Skip non-strings
            }
        
            $sub_cat = '';
            $cus_user_id = 0;
        
            if ($item === 'Lms') {
                $sub_cat = $request->type ?? '';
            } elseif ($item === 'Cave') {
                $sub_cat = $request->cave_type ?? '';
                $cus_user_id = $request->protem_details ?? 0;
            }
        
            DB::table('user_permission_categories')->insert([
                'user_id' => $user->id,
                'name' => $item,
                'sub_cat' => $sub_cat,
                'cus_user_id' => $cus_user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

    return redirect('/members')->with('success', 'Member Updated Successfully');
}

// Helper function for QR code generation
private function generateUniqueAlphaNumeric($length = 10) {
    do {
        $random_string = '';
        for ($i = 0; $i < $length; $i++) {
            $number = random_int(0, 36);
            $character = base_convert($number, 10, 36);
            $random_string .= $character;
        }
    } while (User::where('qrcode', $random_string)->exists());

    return $random_string;
}

    public function destroy($userId)
    {
        $user = User::findOrFail($userId);
        $user->delete();

        return redirect('/members')->with('success','Member Delete Successfully');
    }
    
    public function status($id): RedirectResponse
    {
        $data = User::find($id);
        $status = ($data->status == 1) ? 0 : 1;
        $data->status = $status;
        $data->save();
    
        return redirect()->route('members.index')
                        ->with('success','Member status changed successfully');
    }
    
    
     //csv export
    
    public function csvExport(Request $request)
	{
		$keyword = $request->input('keyword');
        $query = User::whereDoesntHave('roles');
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                      ->orWhere('mobile', 'LIKE', "%{$keyword}%")
                      ->orWhere('email', 'LIKE', "%{$keyword}%");
            });
        }
        $users = $query->cursor();
        $book = $users->all();
        if (count($book) > 0) {
            $delimiter = ","; 
            $filename = "members.csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
            $fields = array('SR', 'Member Name','Email','Mobile','Status','DATE'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($book as $row) {
                $datetime = date('j F, Y h:i A', strtotime($row['created_at']));
				

                $lineData = array(
                    $count,
					$row['name'] ?? 'NA',
                    $row['email'] ?? 'NA',
					$row->mobile ?? 'NA',
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
                        
                        
                        
                         
                         $insertData = array(
                             
                             "name" => isset($importData[0]) ? $importData[0] : null,
                             "mobile" => isset($importData[1]) ? $importData[1] : null,
                             "email" => isset($importData[2]) ? $importData[2] : null,
                             "status" => 1
                         );
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
     
     //issue list
     
      //book issue list
    public function bookIssueList(Request $request,$id)
    {
        $issueDateFrom = $request->input('issue_date_from');
        $issueDateTo = $request->input('issue_date_to');
        $query = IssueBook::where('user_id',$id);
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
        $user=User::where('id',$id)->first();
       return view('lms.member.issue-list',compact('data','user','request'));
       
       
    }
    
    
         //unreturned book list csv export
    
    public function bookIssueListcsvExport(Request $request)
	{
		$user=User::where('id',$request->id)->first();
        $issueDateFrom = $request->input('issue_date_from');
        $issueDateTo = $request->input('issue_date_to');
        $query = IssueBook::where('user_id',$request->id);
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
            $filename = "issued-book-lists of-".$user->name.".csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
            $fields = array('SR', 'Office','Office Location','Bookshelf Number','Category','Title','Uid','Author','Issue request date','Issued date','Returned date','Remarks'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($book as $row) {
                $datetime = date('j F, Y', strtotime($row['approve_date']));
				 $transfer=\App\Model\BookTransfer::where('book_id',$row->book_id)->where('from_user_id',$row->user_id)->with('toUser')->first();      
                if($row->return_date==NUll && !empty($transfer)){
                        $value= 'Transfer to '.$transfer->toUser->name;
                                    
                }
                $lineData = array(
                    $count,
					$row->book->office->name ?? 'NA',
                    $row->book->office->address ?? 'NA',
					$row->book->bookshelves->number ?? 'NA',
					$row->book->category->name ?? 'NA',
					$row->book->title ?? 'NA',
					$row->book->uid ?? 'NA',
					$row->book->author ?? 'NA',
					$row->request_date ?? 'NA',
					$row->approve_date ?? 'NA',
					$row->return_date ?? 'NA',
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
	
	//give access
	public function getPermissionsAndMembers(Request $request,$id)
    {
        //dd($request->all());
       $request->validate([
            'name' => 'required|array',
            'id' => 'required|exists:users,id', // Ensure the user exists
        ]);
        $name = array_unique($request->input('name'));
       //dd($name);
        // Step 1: Remove records that are not in the provided name array
            DB::table('user_permission_categories')
                ->where('user_id', $id)
                
                ->delete();
        
            // Step 2: Insert or update new records
            foreach ($name as $item) {
                DB::table('user_permission_categories')->updateOrInsert(
                    ['user_id' => $id, 'name' => $item],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }

        
        //}
            //return redirect()->back()->with('success' => true, 'message' => 'Access created successfully.');
            return redirect('/members')->with('success','Access created successfully.');
        
    }
	
	
}
