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
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ], 400);
        }
    
        try {
            $phoneNumber = $request->input('mobile');
    
            $user = User::where('mobile', $phoneNumber)->first();
    
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'error' => 'User not found'
                ], 404);
            }
    
            if ($user->status == 1) {
                $otp = random_int(1000, 9999);
    
                $updateSuccessful = $user->update(['otp' => $otp]);
    
                if (!$updateSuccessful) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Failed to send OTP, please try again later'
                    ], 500);
                }
    
                return response()->json([
                    'status' => true,
                    'message' => 'OTP sent successfully',
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'otp' => $user->otp,
                    'user_status' => $user->status
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account is temporarily blocked. Contact Admin.'
                ], 403); 
            }
    
        } catch (\Exception $e) {
            Log::error('OTP sending error: ' . $e->getMessage());
    
            return response()->json([
                'message' => 'An error occurred while sending OTP.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    


    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'otp' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false,'error' => $validator->errors()], 400);
        }
        try{
            $phoneNumber = $request->mobile;
            $otp = $request->otp;

            $user = User::where('mobile', $phoneNumber)
                        ->where('otp', $otp)
                        ->first();
            if ($user) {
                return response()->json(['status' => true,'message' => 'OTP verified successfully','data' =>$user], 200);
            }else {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid OTP or mobile number'
                    
                ], 401);
            }
        } catch (\Exception $e) {
            DB::rollBack(); 
            Log::error('Book transfer error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred during the book transfer.',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

}