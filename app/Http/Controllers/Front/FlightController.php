<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FlightBooking;
use App\Models\User;
use App\Models\MatterCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FlightController extends Controller
{
    public function index()
    {
        return view('front.travel.flight.index');
    }

    public function store(Request $request)
    {
        $request->merge([
            'traveller' => json_decode($request->traveller_data, true),
        ]);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'trip_type' => 'required|integer|in:1,2',
            'from' => 'required|string|max:255',
            'to' => 'required|string|max:255',
            'departure_date' => 'required',
            'departure-time' => 'required|string',
            'return_date' => 'nullable',
            'return-time' => 'nullable|string',
            'bill' => 'required|integer|in:1,2,3',
            'traveller' => 'required|array|min:1',
            'traveller.*.name' => 'required|string|max:255',
            'traveller.*.seat_preference' => 'nullable|string|max:255',
            'traveller.*.food_preference' => 'nullable|string|max:255',
            'matter_code' => 'required_if:bill,3|nullable|string|max:255',
            'remarks' => 'required_if:bill,1,2|nullable|string',
        ]);
        if ($request->trip_type == 2) {
            $rules['return_date'] = 'required|after_or_equal:departure_date';
            $rules['return-time'] = 'required|string';
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $last = FlightBooking::latest('sequence_no')->first();
            $newSeq = $last ? $last->sequence_no + 1 : 1;
            $uniqueNo = 'FM-FB-' . date('Y') . '-' . sprintf("%'.05d", $newSeq);

            $travellers = collect($request->traveller);
            $travellerNames = $travellers->pluck('name')->implode(',');
            $seatPreferences = $travellers->pluck('seat_preference')->filter()->implode(',');
            $foodPreferences = $travellers->pluck('food_preference')->filter()->implode(',');

            $matterId = null;
            if ((int)$request->bill === 3 && !empty($request['matter_code'])) {
                $user = User::find($request->user_id);
                $clientName = $user->name ?? 'Unknown';
                
                $matter = MatterCode::firstOrCreate(
                    ['matter_code' => $request['matter_code']],
                    ['client_name' => $clientName]
                );
                
                $matterId = $matter->id;
            }

            $tripType = $request->trip_type;

            // If it's a one-way trip, force return_date and return_time to null
            $returnDate = $tripType == 1 ? null : $request->input('return_date');
            $returnTime = $tripType == 1 ? null : $request->input('return-time');

            FlightBooking::create([
                'user_id' => $request->user_id,
                'trip_type' => $tripType,
                'from' => $request->from,
                'to' => $request->to,
                'departure_date' => $request->departure_date,
                'arrival_time' => $request->input('departure-time'),
                'return_date' => $returnDate,
                'return_time' => $returnTime,
                'traveller' => $travellerNames,
                'seat_preference' => $seatPreferences,
                'food_preference' => $foodPreferences,
                'bill_to' => $request->bill,
                'matter_code' => $matterId,
                'purpose' => $request->purpose,
                'description' => $request->description,
                'sequence_no' => $newSeq,
                'order_no' => $uniqueNo,
                'bill_to_remarks' => $request->remarks,
            ]);

            return redirect()
                ->route('front.travel.dashboard')
                ->with('success', 'Flight booking created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function searchAirports(Request $request)
    {
        $term = $request->get('term', '');

        $airports = DB::table('airport_list')
            ->where('airport', 'LIKE', "%{$term}%")
            ->orWhere('city', 'LIKE', "%{$term}%")
            ->orWhere('country', 'LIKE', "%{$term}%")
            ->orderBy('city')
            ->limit(10)
            ->get(['id', 'airport', 'city', 'country', 'code']);

        $results = $airports->map(function ($a) {
            return [
                'label' => "{$a->airport}",
                'value' => "{$a->airport}",
            ];
        });

        return response()->json($results);
    }

    public function history()
    {
        $userId = auth()->guard('front_user')->id();

        $flightBookings = FlightBooking::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $processedBookings = $flightBookings->map(function ($booking) {
            $travellers = explode(',', $booking->traveller);
            $seatPreferences = explode(',', $booking->seat_preference ?? '');
            $foodPreferences = explode(',', $booking->food_preference ?? '');

            $formattedTravellers = [];

            foreach ($travellers as $index => $traveller) {
                $formattedTravellers[] = [
                    'name' => trim($traveller),
                    'seat_preference' => $seatPreferences[$index] ?? null,
                    'food_preference' => $foodPreferences[$index] ?? null,
                ];
            }

            $booking->traveller = $formattedTravellers; 
            return $booking;
        });

        return view('front.travel.flight.history', [
            'bookings' => $processedBookings
        ]);
    }

    public function cancelBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_no' => 'required|exists:flight_bookings,order_no',
            'remarks' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $orderData = FlightBooking::where('order_no', $request->order_no)->first();
        $oldValue = $orderData->status;

        $now = now();
        $currentHour = (int) $now->format('H');
        $today = now()->startOfDay();
        $departureDate = \Carbon\Carbon::parse($orderData->departure_date);

        if ($departureDate->lessThan($today)) {
            return redirect()->back()
                ->with('error', 'Booking cannot be cancelled. Departure date is before today.');
        }

        if ($currentHour >= 19 || $currentHour < 10) {
            return redirect()->back()
                ->with('error', 'Your travel requisition is registered. We shall get back to you in next business hours. In case of any urgency, please contact the Travel Desk (Admin).');
        }

        $departureDateTime = \Carbon\Carbon::parse($orderData->departure_date);
        if ($now->greaterThan($departureDateTime->copy()->subHours(6))) {
            return redirect()->back()
                ->with('error', 'Cancellations must be made at least 6 hours before departure.');
        }

        $orderData->update([
            'status' => 4,
            'cancellation_remarks' => $request->remarks ?? 'No remarks provided',
        ]);

        // DB::table('edit_logs')->insert([
        //     'table_name' => 'flight_bookings',
        //     'record_id' => $orderData->id,
        //     'field' => 'status',
        //     'old_value' => $oldValue,
        //     'new_value' => 4,
        //     'updated_by' => $orderData->user_id,
        //     'created_at' => now(),
        // ]);

        return redirect()
            ->route('front.travel.flight.history')
            ->with('success', 'Booking cancellation requested successfully.');
    }

    public function edit($order_no)
    {
        $booking = FlightBooking::where('order_no', $order_no)->firstOrFail();

        $travellers = explode(',', $booking->traveller ?? '');
        $seatPreferences = explode(',', $booking->seat_preference ?? '');
        $foodPreferences = explode(',', $booking->food_preference ?? '');

        $formattedTravellers = [];

        foreach ($travellers as $index => $traveller) {
            if (!$traveller) continue;
            $name = $traveller;

            $formattedTravellers[] = [
                'id' => $index + 1,
                'name' => $name,
                'seat_preference' => $seatPreferences[$index] ?? 'N/A',
                'food_preference' => $foodPreferences[$index] ?? 'N/A',
            ];
        }

        $booking->traveller = $formattedTravellers;

        return view('front.travel.flight.edit', compact('booking'));
    }

    public function update(Request $request)
    {
        $request->merge([
            'traveller' => json_decode($request->traveller_data, true),
        ]);

        $validator = Validator::make($request->all(), [
            'order_no' => 'required|exists:flight_bookings,order_no',
            'from' => 'required|string|max:255',
            'to' => 'required|string|max:255',
            'departure_date' => 'required',
            'departure-time' => 'required|string',
            'return_date' => 'nullable',
            'return-time' => 'nullable|string',
            'bill' => 'required|integer|in:1,2,3',
            'traveller' => 'required|array|min:1',
            'traveller.*.name' => 'required|string|max:255',
            'traveller.*.seat_preference' => 'nullable|string|max:255',
            'traveller.*.food_preference' => 'nullable|string|max:255',
            'matter_code' => 'required_if:bill,3|nullable|string|max:255',
            'remarks' => 'required_if:bill,1,2|nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $booking = FlightBooking::where('order_no', $request->order_no)->firstOrFail();

        // Business restrictions
        $now = now();
        $currentHour = (int)$now->format('H');
        $today = now()->startOfDay();
        $departureDate = \Carbon\Carbon::parse($booking->departure_date);

        if ($departureDate->lessThan($today)) {
            return redirect()->back()
                ->with('error', 'Booking cannot be edited. Departure date is before today.');
        }

        if ($currentHour >= 19 || $currentHour < 10) {
            return redirect()->back()
                ->with('error', 'Your Travel requisition is registered. We shall get back to you in next business hours.In case of any urgency, you may contact the Travel Desk (Admin) directly on mobile phone.');
        }

        $departureDateTime = \Carbon\Carbon::parse($booking->departure_date);
        if ($now->greaterThan($departureDateTime->copy()->subHours(6))) {
            return redirect()->back()
                ->with('error', 'Edits must be made at least 6 hours before the departure time.');
        }

        // Prepare traveller data
        $travellers = collect($request->traveller);
        $travellerNames = $travellers->pluck('name')->implode(',');
        $seatPreferences = $travellers->pluck('seat_preference')->filter()->implode(',');
        $foodPreferences = $travellers->pluck('food_preference')->filter()->implode(',');

        // Handle matter code (same logic as store)
        $matterId = null;
        if ($request->bill == 3 && $request->filled('matter_code')) {
            $user = auth()->guard('front_user')->user();
            $matter = \App\Models\MatterCode::firstOrCreate(
                ['matter_code' => $request->matter_code],
                ['client_name' => $user->name ?? 'Unknown']
            );
            $matterId = $matter->id;
        }

        // Prepare updated data
        $newData = [
            'from' => $request->from,
            'to' => $request->to,
            'trip_type' => $request->trip_type,
            'departure_date' => $request->departure_date,
            'arrival_time' => $request->input('departure-time'),
            'return_date' => $request->input('return_date'),
            'return_time' => $request->input('return-time'),
            'bill_to' => $request->bill,
            'matter_code' => $matterId,
            'purpose' => $request->purpose,
            'description' => $request->description,
            'traveller' => $travellerNames,
            'seat_preference' => $seatPreferences,
            'food_preference' => $foodPreferences,
            'bill_to_remarks' => $request->remarks,
        ];

        // Log changed fields
        foreach ($newData as $field => $newValue) {
            $oldValue = $booking->$field ?? null;
            if ($newValue != $oldValue) {
                DB::table('edit_logs')->insert([
                    'table_name' => 'flight_bookings',
                    'record_id' => $booking->id,
                    'field' => $field,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                    'updated_by' => $booking->user_id,
                    'created_at' => now(),
                ]);
            }
        }

        // Update booking
        $booking->update($newData);

        return redirect()
            ->route('front.travel.flight.history')
            ->with('success', 'Flight booking updated successfully.');
    }

   
}
