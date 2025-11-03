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
            'property_id' => 'nullable|exists:properties,id',
            'departure_date' => 'required',
            'return_date' => 'required|after:departure_date',
            'guest_number' => 'required|integer|min:1',
            'bill' => 'required|integer|in:1,2,3',
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
        if ($request->matter_code) {
            $user = User::find($validatedData['user_id']);
            $clientName = $user->name ?? 'Unknown';
            $matter = MatterCode::firstOrCreate(
                ['matter_code' => $request->matter_code],
                ['client_name' => $clientName]
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
            'text' => $request->text,
            'seat_preference' => $request->seat_preference,
            'food_preference' => $request->food_preference,
            'purpose' => $request->purpose,
            'description' => $request->description,
            'sequence_no' => $new_sequence_no,
            'order_no' => $uniqueNo,
        ]);

        if (!$booking) {
            return redirect()->back()->with('error', 'Failed to create hotel booking.');
        }

        // Email log
        $user = User::find($validatedData['user_id']);
        // MailActivity::create([
        //     'email' => $user->email,
        //     'type' => 'hotel-booking-information-sent',
        //     'sent_at' => now(),
        //     'status' => 'sent',
        // ]);

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

   public function update(Request $request, $id)
    {
        $booking = HotelBooking::findOrFail($id);

        $now = Carbon::now();
        $currentHour = (int)$now->format('H');

        // Restrict updates outside business hours
        if ($currentHour >= 19 || $currentHour < 10) {
            return redirect()->back()->with('error', 'You can only modify bookings during business hours (10 AM - 7 PM).');
        }

        // Prevent updates within 6 hours of check-in
        $checkin = Carbon::parse($booking->checkin_date);
        if ($now->greaterThan($checkin->copy()->subHours(6))) {
            return redirect()->back()->with('error', 'You must make changes at least 6 hours before check-in.');
        }

        $newData = [
            'bill_to' => $request->bill_to,
            'room_id' => $request->room_id,
            'property_id' => $request->property_id,
            'guest_type' => $request->guest_type,
            'checkin_date' => $request->departure_date,
            'checkout_date' => $request->return_date,
            'matter_code' => $request->matter_code,
            'room_number' => $request->room_number,
            'guest_number' => $request->guest_number,
            'hotel_type' => $request->hotel_type,
            'text' => $request->text,
            'seat_preference' => $request->seat_preference,
            'food_preference' => $request->food_preference,
            'purpose' => $request->purpose,
            'description' => $request->description,
            'updated_at' => now(),
        ];

        $booking->update($newData);

        return redirect()->back()->with('success', 'Hotel booking updated successfully.');
    }


}

