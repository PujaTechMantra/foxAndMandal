<?php

namespace App\Http\Controllers\Api\Fms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrainBooking;
use App\Models\User;
use App\Models\MatterCode;
use App\Models\MailActivity;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use DB;
class TrainBookingController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'from' => 'required|string|max:255',
            'to' => 'required|string|max:255',
            'travel_date' => 'required',
            'bill_to' => 'required|integer|in:1,2,3',
             'traveller' => 'required|array',
            'traveller.*.name' => 'required|string|max:255',
            'traveller.*.seat_preference' => 'nullable|string|max:255',
            'traveller.*.food_preference' => 'nullable|string|max:255',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 400);
        }

        $validatedData = $validator->validated();
        $orderData = TrainBooking::select('sequence_no')->latest('sequence_no')->first();
            
            //if (empty($orderData->sequence_no)) {
                if (!empty($orderData->sequence_no)) {
                    $new_sequence_no = (int) $orderData->sequence_no + 1;
    
                } else {
                    $new_sequence_no = 1;
    
                }
                $ordNo = sprintf("%'.05d", $new_sequence_no);
                if($request['type']==1){
                 $uniqueNo = 'FM'.'-'.'TB'.'-'.date('Y').'-'.$ordNo;
                }else{
                    $uniqueNo = 'FM'.'-'.'BB'.'-'.date('Y').'-'.$ordNo;
                }
        $travelDate = Carbon::parse($validatedData['travel_date'])->format('Y-m-d');
         // Traveller details in comma-separated format
            $travellerNames = collect($validatedData['traveller'])->pluck('name')->implode(',');
            $seatPreferences = collect($validatedData['traveller'])->pluck('seat_preference')->implode(',');
            $foodPreferences = collect($validatedData['traveller'])->pluck('food_preference')->implode(',');
        
        // Handle matter code
            $matterId = null;
            $matterCodeInput = $request['matter_code'];

            if ($matterCodeInput) {
                $user = User::find($validatedData['user_id']); // get the user from request
                $clientName = $user->name ?? 'Unknown';

                $matter = MatterCode::firstOrCreate(
                    ['matter_code' => $matterCodeInput],
                    ['client_name' => $clientName]
                );

                $matterId = $matter->id;
            }
        
        $trainBooking = TrainBooking::create([
            'user_id' => $validatedData['user_id'],
            'from' => $validatedData['from'],
            'to' => $validatedData['to'],
            'travel_date' => $validatedData['travel_date'],
            'departure_time' => $request['departure_time']?? null,
            'bill_to' => $validatedData['bill_to'],
            'matter_code' => $matterId,
            //'traveller' =>implode(',',$request['traveller'])?? null,
            'type' => $request['type']?? null,
            'trip_type' =>$request['trip_type']?? null,
            'return_date' =>$request['return_date']?? null,
            'return_time' =>$request['return_time']?? null,
            'traveller' => $travellerNames ?? null,
            'seat_preference' => $seatPreferences ?? null,
            'food_preference' => $foodPreferences ?? null,
            'purpose' => $request['purpose']?? null,
            'description' => $request['description']?? null,
            'sequence_no' => $new_sequence_no?? null,
            'order_no' => $uniqueNo?? null,
        ]);
        
        $user=User::where('id',$validatedData['user_id'])->first();
        if($request['type']==1){
            $email_data = [
                'name' => $user->name,
                'subject' => 'Train Booking Request # '.$uniqueNo,
                'email' => $user->email,
                'trainBooking' => $trainBooking,
               
                'blade_file' => 'mail/train-booking-mail',
            ];
                $mailLog = MailActivity::create([
                    'email' => $user->email,
                    'type' => 'train-booking-information-sent',
                    'sent_at' => now(),
                    'status' => 'pending',
                ]);
                try {
                     $ccEmail = ['admin@foxandmandal.co.in','pintu.chakraborty@foxandmandal.co.in','sumit.dey@foxandmandal.co.in','amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
                     $bccEmail = ['amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
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
        }else{
            $email_data = [
                'name' => $user->name,
                'subject' => 'Bus Booking Request # '.$uniqueNo,
                'email' => $user->email,
                'busBooking' => $trainBooking,
               
                'blade_file' => 'mail/bus-booking-mail',
            ];
                $mailLog = MailActivity::create([
                    'email' => $user->email,
                    'type' => 'bus-booking-information-sent',
                    'sent_at' => now(),
                    'status' => 'pending',
                ]);
                try {
                     $ccEmail = ['admin@foxandmandal.co.in','pintu.chakraborty@foxandmandal.co.in','sumit.dey@foxandmandal.co.in','coziclubsupport@luxcozi.com'];
                     $bccEmail = ['amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
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
        }

        if (!$trainBooking) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create train booking. Please try again.',
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Train/Bus booked successfully.',
            'data' => $trainBooking,
        ], 201);
    }
    
    
    /* public function cancelTrainBooking(Request $request)
  {
    $validator = Validator::make($request->all(), [
        'id' => 'required|exists:train_bookings,id',
        
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'error' => $validator->errors()
        ], 400);
    }

    $validatedData = $validator->validated();
    $orderData = TrainBooking::findOrFail($request->id);

    $now = Carbon::now();
    $currentHour = (int)$now->format('H');

    // ⛔ Restrict actions from 7:00 PM to 10:00 AM
    if ($currentHour >= 19 || $currentHour < 10) {
        return response()->json([
            'status' => false,
            'message' => 'Booking edits or cancellations are not allowed between 7:00 PM and 10:00 AM.'
        ], 403);
    }

    // ⛔ Restrict edits/cancellations less than 6 hours before pickup time
    $pickupDateTime = Carbon::parse($orderData['travel_date'] . ' ' . $orderData['travel_date']);

    if ($now->greaterThan($pickupDateTime->copy()->subHours(6))) {
        return response()->json([
            'status' => false,
            'message' => 'Edits or cancellations must be made at least 6 hours before the pickup time.'
        ], 403);
    }

    // ✅ Cancellation flow
    if ($request->has('cancel') && $request->cancel == true) {
        $orderData->status = 4; // Cancelled
        $orderData->cancellation_remarks = $request->remarks ?? 'No remarks provided';
        $orderData->save();

        return response()->json([
            'status' => true,
            'message' => 'Booking cancellation requested successfully.',
            'data' => $orderData
        ]);
    }

    // ✅ Update flow
    $orderData->bill_to = $request['bill_to'];
    $orderData->from = $request['from'];
    $orderData->to = $request->to ?? null;
    $orderData->travel_date = $request['travel_date'];
    $orderData->departure_time = $request['departure_time'];
    $orderData->type = $request['type'];
    $orderData->trip_type = $request['trip_type'];
    $orderData->return_date = $request['return_date'];
    $orderData->return_time = $request['return_time'];
    $orderData->seat_preference = $request['seat_preference'];
    $orderData->food_preference = $request['food_preference'];
    $orderData->matter_code = $request->matter_code ?? null;
    $orderData->traveller = is_array($request->traveller) ? implode(',', $request->traveller) : null;
    $orderData->purpose = $request->purpose ?? null;
    $orderData->description = $request->description ?? null;

    $orderData->save();
    DB::table('edit_logs')->insert([
            'table_name' => 'train_bookings',
            'record_id'  => $request->id,
            'field'      => 'terms',
            'old_value'  => $existing->terms,
            'new_value'  => $request->terms,
            'updated_by' => Auth::id(), // or null if no auth
            'created_at' => now(),
        ]);
    return response()->json([
        'status' => true,
        'message' => 'Cab updated successfully.',
        'data' => $orderData
    ]);
}*/




public function cancelTrainBooking(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'required|exists:train_bookings,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'error' => $validator->errors()
        ], 400);
    }

    $orderData = DB::table('train_bookings')->where('id', $request->id)->first();

    if (!$orderData) {
        return response()->json([
            'status' => false,
            'message' => 'Booking not found.'
        ], 404);
    }
    $validatedData = $validator->validated();
    $now = Carbon::now();
    $currentHour = (int) $now->format('H');

    // ⛔ Restrict actions from 7:00 PM to 10:00 AM
    if ($currentHour >= 19 || $currentHour < 10) {
        return response()->json([
            'status' => false,
            'message' => 'Your Travel requisition is registered. We shall get back to you in next business hours.In case of any urgency, you may contact the Travel Desk (Admin) directly on mobile phone.'
        ], 403);
    }

    $pickupDateTime = Carbon::parse($orderData->travel_date);

    if ($now->greaterThan($pickupDateTime->copy()->subHours(6))) {
        return response()->json([
            'status' => false,
            'message' => 'Edits or cancellations must be made at least 6 hours before the pickup time.'
        ], 403);
    }

    // ✅ If cancel
    if ($request->has('cancel') && $request->cancel == true) {
        DB::table('train_bookings')->where('id', $request->id)->update([
            'status' => 4,
            'cancellation_remarks' => $request->remarks ?? 'No remarks provided',
            'updated_at' => now()
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Booking cancellation requested successfully.'
        ]);
    }
            $travellerNames = collect($request['traveller'])->pluck('name')->implode(',');
            $seatPreferences = collect($request['traveller'])->pluck('seat_preference')->implode(',');
            $foodPreferences = collect($request['traveller'])->pluck('food_preference')->implode(',');
    // ✅ Update fields and maintain log
    $newData = [
        'bill_to' => $request->bill_to,
        'from' => $request->from,
        'to' => $request->to ?? null,
        'travel_date' => $request->travel_date,
        'departure_time' => $request->departure_time,
        'type' => $request->type,
        'trip_type' => $request->trip_type,
        'return_date' => $request->return_date,
        'return_time' => $request->return_time,
        'matter_code' => $request->matter_code ?? null,
        'traveller' => $travellerNames ?? null,
        'seat_preference' => $seatPreferences ?? null,
        'food_preference' => $foodPreferences ?? null,
        'purpose' => $request->purpose ?? null,
        'description' => $request->description ?? null,
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
                'updated_by' => NULL,
                'created_at' => now()
            ]);
        }
    }

    DB::table('train_bookings')->where('id', $request->id)->update($newData);

    return response()->json([
        'status' => true,
        'message' => 'Train Booking updated successfully.'
    ]);
}


   


}
