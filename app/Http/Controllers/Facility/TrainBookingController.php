<?php

namespace App\Http\Controllers\Facility;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrainBooking;
use App\Models\User;
use App\Models\MatterCode;
use App\Models\MailActivity;
use Illuminate\View\View; 
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Auth;
class TrainBookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:view train booking|train booking list csv export', ['only' => ['index']]);
         $this->middleware('permission:train booking details|train/bus booking status update', ['only' => ['show']]);
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
    // $query = TrainBooking::latest('id');
    $query = TrainBooking::with(['user', 'matter'])->latest('id');
    
    // Apply the keyword search conditions
    if (!empty($keyword)) {
        $query->where(function ($query) use ($keyword) {
            $query->where('matter_code', 'LIKE', "%$keyword%")
                  ->orWhere('from', 'LIKE', "%$keyword%")
                  ->orWhere('to', 'LIKE', "%$keyword%")
                  ->orWhere('departure_date', 'LIKE', "%$keyword%")
                  ->orWhere('return_date', 'LIKE', "%$keyword%")
                  ->orWhere('arrival_time', 'LIKE', "%$keyword%")
                  ->orWhere('traveller', 'LIKE', "%$keyword%")
                  ->orWhere('bill_to', 'LIKE', "%$keyword%")
                  ->orWhereHas('user', function ($query) use ($keyword) {
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
        $query->whereBetween('departure_date', [
            Carbon::parse($issueDateFrom)->startOfDay(),
            Carbon::parse($issueDateTo)->endOfDay()
        ]);
    } 
    // Apply date filter if only 'date_from' is provided
    elseif (!empty($issueDateFrom)) {
        $query->whereDate('departure_date', '>=', Carbon::parse($issueDateFrom)->startOfDay());
    } 
    // Apply date filter if only 'date_to' is provided
    elseif (!empty($issueDateTo)) {
        $query->whereDate('departure_date', '<=', Carbon::parse($issueDateTo)->endOfDay());
    }
        $data = $query->paginate(25);
        return view('facility.train-booking.index',compact('data','request'))
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
    // $query = TrainBooking::latest('id');
    $query = TrainBooking::with(['user', 'matter'])->latest('id');

    
    // Apply the keyword search conditions
    if (!empty($keyword)) {
        $query->where(function ($query) use ($keyword) {
            $query->where('matter_code', 'LIKE', "%$keyword%")
                  ->orWhere('from', 'LIKE', "%$keyword%")
                  ->orWhere('to', 'LIKE', "%$keyword%")
                  ->orWhere('departure_date', 'LIKE', "%$keyword%")
                  ->orWhere('return_date', 'LIKE', "%$keyword%")
                  ->orWhere('arrival_time', 'LIKE', "%$keyword%")
                  ->orWhere('traveller', 'LIKE', "%$keyword%")
                  ->orWhere('bill_to', 'LIKE', "%$keyword%")
                  ->orWhereHas('user', function ($query) use ($keyword) {
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
        $query->whereBetween('departure_date', [
            Carbon::parse($issueDateFrom)->startOfDay(),
            Carbon::parse($issueDateTo)->endOfDay()
        ]);
    } 
    // Apply date filter if only 'date_from' is provided
    elseif (!empty($issueDateFrom)) {
        $query->whereDate('departure_date', '>=', Carbon::parse($issueDateFrom)->startOfDay());
    } 
    // Apply date filter if only 'date_to' is provided
    elseif (!empty($issueDateTo)) {
        $query->whereDate('departure_date', '<=', Carbon::parse($issueDateTo)->endOfDay());
    }

    // Execute the query and paginate results
        $data = $query->cursor();
        $book = $data->all();
        if (count($book) > 0) {
            $delimiter = ","; 
            $filename = "train/bus boking request.csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
            $fields = array('SR', 'Unique Code','Member','Type','From','To','Trip Type','Travel Date','Travel Time','Return Date', 'Return Time','Traveller','Seat Preference','Food Preference','Bill to','Matter Code','Purpose','Description','Creation Date'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($book as $row) {
                $datetime = date('d-m-Y', strtotime($row['created_at']));
				if($row->trip_type == 2){
                                    $returnDate= $row->return_date;
                                    $returnTime= $row->return_time;
                }else{
                    $returnDate='';
                    $returnTime='';
                }
                 $travellers = explode(',', $row->traveller ?? '');
                $seatPreferences = explode(',', $row->seat_preference ?? '');
                $foodPreferences = explode(',', $row->food_preference ?? '');
            
                $formattedTravellers = [];
                        foreach ($travellers as $i => $traveller) {
                            if (trim($traveller) !== '') {
                                $formattedTravellers[] = [
                                    'name' => trim($traveller),
                                    'seat_preference' => $seatPreferences[$i] ?? 'N/A',
                                    'food_preference' => $foodPreferences[$i] ?? 'N/A',
                                ];
                            }
                        }
                if (count($formattedTravellers) > 0) {
                    foreach ($formattedTravellers as $key => $traveller) {
                        $lineData = [
                            $key === 0 ? $count :'',
                            $key === 0 ? $row->order_no :'',
        					$key === 0 ? $row['user']['name'] :'',
        					$key === 0 ? $row->type == 1 ? 'Train' : 'Bus' :'',
                            $key === 0 ?$row['from'] :'',
        					$key === 0 ?$row->to :'',
        					$key === 0 ?$row->trip_type == 1 ? 'One way' : ($row->trip_type == 2 ? 'Round Trip': '') :'',
        					$key === 0 ?$row->travel_date :'',
                            $key === 0 ?$row->departure_time :'',
        					$key === 0 ?$returnDate :'',
        					$key === 0 ?$returnTime :'',
        					$traveller['name'] ?? 'N/A',
                            $traveller['seat_preference'] ?? 'N/A', // Assign correct seat preference
                       
                            $traveller['food_preference'] ?? 'N/A',
        					$key === 0 ?$row->bill_to == 1 ? 'Firm' : ($row->bill_to == 2 ? 'Third Party' : 'Matter Expenses') :'',
        					$key === 0 ?$row->matter_code :'',
        					$key === 0 ?$row->purpose :'',
        					$key === 0 ?$row->description :'',
        					$key === 0 ?$datetime:'',
                        ];
                        fputcsv($f, $lineData, $delimiter);
                    }
                }else{
                     $lineData = [
                        $count,
                        $row->order_no,
                        $row['user']['name'] ?? 'NA',
                        $row->type == 1 ? 'Train' : 'Bus',
                        $row['from'] ?? 'NA',
        				$row->to ?? 'NA',
        				$row->trip_type == 1 ? 'One way' : ($row->trip_type == 2 ? 'Round Trip': ''),
        				$row->travel_date ?? 'NA',
                        $row->departure_time ?? 'NA',
        				$returnDate ?? 'NA',
        				$returnTime ?? 'NA',
                        'N/A', // No traveller
                        'N/A', // No seat preference
                        'N/A', // No food preference
                        
                        $row->bill_to == 1 ? 'Firm' : ($row->bill_to == 2 ? 'Third Party' : 'Matter Expenses'),
                        $row->matter_code ?? 'NA',
                        $row->purpose ?? 'NA',
                        $row->description ?? 'NA',
                        $datetime,
                    ];
                    fputcsv($f, $lineData, $delimiter);
                }

                

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
	    
	    $data=TrainBooking::where('id',$id)->first();
	    return view('facility.train-booking.view', compact('data', 'request'));
	}
	
	
	public function status(Request $request,$id,$status)
    {
		$booking = TrainBooking::findOrFail($id);
    
        // Prevent status change if already cancelled
        if ($booking->status == 4) {
            return redirect()->back()->with('failure', 'This booking has already been cancelled and cannot be updated.');
        }
        if($booking->status == 3 && $status != 4){
            return redirect()->back()->with('failure', 'Ticket has already been booked and can only be cancelled.');
        }
        // If cancelling, ensure remarks are provided
        if ($status == 4) {
            $now = Carbon::now();
            $pickupDateTime = Carbon::parse($booking['travel_date']);

            if ($now->greaterThan($pickupDateTime->copy()->subHours(6))) {
                return redirect()->back()
                                ->with('failure','cancellations must be made at least 6 hours before the departure time.');
                
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
        if ($status == 3) {
            
                $request->validate([
                    'pnr' => 'required_if:type,1|string|max:1000',
                    'train_no' => 'required',
                    'date_time' => 'required',
                ]);
        
                $booking->status = 3;
                $booking->pnr = $request->pnr; 
                $booking->train_no = $request->train_no; // Make sure 'remarks' column exists in DB
                $booking->date_time = $request->date_time;

                $booking->save();
                
                
                $user=User::where('id',$booking->user_id)->first();
                if($booking->type==1){
                    $email_data = [
                        'name' => $user->name,
                        'subject' => 'Train Booking Confirmation # '.$booking->order_no,
                        'email' => $user->email,
                        'trainBooking' => $booking,
                       
                        'blade_file' => 'mail/train-booking-confirm-mail',
                    ];
                    /*$mailLog = MailActivity::create([
                        'email' => $user->email,
                        'type' => 'train-booking-confirmation-sent',
                        'sent_at' => now(),
                        'status' => 'pending',
                    ]);
                    try {
                        // Send email
                         //$ccEmail = ['admin@foxandmandal.co.in','pintu.chakraborty@foxandmandal.co.in','sumit.dey@foxandmandal.co.in','amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
                        // $bccEmail = ['amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
                        $ccEmail =['malik.priya123456@mail.com'];
                        $bccEmail =[];
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
                }else{
                    $email_data = [
                        'name' => $user->name,
                        'subject' => 'Bus Booking Confirmation # '.$booking->order_no,
                        'email' => $user->email,
                        'busBooking' => $booking,
                       
                        'blade_file' => 'mail/bus-booking-confirm-mail',
                    ];
                   /* $mailLog = MailActivity::create([
                        'email' => $user->email,
                        'type' => 'bus-booking-confirmation-sent',
                        'sent_at' => now(),
                        'status' => 'pending',
                    ]);
                    try {
                        // Send email
                         //$ccEmail = ['admin@foxandmandal.co.in','pintu.chakraborty@foxandmandal.co.in','sumit.dey@foxandmandal.co.in','amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
                        // $bccEmail = ['amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
                        $ccEmail =['malik.priya123456@mail.com'];
                        $bccEmail =[];
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

                }

                return redirect()->back()->with('success', 'Ticket has been booked');
            
        }
    
        // For all other statuses
        $booking->status = $status;
        $booking->save();
    
        return redirect()->back()->with('success', 'Booking status updated.');
       
      
    }

    public function uploadTicket(Request $request, $id)
    {
        $request->validate([
            'ticket' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $booking = TrainBooking::findOrFail($id);

        if ($request->hasFile('ticket')) {
            $uploadPath = public_path('uploads/tickets');

            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            if (!empty($booking->ticket) && file_exists(public_path($booking->ticket))) {
                unlink(public_path($booking->ticket));
            }

            $file = $request->file('ticket');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move($uploadPath, $fileName);

            $booking->ticket = 'uploads/tickets/' . $fileName;
            $booking->save();

            return response()->json([
                'status' => 'success',
                'path'   => $booking->ticket
            ]);
        }

        return response()->json(['status' => 'error'], 400);
    }

    public function edit(Request $request,$id): View
	{
	    
	    $data=TrainBooking::where('id',$id)->first();
	    return view('facility.train-booking.edit', compact('data', 'request'));
	}
	
	
	 public function update(Request $request,$id)
    {
       // dd($request->all());
    $orderData = TrainBooking::findOrFail($id);
    if (!$orderData) {
        return redirect()->back()
                        ->with('failure','Booking Data not found');
    }
    $now = Carbon::now();
    $currentHour = (int)$now->format('H');

    $today = Carbon::today();
    $pickupDate = Carbon::parse($orderData->travel_date);
    if ($pickupDate->lessThan($today)) {
        return redirect()->back()->with('failure', 'Booking cannot be edited. Departure date is before today.');
    }
    // ⛔ Restrict edits/cancellations less than 6 hours before pickup time
    $pickupDateTime = Carbon::parse($orderData['travel_date']);

    if ($now->greaterThan($pickupDateTime->copy()->subHours(6))) {
        return redirect()->back()
                        ->with('failure','Edits or cancellations must be made at least 6 hours before the pickup time.');
        
    }

   if ($orderData->status==4) {
        return redirect()->back()
                        ->with('failure','Booking already cancelled, cannot be edited.');
    }
    
    $combinedTravellers = collect($request->input('prefix'))
    ->zip($request->input('traveller'))
    ->map(fn($pair) => trim($pair[0] . ' ' . $pair[1]))
    ->implode(',');

    $seatPreferences = collect($request->input('seat_preference'))->implode(',');
    $foodPreferences = collect($request->input('food_preference'))->implode(',');

    // create matter code if not available
    $matterCodeInput = $request->matter_code;
    $matterId = null; // default to null

    if ($matterCodeInput) {
        $user = $orderData->user; 
        $clientName = $user->name ?? 'Unknown';

        $matter = MatterCode::firstOrCreate(
            ['matter_code' => $matterCodeInput], 
            ['client_name' => $clientName]
        );

        // Store ID for saving
        $matterId = $matter->id;
    }

    // ✅ Update flow
  $newData = [
       'bill_to' => $request['bill_to'],
        'from' => $request['from'],
        'to' => $request->to ?? null,
        'travel_date' => date('d-m-Y', strtotime($request['travel_date'])),
        'departure_time' => $request['departure_time'],
         'type' => $request['type'],
        'trip_type' => $request['trip_type'],
        'return_date' => !empty($request->return_date) ? date('d-m-Y', strtotime($request->return_date)) : null,
        'return_time' =>  $request->return_time ?? null,
        'seat_preference' => $seatPreferences,
        'food_preference' => $foodPreferences,
        'matter_code' => $matterId,
        'traveller' => $combinedTravellers,
        'purpose' => $request->purpose ?? null,
        'description' => $request->description ?? null,
        'pnr' => $request->pnr,
        'updated_at' => now()
    ];
    
     // Compare and insert into log
    foreach ($newData as $field => $newValue) {
        $oldValue = $orderData->$field ?? null;

        if ($newValue != $oldValue) {
            DB::table('edit_logs')->insert([
                'table_name' => 'train_bookings',
                'record_id' => $request->id,
                'field' => $field,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'updated_by' => Auth::user()->id,
                'created_at' => now()
            ]);
        }
    }

    DB::table('train_bookings')->where('id', $id)->update($newData);
    
    $booking=TrainBooking::findOrFail($id);
    if($booking->type==1){
    
    $user=User::where('id',$booking->user_id)->first();
            $email_data = [
                'name' => $user->name,
                'subject' => 'REVISED: Train Booking Request # '.$booking->order_no,
                'email' => $user->email,
                'trainBooking' => $booking,
               
                'blade_file' => 'mail/train-booking-mail',
            ];
                $mailLog = MailActivity::create([
                    'email' => $user->email,
                    'type' => 'train-booking-edit-information-sent',
                    'sent_at' => now(),
                    'status' => 'pending',
                ]);
                /*try {
                     $ccEmail = ['admin@foxandmandal.co.in','pintu.chakraborty@foxandmandal.co.in','sumit.dey@foxandmandal.co.in','amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
                     $bccEmail = ['amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
                     //$ccEmail = ['malik.priya123456@gmail.com'];
                     //$bccEmail=['koushik@techmantra.co'];
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
    }else{
        $user=User::where('id',$booking->user_id)->first();
            $email_data = [
                'name' => $user->name,
                'subject' => 'REVISED: Bus Booking Request # '.$booking->order_no,
                'email' => $user->email,
                'busBooking' => $booking,
               
                'blade_file' => 'mail/bus-booking-mail',
            ];
                $mailLog = MailActivity::create([
                    'email' => $user->email,
                    'type' => 'bus-booking-edit-information-sent',
                    'sent_at' => now(),
                    'status' => 'pending',
                ]);
                /*try {
                     $ccEmail = ['admin@foxandmandal.co.in','pintu.chakraborty@foxandmandal.co.in','sumit.dey@foxandmandal.co.in','amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
                     $bccEmail = ['amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
                     //$ccEmail = ['malik.priya123456@gmail.com'];
                     //$bccEmail=['koushik@techmantra.co'];
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
    }
    
     return redirect()->back()
                        ->with('success','Train booking updated successfully.');
    
    }
    
    
    
     public function upload(Request $request)
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
                                         $bulkData = [];
                    
                   try {
                        foreach ($importData_arr as $importData) {
                            $insertData = [
                                "airport"   => $importData[0] ?? null,
                                "city"      => $importData[1] ?? null,
                                "country"   => $importData[2] ?? null,
                                "code"      => $importData[3] ?? null,
                                "Continent" => $importData[4] ?? null,
                            ];
                    
                            DB::table('airport_list')->insert($insertData);
                            $successCount++;
                        }
                    } catch (Exception $e) {
                        dd($e->getMessage()); // or log it
                    }

                    Session::flash('message', 'CSV Import Complete. Total no of entries: ' . count($importData_arr) . '. Successfull: ' . $successCount . ', Failed: ' . (count($importData_arr) - $successCount));
               
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
