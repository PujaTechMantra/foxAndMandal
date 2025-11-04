<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HotelBooking;
use App\Models\User;
use App\Models\MailActivity;
use App\Models\Room;
use App\Models\MatterCode;
use App\Models\Property;
use App\Models\HotelBookingGuest;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use DB;

class HotelController extends Controller
{
    public function index()
    {
        $properties = Property::orderBy('name', 'asc')->get(); 
        return view('front.travel.hotel.index', compact('properties'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'hotel_type' => 'required|integer|in:1,2',
            'property_id' => 'required_if:hotel_type,1|exists:properties,id|nullable',
            'hotel_preference' => 'required_if:hotel_type,2|string|nullable',
            'departure_date' => 'required|date',
            'return_date' => 'required|date|after:departure_date',
            'guest_number' => 'required|integer|min:1',
            'bill' => 'required|integer|in:1,2,3',
            'guest_type' => 'required',
            'matter_code' => 'required_if:bill,3|nullable|string|max:255',
            'remarks' => 'required_if:bill,1,2|nullable|string',
        ]);


        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validatedData = $validator->validated();

        $orderData = HotelBooking::select('sequence_no')->latest('sequence_no')->first();
        $new_sequence_no = $orderData && !empty($orderData->sequence_no)
            ? (int)$orderData->sequence_no + 1
            : 1;

        $ordNo = sprintf("%'.05d", $new_sequence_no);
        $uniqueNo = 'FM-HB-' . date('Y') . '-' . $ordNo;

        // Matter code logic
        $matterId = null;
        if ($request->bill == 3 && $request->filled('matter_code')) {
            $user = auth()->guard('front_user')->user();
            $matter = MatterCode::firstOrCreate(
                ['matter_code' => $request->matter_code],
                ['client_name' => $user->name ?? 'Unknown']
            );
            $matterId = $matter->id;
        }

        $booking = HotelBooking::create([
            'user_id' => $validatedData['user_id'],
            'property_id' => $validatedData['property_id'],
            'checkin_date' => $validatedData['departure_date'],
            'checkout_date' => $validatedData['return_date'],
            'guest_number' => $validatedData['guest_number'],
            'bill_to' => $validatedData['bill'],
            'matter_code' => $matterId,
            'guest_type' => is_array($request->guest_type)
                            ? implode(',', $request->guest_type)
                            : $request->guest_type,
            'hotel_type' => $request->hotel_type,
            'text' => $request->hotel_preference,
            'seat_preference' => $request->seat_preference,
            'food_preference' => $request->food_preference,
            'purpose' => $request->purpose,
            'description' => $request->description,
            'sequence_no' => $new_sequence_no,
            'bill_to_remarks' => $validatedData['remarks'],
            'order_no' => $uniqueNo,
        ]);

        $user = auth()->guard('front_user')->user();
            $email_data = [
                'name' => $user->name,
                'subject' => 'Accommodation Booking Request # '.$uniqueNo,
                'email' => $user->email,
                'hotelBooking' => $booking,
               
                'blade_file' => 'mail/hotel-booking-mail',
            ];
                $mailLog = MailActivity::create([
                    'email' => $user->email,
                    'type' => 'hotel-booking-information-sent',
                    'sent_at' => now(),
                    'status' => 'pending',
                ]);
                try {
                     $ccEmail = ['admin@foxandmandal.co.in','pintu.chakraborty@foxandmandal.co.in','sumit.dey@foxandmandal.co.in','amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
                     $bccEmail = ['amitava.mukherjee@foxandmandal.co.in','surya.sarkar@foxandmandal.co.in'];
                    //  SendMail($email_data,$ccEmail, $bccEmail);
            
                    $mailLog->update(['status' => 'sent']);
            
                   
                } catch (\Exception $e) {
                    \Log::error('Hotel booking mail failed: ' . $e->getMessage());
                    $mailLog->update(['status' => 'failed']);
                }

        if (!$booking) {
            return redirect()->back()->with('error', 'Failed to create hotel booking.');
        }

        return redirect()->route('front.travel.dashboard')->with('success', 'Booked successfully!');
    }

    public function history()
    {
        $userId = auth()->guard('front_user')->id();

        $hotelBookings = HotelBooking::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('front.travel.hotel.history', [
            'bookings' => $hotelBookings
        ]);
    }
    
    public function edit($order_no)
    {
        $properties = Property::orderBy('name', 'asc')->get(); 

        $booking = HotelBooking::where('order_no', $order_no)->firstOrFail();
        
        return view('front.travel.hotel.edit', compact('booking', 'properties'));
    }

    public function cancelBooking(Request $request)
    {
        $request->validate([
            'order_no' => 'required|exists:hotel_bookings,order_no',
            'remarks' => 'nullable|string|max:500',
        ]);

        $booking = HotelBooking::where('order_no', $request->order_no)->firstOrFail();

        $now = Carbon::now();
        $currentHour = (int)$now->format('H');

        if ($currentHour >= 19 || $currentHour < 10) {
            return redirect()->back()->with('error', 'You can only cancel bookings during business hours (10 AM - 7 PM).');
        }

        $checkin = Carbon::parse($booking->checkin_date);
        if ($now->greaterThan($checkin->copy()->subHours(6))) {
            return redirect()->back()->with('error', 'You must cancel at least 6 hours before check-in.');
        }

        $booking->update([
            'status' => 4,
            'cancellation_remarks' => $request->remarks ?? 'No remarks provided',
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('front.travel.dashboard')
            ->with('success', 'Booking cancelled successfully.');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hotel_type' => 'required|integer|in:1,2',
            'property_id' => 'required_if:hotel_type,1|exists:properties,id|nullable',
            'hotel_preference' => 'required_if:hotel_type,2|string|nullable',
            'departure_date' => 'required|date',
            'return_date' => 'required|date|after:departure_date',
            'guest_number' => 'required|integer|min:1',
            'bill' => 'required|integer|in:1,2,3',
            'guest_type' => 'required',
            'matter_code' => 'required_if:bill,3|nullable|string|max:255',
            'remarks' => 'required_if:bill,1,2|nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validatedData = $validator->validated();

        $booking = HotelBooking::where('order_no', $request->order_no)->firstOrFail();

        $now = Carbon::now();
        $currentHour = (int)$now->format('H');

        if ($currentHour >= 19 || $currentHour < 10) {
            return redirect()->back()->with('error', 'You can only cancel bookings during business hours (10 AM - 7 PM).');
        }

        $checkin = Carbon::parse($booking->checkin_date);
        if ($now->greaterThan($checkin->copy()->subHours(6))) {
            return redirect()->back()->with('error', 'You must cancel at least 6 hours before check-in.');
        }
        $matterId = null;
        if ($request->bill == 3 && $request->filled('matter_code')) {
            $user = auth()->guard('front_user')->user();
            $matter = MatterCode::firstOrCreate(
                ['matter_code' => $request->matter_code],
                ['client_name' => $user->name ?? 'Unknown']
            );
            $matterId = $matter->id;
        }

        $newData = [
            'property_id' => $validatedData['property_id'],
            'checkin_date' => $validatedData['departure_date'],
            'checkout_date' => $validatedData['return_date'],
            'guest_number' => $validatedData['guest_number'],
            'bill_to' => $validatedData['bill'],
            'matter_code' => $matterId,
            'guest_type' => is_array($request->guest_type)
                            ? implode(',', $request->guest_type)
                            : $request->guest_type,
            'hotel_type' => $request->hotel_type,
            'food_preference' => $request->food_preference,
            'text' => $request->hotel_preference,
            'purpose' => $request->purpose,
            'description' => $request->description,
            'bill_to_remarks' => $validatedData['remarks'],
        ];

        $booking->update($newData);

        return redirect()->back()->with('success', 'Hotel booking updated successfully.');
    }

}

