<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CabBooking;
use App\Models\User;
use App\Models\MatterCode;
use App\Models\MailActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class CabController extends Controller
{
    public function index()
    {
        return view('front.travel.cab.index');
    }

    public function store(Request $request)
    {
        // Decode traveller JSON string into an array
        $request->merge([
            'traveller' => json_decode($request->traveller_data, true),
        ]);

        // Define validation rules
        $rules = [
            'user_id' => 'required|exists:users,id',
            'from' => 'required|string|max:255',
            'to' => 'required|string|max:255',
            'departure_date' => 'required|date',
            'departure_time' => 'required|string',
            'bill' => 'required|integer|in:1,2,3',
            'traveller' => 'required|array|min:1',
            'traveller.*.name' => 'required|string|max:255',
            'traveller.*.seat_preference' => 'nullable|string|max:255',
            'traveller.*.food_preference' => 'nullable|string|max:255',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Generate unique order number
            $last = CabBooking::latest('sequence_no')->first();
            $newSeq = $last ? $last->sequence_no + 1 : 1;
            $uniqueNo = 'FM-CB-' . date('Y') . '-' . sprintf("%'.05d", $newSeq);

            // Extract traveller details
            $travellers = collect($request->traveller);
            $travellerNames = $travellers->pluck('name')->implode(',');

            // Handle matter code (if applicable)
            $matterId = null;
            if ((int)$request->bill === 3 && !empty($request->matter_code)) {
                $user = User::find($request->user_id);
                $clientName = $user->name ?? 'Unknown';

                $matter = MatterCode::firstOrCreate(
                    ['matter_code' => $request->matter_code],
                    ['client_name' => $clientName]
                );

                $matterId = $matter->id;
            }

            // Create new cab booking record
            CabBooking::create([
                'user_id' => $request->user_id,
                'from_location' => $request->from,
                'to_location' => $request->to,
                'pickup_date' => Carbon::parse($request->departure_date)->format('Y-m-d'),
                'pickup_time' => $request->departure_time,
                'traveller' => $travellerNames,
                'bill_to' => $request->bill,
                'matter_code' => $matterId,
                'purpose' => $request->purpose,
                'description' => $request->description,
                'sequence_no' => $newSeq,
                'order_no' => $uniqueNo,
            ]);

            // Redirect with success
            return redirect()
                ->route('front.travel.dashboard')
                ->with('success', 'Cab booked successfully');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }
        
    public function history()
    {
        $userId = auth()->guard('front_user')->id();

        $cabBookings = CabBooking::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $processedBookings = $cabBookings->map(function ($booking) {
            $travellers = explode(',', $booking->traveller);

            $formattedTravellers = [];

            foreach ($travellers as $index => $traveller) {
                $formattedTravellers[] = [
                    'name' => trim($traveller),
                ];
            }

            $booking->traveller = $formattedTravellers;
            return $booking;
        });

        return view('front.travel.cab.history', [
            'bookings' => $processedBookings
        ]);
    }

    public function cancelBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_no' => 'required|exists:cab_bookings,order_no',
            'remarks' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $orderData = CabBooking::where('order_no', $request->order_no)->first();
        $oldValue = $orderData->status;

        $now = Carbon::now();
        $currentHour = (int) $now->format('H');
        $today = Carbon::today();
        $pickupDate = Carbon::parse($orderData->pickup_date);

        // Disallow cancellation if pickup date is before today
        if ($pickupDate->lessThan($today)) {
            return redirect()->back()
                ->with('error', 'Booking cannot be cancelled. Pickup date is before today.');
        }

        // Restrict actions between 7:00 PM and 10:00 AM
        if ($currentHour >= 19 || $currentHour < 10) {
            return redirect()->back()
                ->with('error', 'Your travel requisition is registered. Please contact the Travel Desk (Admin) directly during business hours in case of urgency.');
        }

        // Restrict cancellations less than 6 hours before pickup time
        $pickupDateTime = Carbon::parse($orderData->pickup_date . ' ' . $orderData->pickup_time);

        if ($now->greaterThan($pickupDateTime->copy()->subHours(6))) {
            return redirect()->back()
                ->with('error', 'Cancellations must be made at least 6 hours before the pickup time.');
        }

        $orderData->status = 4;
        $orderData->cancellation_remarks = $request->remarks ?? 'No remarks provided';
        $orderData->save();

        // DB::table('edit_logs')->insert([
        //     'table_name' => 'cab_bookings',
        //     'record_id' => $orderData->id,
        //     'field' => 'status',
        //     'old_value' => $oldValue,
        //     'new_value' => 4,
        //     'updated_by' => $orderData->user_id,
        //     'created_at' => now(),
        // ]);

        return redirect()
            ->route('front.travel.cab.history')
            ->with('success', 'Cab booking cancelled successfully.');

    }

    public function edit($order_no)
    {
        $booking = CabBooking::where('order_no', $order_no)->firstOrFail();
        $travellers = explode(',', $booking->traveller ?? '');
        $formattedTravellers = [];
        foreach ($travellers as $index => $traveller) {
            if (!$traveller) continue;
            $name = $traveller;
            $formattedTravellers[] = [
                'id' => $index + 1,
                'name' => $name,
            ];
        }
        $booking->traveller = $formattedTravellers;
        return view('front.travel.cab.edit', compact('booking'));
    }

    public function update(Request $request)
    {
        // Decode traveller JSON into array if present
        if ($request->filled('traveller_data')) {
            $request->merge(['traveller' => json_decode($request->traveller_data, true)]);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'order_no' => 'required|exists:cab_bookings,order_no',
            'bill' => 'required|integer|in:1,2,3',
            'from' => 'required|string|max:255',
            'to' => 'nullable|string|max:255',
            'departure_date' => 'required',
            'departure_time' => 'required',
            'traveller' => 'required|array|min:1',
            'traveller.*.name' => 'required|string|max:255',
            'purpose' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $order = CabBooking::where('order_no', $request->order_no)->firstOrFail();

            $now = Carbon::now();
            $pickupDate = Carbon::createFromFormat('d-m-Y', $request->departure_date);
            $pickupDateTime = Carbon::createFromFormat('d-m-Y H:i', "{$request->departure_date} {$request->departure_time}");

            // Business logic checks
            if ($pickupDate->isBefore(Carbon::today())) {
                return back()->with('error', 'Booking cannot be edited. Pickup date is before today.');
            }

            if ($now->hour >= 19 || $now->hour < 10) {
                return back()->with('error', 'Your Travel requisition is registered. We shall get back to you in next business hours. 
                    In case of any urgency, please contact the Travel Desk (Admin) directly.');
            }

            if ($now->greaterThan($pickupDateTime->copy()->subHours(6))) {
                return back()->with('error', 'Edits must be made at least 6 hours before the pickup time.');
            }

            // Prepare updated data
            $newData = [
                'bill_to' => $request->bill,
                'from_location' => $request->from,
                'to_location' => $request->to,
                'pickup_date' => $pickupDate->format('Y-m-d'),
                'pickup_time' => $request->departure_time,
                'matter_code' => $request->matter_code,
                'traveller' => collect($request->traveller)->pluck('name')->implode(','),
                'purpose' => $request->purpose,
                'description' => $request->description,
                'updated_at' => now(),
            ];

            // Detect and log changes
            $changedFields = collect($newData)->filter(
                fn($value, $field) => $value != ($order->$field ?? null)
            );

            if ($changedFields->isNotEmpty()) {
                $logs = $changedFields->map(fn($newValue, $field) => [
                    'table_name' => 'cab_bookings',
                    'record_id' => $order->id,
                    'field' => $field,
                    'old_value' => $order->$field,
                    'new_value' => $newValue,
                    'updated_by' => $order->user_id,
                    'created_at' => now(),
                ])->values()->all();

                DB::table('edit_logs')->insert($logs);
            }

            $order->update($newData);

            return redirect()->route('front.travel.cab.history')
                            ->with('success', 'Cab booking updated successfully.');

        } catch (\Throwable $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }



}
