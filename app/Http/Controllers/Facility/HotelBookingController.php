<?php

namespace App\Http\Controllers\Facility;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HotelBooking;
use Illuminate\View\View;
use App\Models\User;
use App\Models\MailActivity;
use App\Models\MatterCode;
use Carbon\Carbon;
use App\Models\Property;
use DB;
use Auth;
use Illuminate\Support\Str;
class HotelBookingController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:view hotel booking|hotel booking list csv export', ['only' => ['index']]);
         $this->middleware('permission:hotel booking details|hotel booking status update', ['only' => ['show']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): View
    {
        $keyword = $request->input('keyword');
        $issueDateFrom = $request->input('date_from');
        $issueDateTo = $request->input('date_to');
        $billTo = $request->input('bill_to');
        // Start the query
        // $query = HotelBooking::latest('id');
        $query = HotelBooking::with(['user', 'property', 'matter'])->latest('id');

        
        // Apply the keyword search conditions
        if (!empty($keyword)) {
            $query->where(function ($query) use ($keyword) {
                $query->where('matter_code', 'LIKE', "%$keyword%")
                      ->orWhere('hotel_type', 'LIKE', "%$keyword%")
                      ->orWhere('text', 'LIKE', "%$keyword%")
                      ->orWhere('guest_number', 'LIKE', "%$keyword%")
                      ->orWhere('room_number', 'LIKE', "%$keyword%")
                      ->orWhere('guest_type', 'LIKE', "%$keyword%")
                      ->orWhere('checkout_date', 'LIKE', "%$keyword%")
                      ->orWhere('bill_to', 'LIKE', "%$keyword%")
                      ->orWhereHas('user', function ($query) use ($keyword) {
                          $query->where('name', 'LIKE', "%$keyword%");
                      })
                      ->orWhereHas('property', function ($query) use ($keyword) {
                          $query->where('name', 'LIKE', "%$keyword%");
                      });
            });
        }
    
        // Apply the bill_to filter based on the keyword input (company, client, etc.)
        if (!empty($billTo)) {
            $query->where('bill_to', $billTo);
        }
    
        // Apply date range filter if both are provided
        if (!empty($issueDateFrom) && !empty($issueDateTo)) {
            $query->whereBetween('checkin_date', [
                Carbon::parse($issueDateFrom)->startOfDay(),
                Carbon::parse($issueDateTo)->endOfDay()
            ]);
        } 
        // Apply date filter if only 'date_from' is provided
        elseif (!empty($issueDateFrom)) {
            $query->whereDate('checkin_date', '>=', Carbon::parse($issueDateFrom)->startOfDay());
        } 
        // Apply date filter if only 'date_to' is provided
        elseif (!empty($issueDateTo)) {
            $query->whereDate('checkin_date', '<=', Carbon::parse($issueDateTo)->endOfDay());
        }
        $data = $query->paginate(25);
        return view('facility.hotel-booking.index',compact('data','request'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }
    
    
    
    
     public function csvExport(Request $request)
{
		 // Capture inputs
   $keyword = $request->input('keyword');
        $issueDateFrom = $request->input('date_from');
        $issueDateTo = $request->input('date_to');
        $billTo = $request->input('bill_to');
        // Start the query
        // $query = HotelBooking::latest('id');
        $query = HotelBooking::with(['user', 'property', 'matter'])->latest('id');

        
        // Apply the keyword search conditions
        if (!empty($keyword)) {
            $query->where(function ($query) use ($keyword) {
                $query->where('matter_code', 'LIKE', "%$keyword%")
                      ->orWhere('hotel_type', 'LIKE', "%$keyword%")
                      ->orWhere('text', 'LIKE', "%$keyword%")
                      ->orWhere('guest_number', 'LIKE', "%$keyword%")
                      ->orWhere('room_number', 'LIKE', "%$keyword%")
                      ->orWhere('guest_type', 'LIKE', "%$keyword%")
                      ->orWhere('checkout_date', 'LIKE', "%$keyword%")
                      ->orWhere('bill_to', 'LIKE', "%$keyword%")
                      ->orWhereHas('user', function ($query) use ($keyword) {
                          $query->where('name', 'LIKE', "%$keyword%");
                      })
                      ->orWhereHas('property', function ($query) use ($keyword) {
                          $query->where('name', 'LIKE', "%$keyword%");
                      });
            });
        }
    
        // Apply the bill_to filter based on the keyword input (company, client, etc.)
        if (!empty($billTo)) {
            $query->where('bill_to', $billTo);
        }
    
        // Apply date range filter if both are provided
        if (!empty($issueDateFrom) && !empty($issueDateTo)) {
            $query->whereBetween('checkin_date', [
                Carbon::parse($issueDateFrom)->startOfDay(),
                Carbon::parse($issueDateTo)->endOfDay()
            ]);
        } 
        // Apply date filter if only 'date_from' is provided
        elseif (!empty($issueDateFrom)) {
            $query->whereDate('checkin_date', '>=', Carbon::parse($issueDateFrom)->startOfDay());
        } 
        // Apply date filter if only 'date_to' is provided
        elseif (!empty($issueDateTo)) {
            $query->whereDate('checkin_date', '<=', Carbon::parse($issueDateTo)->endOfDay());
        }

    // Execute the query and paginate results
        $data = $query->cursor();
        $book = $data->all();
        if (count($book) > 0) {
            $delimiter = ","; 
            $filename = "hotel boking request.csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
            $fields = array('SR', 'Unique Code','Member','Property Type','Preferred Accommodation','Check In Date','Check In Time','Check Out','Guest Type','Guest Number','Food Preference','Bill to','Matter Code','Purpose','Description','Creation Date'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($book as $row) {
                $datetime = date('d-m-Y', strtotime($row['created_at']));
				if($row->hotel_type==1){
                         $type='Guest House';
                                   } else{
                                $type='Hotel';
                                   }    
                if($row->hotel_type==1){
                 $proname= $row->property->name ;
               } else{
                   $proname=$row->text;
               }
                                    
                                   

                $lineData = array(
                    $count,
                    $row->order_no,
					$row['user']['name'] ?? 'NA',
                    $type ?? 'NA',
					$proname ?? 'NA',
					$row->checkin_date ?? 'NA',
					$row->checkin_time ?? 'NA',
					$row->checkout_date ?? 'NA',
				    $row->guest_type ?? 'NA',
				    $row->guest_number ?? 'NA',
				    $row->food_preference ?? 'NA',
					$row->bill_to == 1 ? 'Firm' : ($row->bill_to == 2 ? 'Third Party' : 'Matter Expenses') ?? 'NA',
					$row->matter_code ?? 'NA',
					$row->purpose ?? 'NA',
					$row->description ?? 'NA',
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
	
	
		public function show(Request $request,$id): View
	{
	    
	    $data=HotelBooking::where('id',$id)->first();
	    return view('facility.hotel-booking.view', compact('data', 'request'));
	}
	
	
	public function status(Request $request, $id, $status)
    {
        $booking = HotelBooking::findOrFail($id);
    
        // Prevent status change if already cancelled
        if ($booking->status == 4) {
            return redirect()->back()->with('failure', 'This booking has already been cancelled and cannot be updated.');
        }
        if($booking->status == 3 && $status != 4){
            return redirect()->back()->with('failure', 'Accommodation has already been booked and can only be cancelled.');
        }
        // If cancelling, ensure remarks are provided
        if ($status == 4) {
            $now = Carbon::now();
            $pickupDateTime = Carbon::parse($booking['checkin_date']);

            if ($now->greaterThan($pickupDateTime->copy()->subHours(6))) {
                return redirect()->back()
                                ->with('failure','cancellations must be made at least 6 hours before the pickup time.');
                
            }else{
                $request->validate([
                    'remarks' => 'required|string|max:1000',
                ]);
        
                $booking->status = 4;
                $booking->cancellation_remarks = $request->remarks; // Make sure 'remarks' column exists in DB
                $booking->save();
            
                return redirect()->back()->with('success', 'Booking has been cancelled with remarks.');
            }
        }
    
        // For all other statuses
        $booking->status = $status;
        $booking->save();
    
        return redirect()->back()->with('success', 'Booking status updated.');
    }

    
    
    public function edit(Request $request,$id): View
	{
	    
	    $data=HotelBooking::where('id',$id)->first();
	    $property=Property::where('is_available',1)->get();
	    return view('facility.hotel-booking.edit', compact('data', 'request','property'));
	}
	
	
	 public function update(Request $request,$id)
    {
       //dd($request->all());
    $orderData = HotelBooking::findOrFail($id);
    if (!$orderData) {
        return redirect()->back()
                        ->with('failure','Booking Data not found');
    }
    $now = Carbon::now();
    $currentHour = (int)$now->format('H');

    $today = Carbon::today();
    $pickupDate = Carbon::parse($orderData->checkin_date);
    if ($pickupDate->lessThan($today)) {
        return redirect()->back()->with('failure', 'Booking cannot be edited. CheckIn date is before today.');
    }
    // ⛔ Restrict edits/cancellations less than 6 hours before pickup time
    $pickupDateTime = Carbon::parse($orderData['checkin_date']);

    if ($now->greaterThan($pickupDateTime->copy()->subHours(6))) {
        return redirect()->back()
                        ->with('failure','Edits or cancellations must be made at least 6 hours before the checkin time.');
        
    }

   if ($orderData->status==4) {
        return redirect()->back()
                        ->with('failure','Booking already cancelled, cannot be edited.');
    }

    // create matter code if not available
        $matterCodeInput = $request->matter_code;
        $matterId = null;

        if ($matterCodeInput) {
            $user = $orderData->user; 
            $clientName = $user->name ?? 'Unknown';

            $matter = MatterCode::firstOrCreate(
                ['matter_code' => $matterCodeInput], 
                ['client_name' => $clientName]
            );

            $matterId = $matter->id;
        }


    // ✅ Update flow
    $newData = [
       'bill_to' => $request['bill_to'],
        'checkin_date' => date('d-m-Y', strtotime($request['checkin_date'])),
        'checkin_time' => $request['checkin_time'],

        'checkout_date' => date('d-m-Y H:i:s', strtotime($request->checkout_date)) ?? null,
        
         'guest_type' => $request['guest_type'],
        'hotel_type' => $request['hotel_type'],
        'property_id' => $request['property_id'],
        'text' => $request['text'],
        'guest_number' => $request['guest_number'],
        'food_preference' =>$request->food_preference ?? null,
        'matter_code' => $matterId,
        'purpose' => $request->purpose ?? null,
        'description' => $request->description ?? null,
        'updated_at' => now()
    ];
    
     // Compare and insert into log
    foreach ($newData as $field => $newValue) {
        $oldValue = $orderData->$field ?? null;

        if ($newValue != $oldValue) {
            DB::table('edit_logs')->insert([
                'table_name' => 'hotel_bookings',
                'record_id' => $request->id,
                'field' => $field,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'updated_by' => Auth::user()->id,
                'created_at' => now()
            ]);
        }
    }

    DB::table('hotel_bookings')->where('id', $id)->update($newData);
    
    $booking=HotelBooking::findOrFail($id);
    
    $user=User::where('id',$booking->user_id)->first();
            $email_data = [
                'name' => $user->name,
                'subject' => 'REVISED: Accommodation Booking Request # '.$booking->order_no,
                'email' => $user->email,
                'hotelBooking' => $booking,
               
                'blade_file' => 'mail/hotel-booking-mail',
            ];
               /* $mailLog = MailActivity::create([
                    'email' => $user->email,
                    'type' => 'hotel-booking-edit-information-sent',
                    'sent_at' => now(),
                    'status' => 'pending',
                ]);
                try {
                     //$ccEmail = ['admin@foxandmandal.co.in','pintu.chakraborty@foxandmandal.co.in','sumit.dey@foxandmandal.co.in','amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
                     //$bccEmail = ['amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
                     $ccEmail = ['malik.priya123456@gmail.com'];
                     $bccEmail=['koushik@techmantra.co'];
                    // Send email
                     SendMail($email_data,$ccEmail, $bccEmail);
            
                    // Update the log status to "sent" on success
                    $mailLog->update(['status' => 'sent']);
            
                   
                } catch (\Exception $e) {
                     dd('Exception:', $e->getMessage());
                    // Update the log status to "failed" on error
                    $mailLog->update(['status' => 'failed']);
            
                    //return response()->json(['success' => false, 'message' => 'Failed to send email.'], 500);
                }*/
     return redirect()->back()
                        ->with('success','Hotel booking updated successfully.');
    
    }
    
    
     public function editLogs(Request $request): View
	{ 
	    $issueDateFrom = $request->input('date_from');
        $issueDateTo = $request->input('date_to');
	    
	    $query=DB::table('edit_logs');
	    if (!empty($issueDateFrom) && !empty($issueDateTo)) {
            $query->whereBetween('created_at', [
                Carbon::parse($issueDateFrom)->startOfDay(),
                Carbon::parse($issueDateTo)->endOfDay()
            ]);
        } 
        // Apply date filter if only 'date_from' is provided
        elseif (!empty($issueDateFrom)) {
            $query->whereDate('created_at', '>=', Carbon::parse($issueDateFrom)->startOfDay());
        } 
        // Apply date filter if only 'date_to' is provided
        elseif (!empty($issueDateTo)) {
            $query->whereDate('created_at', '<=', Carbon::parse($issueDateTo)->endOfDay());
        }
	    $data=$query->paginate(25);
	    return view('facility.hotel-booking.logs', compact('data', 'request'));
	}
	
	
	
	 public function editLogscsvExport(Request $request)
{
		 // Capture inputs
   $issueDateFrom = $request->input('date_from');
        $issueDateTo = $request->input('date_to');
	    
	    $query=DB::table('edit_logs');
	    if (!empty($issueDateFrom) && !empty($issueDateTo)) {
            $query->whereBetween('created_at', [
                Carbon::parse($issueDateFrom)->startOfDay(),
                Carbon::parse($issueDateTo)->endOfDay()
            ]);
        } 
        // Apply date filter if only 'date_from' is provided
        elseif (!empty($issueDateFrom)) {
            $query->whereDate('created_at', '>=', Carbon::parse($issueDateFrom)->startOfDay());
        } 
        // Apply date filter if only 'date_to' is provided
        elseif (!empty($issueDateTo)) {
            $query->whereDate('created_at', '<=', Carbon::parse($issueDateTo)->endOfDay());
        }
	    $data=$query->cursor();
        $book = $data->all();
        if (count($book) > 0) {
            $delimiter = ","; 
            $filename = "edit-logs.csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
            $fields = array('SR', 'Module','Record Details','Field','Previous Value','Updated Value','Updated Date','Updated By'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($book as $row) {
                if ($row->table_name && $row->record_id) {
                                        $modelClass = 'App\\Models\\' . ucfirst(Str::camel(Str::singular($row->table_name)));
                                
                                        if (class_exists($modelClass)) {
                                            $record = $modelClass::find($row->record_id);
                                            $row->record_details = $record;
                                           
                                        } else {
                                            $row->record_details = null;
                                        }
                                    }
                                    
                                    if ($row->updated_by && $row->updated_by) {
                                        $modelClass = 'App\\Models\User';
                                
                                        if (class_exists($modelClass)) {
                                            $record = $modelClass::find($row->updated_by);
                                            $row->user_details = $record;
                                           
                                        } else {
                                            $row->user_details = null;
                                        }
                                    }
                                    
                                   

                $lineData = array(
                    $count,
                    ucwords(str_replace('_', ' ',$row->table_name)) ??'',
					$row->record_details->order_no ?? $row->record_details->order_no ?? 'Details available',
                    $row->field  ?? 'NA',
					$row->old_value ?? 'NA',
					
					$row->new_value ?? 'NA',
				    date('d-m-Y', strtotime($row->created_at)),
				    $row->user_details->name ?? 'NA',
				    
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
