<?php

namespace App\Http\Controllers\Facility;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CabBooking;
use App\Models\User;
use App\Models\MailActivity;
use App\Models\MatterCode;
use Illuminate\View\View; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use DB;
use Auth;
class CabBookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:view cab booking|cab booking list csv export', ['only' => ['index']]);
         $this->middleware('permission:cab booking detail|cab booking status update', ['only' => ['show']]);
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
    // $query = CabBooking::latest('id');
    $query = CabBooking::with(['user', 'matter'])->latest('id');
    
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
    $data = $query->paginate(25);
     
    // Return the view with data
    return view('facility.cab-booking.index', compact('data', 'request'))
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
    $query = CabBooking::with(['user', 'matter'])->latest('id');
    
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
            $filename = "cab boking request.csv"; 

            // Create a file pointer 
            $f = fopen('php://memory', 'w'); 

            // Set column headers 
            // $fields = array('SR', 'QRCODE TITLE','CODE','DISTRIBUTOR','ASE','STORE NAME','STORE MOBILE','STORE EMAIL','STORE STATE','STORE ADDRESS','POINTS','DATE'); 
            $fields = array('SR','Unique Code', 'Member','From','To','Pickup Date','Pickup Time','Traveller','Bill to','Matter Code','Purpose','Description','Creation Date'); 
            fputcsv($f, $fields, $delimiter); 

            $count = 1;

            foreach($book as $row) {
                $datetime = date('j F, Y h:i A', strtotime($row['created_at']));
				

                $lineData = array(
                    $count,
                    $row->order_no,
					$row['user']['name'] ?? 'NA',
                    $row['from_location'] ?? 'NA',
					$row->to_location ?? 'NA',
					$row->pickup_date ?? 'NA',
					$row->pickup_time ?? 'NA',
					$row->traveller ?? 'NA',
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
	    
	    $data=CabBooking::where('id',$id)->first();
	    return view('facility.cab-booking.view', compact('data', 'request'));
	}
	
	
	public function status(Request $request,$id,$status)
    {
		// dd($request->all());
		$booking = CabBooking::findOrFail($id);
		 if ($booking->status == 4) {
            return redirect()->back()->with('failure', 'This booking has already been cancelled and cannot be updated.');
        }
         if($booking->status == 3 && $status != 4){
            return redirect()->back()->with('failure', 'Cab has already been booked and can only be cancelled.');
        }
		if($status==4){
		    $now = Carbon::now();
		    $updatedEntry = CabBooking::findOrFail($id);
		    $pickupDateTime = Carbon::parse($updatedEntry['pickup_date'] . ' ' . $updatedEntry['pickup_time']);

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
        if ($status == 3) {
            
                $request->validate([
                    'cab_no' => 'required',
                    'date_time' => 'required',
                ]);
        
                $booking->status = 3;
                $booking->cab_no = $request->cab_no; // Make sure 'remarks' column exists in DB
                $booking->date_time = $request->date_time;

                $booking->save();

                $user=User::where('id',$booking->user_id)->first();
                $email_data = [
                    'name' => $user->name,
                    'subject' => 'Cab Booking Confirmation # '.$booking->order_no,
                    'email' => $user->email,
                    'cabBooking' => $booking,
                   
                    'blade_file' => 'mail/cab-booking-confirm-mail',
                ];
                $mailLog = MailActivity::create([
                    'email' => $user->email,
                    'type' => 'cab-booking-confirmation-sent',
                    'sent_at' => now(),
                    'status' => 'pending',
                ]);
                try {
                    // Send email
                     //$ccEmail = ['admin@foxandmandal.co.in','pintu.chakraborty@foxandmandal.co.in','sumit.dey@foxandmandal.co.in','amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
                     //$bccEmail = ['amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
                    //  $ccEmail =['malik.priya123456@mail.com'];
                     $bccEmail =[];
                    // Send email
                    //  SendMail($email_data,$ccEmail, $bccEmail);
            
                    // Update the log status to "sent" on success
                    $mailLog->update(['status' => 'sent']);
            
                   
                } catch (\Exception $e) {
                     dd('Exception:', $e->getMessage());
                    // Update the log status to "failed" on error
                    $mailLog->update(['status' => 'failed']);
            
                    //return response()->json(['success' => false, 'message' => 'Failed to send email.'], 500);
                }
            
                return redirect()->back()->with('success', 'Cab has been booked.');
            
        }
	
		
            
            // For all other statuses
            $booking->status = $status;
            $booking->save();
            return redirect()->back()->with('success', 'Booking status updated');
		
      
    }

    public function uploadTicket(Request $request, $id)
    {
        $request->validate([
            'ticket' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $booking = CabBooking::findOrFail($id);

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
	    
	    $data=CabBooking::where('id',$id)->first();
	    return view('facility.cab-booking.edit', compact('data', 'request'));
	}
	
	
	 public function update(Request $request,$id)
    {
        
    $orderData = CabBooking::findOrFail($id);
    if (!$orderData) {
        return redirect()->back()
                        ->with('failure','Booking Data not found');
    }
    $now = Carbon::now();
    $currentHour = (int)$now->format('H');

    $today = Carbon::today();
    $pickupDate = Carbon::parse($orderData->pickup_date);
    if ($pickupDate->lessThan($today)) {
        return redirect()->back()->with('failure', 'Booking cannot be edited. Pickup date is before today.');
    }
    // ⛔ Restrict edits/cancellations less than 6 hours before pickup time
    $pickupDateTime = Carbon::parse($orderData['pickup_date'] . ' ' . $orderData['pickup_time']);

    if ($now->greaterThan($pickupDateTime->copy()->subHours(6))) {
        return redirect()->back()
                        ->with('failure','Edits or cancellations must be made at least 6 hours before the pickup time.');
        
    }

    if ($orderData->status==4) {
        return redirect()->back()
                        ->with('failure','Booking already cancelled, cannot be edited.');
    }

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

        $newData = [
            'bill_to'       => $request->bill_to,
            'from_location' => $request->from_location,
            'to_location'   => $request->to_location ?? null,
            'pickup_date'   => date('Y-m-d', strtotime($request->pickup_date)), 
            'pickup_time'   => $request->pickup_time,
            'matter_code'   => $matterId, 
            'traveller'     => is_array($request->traveller) ? implode(',', $request->traveller) : $request->traveller,
            'purpose'       => $request->purpose ?? null,
            'description'   => $request->description ?? null,
            'updated_at'    => now(),
        ];
    
     // Compare and insert into log
    foreach ($newData as $field => $newValue) {
        $oldValue = $orderData->$field ?? null;

        if ($newValue != $oldValue) {
            DB::table('edit_logs')->insert([
                'table_name' => 'cab_bookings',
                'record_id' => $request->id,
                'field' => $field,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'updated_by' => Auth::user()->id,
                'created_at' => now()
            ]);
        }
    }

    DB::table('cab_bookings')->where('id', $id)->update($newData);
    
    $booking=CabBooking::findOrFail($id);
    
    $user=User::where('id',$booking->user_id)->first();
            $email_data = [
                'name' => $user->name,
                'subject' => 'REVISED: Cab Booking Request # '.$booking->order_no,
                'email' => $user->email,
                'cabBooking' => $booking,
               
                'blade_file' => 'mail/cab-booking-mail',
            ];
                $mailLog = MailActivity::create([
                    'email' => $user->email,
                    'type' => 'cab-booking-edit-information-sent',
                    'sent_at' => now(),
                    'status' => 'pending',
                ]);
                try {
                    // $ccEmail = ['admin@foxandmandal.co.in','pintu.chakraborty@foxandmandal.co.in','sumit.dey@foxandmandal.co.in','amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
                     //$bccEmail = ['amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
                     $ccEmail = ['malik.priya123456@gmail.com'];
                     $bccEmail=['koushik@techmantra.co'];
                    // Send email
                    //  SendMail($email_data,$ccEmail, $bccEmail);
            
                    // Update the log status to "sent" on success
                    $mailLog->update(['status' => 'sent']);
            
                   
                } catch (\Exception $e) {
                     dd('Exception:', $e->getMessage());
                    // Update the log status to "failed" on error
                    $mailLog->update(['status' => 'failed']);
            
                    //return response()->json(['success' => false, 'message' => 'Failed to send email.'], 500);
                }
     return redirect()->back()
                        ->with('success','Cab updated successfully.');
    
    }

}
