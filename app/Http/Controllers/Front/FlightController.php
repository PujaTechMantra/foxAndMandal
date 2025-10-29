<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FlightBooking;
use App\Models\User;
use App\Models\MatterCode;
use App\Models\MailActivity;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use DB;

class FlightController extends Controller
{
    public function index(){
        return view('front.travel.flight.index');
    }

    public function store(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'trip_type' => 'required|integer|in:1,2',
            'from' => 'required|string|max:255',
            'to' => 'required|string|max:255',
            'departure_date' => 'required|date',
            'arrival_time' => 'required|string',
            'return_date' => 'nullable|date',
            'return_time' => 'nullable|string',
            'bill_to' => 'required|integer|in:1,2,3',
            'traveller' => 'required|array|min:1',
            'traveller.*.name' => 'required|string|max:255',
            'traveller.*.seat_preference' => 'nullable|string|max:255',
            'traveller.*.food_preference' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            // If AJAX, send JSON response
            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

           
        }

        try {

            // Generate unique order number
            $last = FlightBooking::latest('sequence_no')->first();
            $newSeq = $last ? $last->sequence_no + 1 : 1;
            $ordNo = sprintf("%'.05d", $newSeq);
            $uniqueNo = 'FM-FB-' . date('Y') . '-' . $ordNo;

            // Prepare traveller data
            $travellers = collect($request->traveller);
            $travellerNames = $travellers->pluck('name')->implode(',');
            $seatPreferences = $travellers->pluck('seat_preference')->filter()->implode(',');
            $foodPreferences = $travellers->pluck('food_preference')->filter()->implode(',');

            // Handle Matter Code
            $matterId = null;
            if ($request->filled('matter_code')) {
                $user = User::find($request->user_id);
                $clientName = $user->name ?? 'Unknown';
                $matter = MatterCode::firstOrCreate(
                    ['matter_code' => $request->matter_code],
                    ['client_name' => $clientName]
                );
                $matterId = $matter->id;
            }

            // Create booking
            $booking = FlightBooking::create([
                'user_id' => $request->user_id,
                'trip_type' => $request->trip_type,
                'from' => $request->from,
                'to' => $request->to,
                'departure_date' => $request->departure_date,
                'arrival_time' => $request->arrival_time,
                'return_date' => $request->return_date,
                'return_time' => $request->return_time,
                'traveller' => $travellerNames,
                'seat_preference' => $seatPreferences,
                'food_preference' => $foodPreferences,
                'bill_to' => $request->bill_to,
                'matter_code' => $matterId,
                'purpose' => $request->purpose,
                'description' => $request->description,
                'sequence_no' => $newSeq,
                'order_no' => $uniqueNo,
            ]);

                // $user = User::find($request->user_id);
                // $email_data = [
                //     'name' => $user->name,
                //     'subject' => 'Flight Booking Request # '.$uniqueNo,
                //     'email' => $user->email,
                //     'flightBooking' => $flightBooking,
                
                //     'blade_file' => 'mail/flight-booking-mail',
                // ];
                //     $mailLog = MailActivity::create([
                //         'email' => $user->email,
                //         'type' => 'flight-booking-information-sent',
                //         'sent_at' => now(),
                //         'status' => 'pending',
                //     ]);
                //     try {
                //          $ccEmail = ['admin@foxandmandal.co.in','pintu.chakraborty@foxandmandal.co.in','sumit.dey@foxandmandal.co.in','amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
                //          $bccEmail = ['amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
                //         // Send email
                //          SendMail($email_data,$ccEmail, $bccEmail);
                
                //         // Update the log status to "sent" on success
                //         $mailLog->update(['status' => 'sent']);
                
                    
                //     } catch (\Exception $e) {
                //          dd('Exception:', $e->getMessage());
                //         // Update the log status to "failed" on error
                //         $mailLog->update(['status' => 'failed']);
                
                //         //return response()->json(['success' => false, 'message' => 'Failed to send email.'], 500);
                //     }

            // Flash success message

                return response()->json([
                    'status' => true,
                    'message' => 'Flight booking created successfully!'
                ]);
            
         } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }
}
