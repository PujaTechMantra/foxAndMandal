<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $phoneNumber = $request->mobile;
        $otp = rand(100000, 999999); // Generate a 6-digit OTP
        // $expiresAt = Carbon::now()->addMinutes(10); // OTP valid for 10 minutes

        $user = User::where('mobile', $phoneNumber)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Update OTP and expiration
        $user->update([
            'otp' => $otp,
        ]);

        // Here you should send OTP to the user's phone number
        // For this example, we'll just return the OTP in the response for testing purposes
        return response()->json(['message' => 'OTP sent successfully', 
        'otp' => $otp,
        'name'=> $user->name,
        'email'=> $user->email,
        'mobile'=> $user->mobile,
    ], 200);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'otp' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $phoneNumber = $request->mobile;
        $otp = $request->otp;

        $user = User::where('mobile', $phoneNumber)
                    ->where('otp', $otp)
                    // ->where('otp_expires_at', '>', Carbon::now())
                    ->first();
        if ($user) {
            

            return response()->json(['message' => 'OTP verified successfully'], 200);
        }

        // return response()->json(['error' => 'Invalid or expired OTP'], 400);
    }

}