<?php

namespace App\Http\Controllers\Facility;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FlightBooking;
use App\Models\User;
use App\Models\MailActivity;
use App\Models\MatterCode;
use Illuminate\View\View;
use Carbon\Carbon;
use DB;
use Auth;
class FlightBookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:view flight booking|flight booking list csv export', ['only' => ['index']]);
         $this->middleware('permission:flight booking details|flight booking status update', ['only' => ['show']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): View
    {
        // Capture inputs
    $keyword = $request->input('keyword');
    $issueDateFrom = $request->input('date_from');
    $issueDateTo = $request->input('date_to');
    $billTo = $request->input('bill_to');
    // Start the query
    // $query = FlightBooking::latest('id');
    $query = FlightBooking::with(['user', 'matter'])->latest('id');
    
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
    $data = $query->paginate(25);
        return view('facility.flight-booking.index',compact('data','request'))
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
    // $query = FlightBooking::latest('id');
    $query = FlightBooking::with(['user', 'matter'])->latest('id');

    
    // Apply the keyword search conditions
    if (!empty($keyword)) {
        $query->where(function ($query) use ($keyword) {
            $query->where('matter_code', 'LIKE', "%$keyword%")
                  ->orWhere('from_location', 'LIKE', "%$keyword%")
                  ->orWhere('to_location', 'LIKE', "%$keyword%")
                  ->orWhere('pickup_date', 'LIKE', "%$keyword%")
                  ->orWhere('pickup_time', 'LIKE', "%$keyword%")
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
        $query->whereBetween('pickup_date', [
            Carbon::parse($issueDateFrom)->startOfDay(),
            Carbon::parse($issueDateTo)->endOfDay()
        ]);
    } 
    // Apply date filter if only 'date_from' is provided
    elseif (!empty($issueDateFrom)) {
        $query->whereDate('pickup_date', '>=', Carbon::parse($issueDateFrom)->startOfDay());
    } 
    // Apply date filter if only 'date_to' is provided
    elseif (!empty($issueDateTo)) {
        $query->whereDate('pickup_date', '<=', Carbon::parse($issueDateTo)->endOfDay());
    }

    // Execute the query and paginate results
        $data = $query->cursor();
        $book = $data->all();
        if (count($book) > 0) {
            $delimiter = ","; 
            $filename = "flight boking request.csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
            $fields = array('SR', 'Unique Code','Member','From','To','Trip Type','Departure Date','Preffered Departure Time','Return Date','Return Time','Traveller','Seat Preference','Food Preference','Bill to','Matter Code','Purpose','Description','Creation Date'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

        foreach ($book as $row) {
                $datetime = date('j F, Y h:i A', strtotime($row['created_at']));
                $return_date = ($row->trip_type == 2) ? ($row->return_date ?? 'NA') : '';
                $return_time = ($row->trip_type == 2) ? ($row->return_time ?? 'NA') : '';
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
                            $key === 0 ? $count : '', // SR only for first row
                            $key === 0 ? $row->order_no : '',
                            $key === 0 ? ($row['user']['name'] ?? 'NA') : '',
                            $key === 0 ? ($row['from'] ?? 'NA') : '',
                            $key === 0 ? ($row->to ?? 'NA') : '',
                            $key === 0 ? ($row->trip_type == 1 ? 'One way' : ($row->trip_type == 2 ? 'Round Trip' : '')) : '',
                            $key === 0 ? ($row->departure_date ?? 'NA') : '',
                            $key === 0 ? ($row->arrival_time ?? 'NA') : '',
                            $key === 0 ? $return_date : '',
                            $key === 0 ? $return_time : '',
                            $traveller['name'] ?? 'N/A',
                            $traveller['seat_preference'] ?? 'N/A',
                            $traveller['food_preference'] ?? 'N/A',
                            $key === 0 ? ($row->bill_to == 1 ? 'Firm' : ($row->bill_to == 2 ? 'Third Party' : 'Matter Expenses')) : '',
                            $key === 0 ? ($row->matter_code ?? 'NA') : '',
                            $key === 0 ? ($row->purpose ?? 'NA') : '',
                            $key === 0 ? ($row->description ?? 'NA') : '',
                            $key === 0 ? $datetime : '',
                        ];
            
                        fputcsv($f, $lineData, $delimiter);
                    }
                } else {
                    $lineData = [
                        $count,
                        $row->order_no,
                        $row['user']['name'] ?? 'NA',
                        $row['from'] ?? 'NA',
                        $row->to ?? 'NA',
                        $row->trip_type == 1 ? 'One way' : ($row->trip_type == 2 ? 'Round Trip' : ''),
                        $row->departure_date ?? 'NA',
                        $row->arrival_time ?? 'NA',
                        $return_date ?? 'NA',
                        $return_time ?? 'NA',
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


                //fputcsv($f, $lineData, $delimiter);

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
	    
	    $data=FlightBooking::where('id',$id)->first();
	    return view('facility.flight-booking.view', compact('data', 'request'));
	}
	
	
	public function status(Request $request,$id,$status)
    {
		$booking = FlightBooking::findOrFail($id);
        
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
            $pickupDateTime = Carbon::parse($booking['departure_date'] . ' ' . $booking['arrival_time']);

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
                    'pnr' => 'required|string|max:1000',
                    'flight_no' => 'required',
                    'date_time' => 'required',
                ]);
        
                $booking->status = 3;
                $booking->pnr = $request->pnr; // Make sure 'remarks' column exists in DB
                $booking->flight_no = $request->flight_no; // Make sure 'remarks' column exists in DB
                $booking->date_time = $request->date_time;
                
                $booking->save();
                
                $user=User::where('id',$booking->user_id)->first();
                $email_data = [
                    'name' => $user->name,
                    'subject' => 'Flight Booking Confirmation # '.$booking->order_no,
                    'email' => $user->email,
                    'flightBooking' => $booking,
                   
                    'blade_file' => 'mail/flight-booking-confirm-mail',
                ];
                /*$mailLog = MailActivity::create([
                    'email' => $user->email,
                    'type' => 'flight-booking-confirmation-sent',
                    'sent_at' => now(),
                    'status' => 'pending',
                ]);
                try {
                    // Send email
                    // $ccEmail = ['admin@foxandmandal.co.in','pintu.chakraborty@foxandmandal.co.in','sumit.dey@foxandmandal.co.in','amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
                     //$bccEmail = ['amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
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
            
                return redirect()->back()->with('success', 'Flight Ticket has been booked with PNR.');
            
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

        $booking = FlightBooking::findOrFail($id);

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
	    
	    $data=FlightBooking::where('id',$id)->first();
	    return view('facility.flight-booking.edit', compact('data', 'request'));
	}
	
	
	 public function update(Request $request,$id)
    {
        //dd($request->all());
    $orderData = FlightBooking::findOrFail($id);
   
    if (!$orderData) {
        return redirect()->back()
                        ->with('failure','Booking Data not found');
    }
    $now = Carbon::now();
    $currentHour = (int)$now->format('H');

    $today = Carbon::today();
    $pickupDate = Carbon::parse($orderData->departure_date);
    if ($pickupDate->lessThan($today)) {
        return redirect()->back()->with('failure', 'Booking cannot be edited. Departure date is before today.');
    }
    // ⛔ Restrict edits/cancellations less than 6 hours before pickup time
    $pickupDateTime = Carbon::parse($orderData['departure_date']);

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
        $matterId = null; 

        if ($matterCodeInput) {
            $user = $orderData->user; 
            $clientName = $user->name ?? 'Unknown';

            // Create or get existing MatterCode
            $matter = MatterCode::firstOrCreate(
                ['matter_code' => $matterCodeInput],
                ['client_name' => $clientName]
            );

            // Capture ID
            $matterId = $matter->id;
        }

        // ✅ Update flow
        $newData = [
            'bill_to'          => $request->bill_to,
            'from'             => $request->from,
            'to'               => $request->to ?? null,
            'departure_date'   => date('Y-m-d', strtotime($request->departure_date)),
            'arrival_time'     => $request->arrival_time,
            'trip_type'        => $request->trip_type,
            'return_date'      => date('Y-m-d', strtotime($request->return_date)),
            'return_time'      => $request->return_time ?? null,
            'seat_preference'  => $seatPreferences,
            'food_preference'  => $foodPreferences,
            'matter_code'   => $matterId, 
            'traveller'        => $combinedTravellers,
            'purpose'          => $request->purpose ?? null,
            'description'      => $request->description ?? null,
            'pnr'              => $request->pnr,
            'updated_at'       => now(),
        ];

    
     // Compare and insert into log
    foreach ($newData as $field => $newValue) {
        $oldValue = $orderData->$field ?? null;

        if ($newValue != $oldValue) {
            DB::table('edit_logs')->insert([
                'table_name' => 'flight_bookings',
                'record_id' => $request->id,
                'field' => $field,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'updated_by' => Auth::user()->id,
                'created_at' => now()
            ]);
        }
    }

    DB::table('flight_bookings')->where('id', $id)->update($newData);
    $booking=FlightBooking::findOrFail($id);
    
    $user=User::where('id',$booking->user_id)->first();
            $email_data = [
                'name' => $user->name,
                'subject' => 'REVISED: Flight Booking Request # '.$booking->order_no,
                'email' => $user->email,
                'flightBooking' => $booking,
               
                'blade_file' => 'mail/flight-booking-mail',
            ];
                /*$mailLog = MailActivity::create([
                    'email' => $user->email,
                    'type' => 'flight-booking-edit-information-sent',
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
                        ->with('success','Flight booking updated successfully.');
    
    }
}
