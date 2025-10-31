<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrainBooking;
use App\Models\User;
use App\Models\MatterCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TrainController extends Controller
{
    public function index()
    {
        return view('front.travel.train.index');
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
            'departure_date' => 'required|date',
            'departure_time' => 'required|string',
            'return_date' => 'nullable|date',
            'return_time' => 'nullable|string',
            'bill' => 'required|integer|in:1,2,3',
            'traveller' => 'required|array',
            'traveller.*.name' => 'required|string|max:255',
            'traveller.*.seat_preference' => 'nullable|string|max:255',
            'traveller.*.food_preference' => 'nullable|string|max:255',
            'preference' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $validatedData = $validator->validated();

            // Generate sequence number
            $orderData = TrainBooking::select('sequence_no')->latest('sequence_no')->first();
            $new_sequence_no = $orderData ? ((int) $orderData->sequence_no + 1) : 1;
            $ordNo = sprintf("%'.05d", $new_sequence_no);

            if ($request['preference'] == 1) {
                $uniqueNo = 'FM-TB-' . date('Y') . '-' . $ordNo;
            } else {
                $uniqueNo = 'FM-BB-' . date('Y') . '-' . $ordNo;
            }

            $travellerNames = collect($validatedData['traveller'])->pluck('name')->implode(',');
            $seatPreferences = collect($validatedData['traveller'])->pluck('seat_preference')->implode(',');
            $foodPreferences = collect($validatedData['traveller'])->pluck('food_preference')->implode(',');

            $matterId = null;
            if ((int)$validatedData['bill'] === 3 && !empty($request['matter_code'])) {
                $user = User::find($validatedData['user_id']);
                $clientName = $user->name ?? 'Unknown';
                
                $matter = MatterCode::firstOrCreate(
                    ['matter_code' => $request['matter_code']],
                    ['client_name' => $clientName]
                );
                
                $matterId = $matter->id;
            }

            $tripType = $validatedData['trip_type'] ?? null;

            // If trip_type == 1 (one-way), set return values to null
            $returnDate = $tripType == 1 ? null : ($request['return_date'] ?? null);
            $returnTime = $tripType == 1 ? null : ($request['return_time'] ?? null);

            $trainBooking = TrainBooking::create([
                'user_id' => $validatedData['user_id'],
                'from' => $validatedData['from'],
                'to' => $validatedData['to'],
                'travel_date' => $validatedData['departure_date'],
                'departure_time' => $validatedData['departure_time'] ?? null,
                'bill_to' => $validatedData['bill'],
                'matter_code' => $matterId,
                'type' => $validatedData['preference'] ?? null,
                'trip_type' => $tripType,
                'return_date' => $returnDate,
                'return_time' => $returnTime,
                'traveller' => $travellerNames ?? null,
                'seat_preference' => $seatPreferences ?? null,
                'food_preference' => $foodPreferences ?? null,
                'purpose' => $request['purpose'] ?? null,
                'description' => $request['description'] ?? null,
                'sequence_no' => $new_sequence_no ?? null,
                'order_no' => $uniqueNo ?? null,
            ]);

            return redirect()
                ->route('front.travel.train.index')
                ->with('success', 'Train/Bus booked successfully!');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Something went wrong. Please try again.')
                ->withInput();
        }
    }

    public function searchStation(Request $request)
    {
        $term = $request->get('term', '');

        $stations = DB::table('station_list')
            ->where('station_name', 'LIKE', "%{$term}%")
            ->orWhere('station', 'LIKE', "%{$term}%")
            ->orWhere('code', 'LIKE', "%{$term}%")
            ->limit(10)
            ->get(['id', 'station_name', 'station', 'code']);

        $results = $stations->map(function ($station) {
            return [
                'label' => "{$station->station_name}",
                'value' => "{$station->station_name}",
            ];
        });

        return response()->json($results);
    }

     public function history()
    {
        $userId = auth()->guard('front_user')->id();

        $trainBookings = TrainBooking::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $processedBookings = $trainBookings->map(function ($booking) {
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

        return view('front.travel.train.history', [
            'bookings' => $processedBookings
        ]);
    }


    public function cancelBooking(Request $request)
    {
        $request->validate([
            'order_no' => 'required|exists:train_bookings,order_no',
            'remarks' => 'required|string|max:500',
        ]);

        $booking = TrainBooking::where('order_no', $request->order_no)->first();

        if (!$booking) {
            return redirect()->back()->with('error', 'Booking not found.');
        }

        $now = now();
        $currentHour = (int) $now->format('H');

        // Restrict cancellation between 7 PM and 10 AM
        if ($currentHour >= 19 || $currentHour < 10) {
            return redirect()->back()->with('error',
                'Your travel requisition is registered. We shall get back to you in next business hours. 
                In case of any urgency, please contact the Travel Desk (Admin) directly.'
            );
        }

        // Prevent cancellation less than 6 hours before departure
        $departureTime = \Carbon\Carbon::parse($booking->travel_date);
        if ($now->greaterThan($departureTime->copy()->subHours(6))) {
            return redirect()->back()->with('error',
                'Cancellations must be made at least 6 hours before the departure time.'
            );
        }

        // Prevent double cancellation
        if ($booking->status == 4) {
            return redirect()->back()->with('error', 'This booking has already been cancelled.');
        }

        // Cancel booking
        $booking->update([
            'status' => 4,
            'cancellation_remarks' => $request->remarks,
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('front.travel.train.history')
            ->with('success', 'Your booking has been cancelled successfully.');
    }



}
