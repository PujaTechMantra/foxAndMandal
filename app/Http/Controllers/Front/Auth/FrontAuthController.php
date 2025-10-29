<?php

namespace App\Http\Controllers\Front\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class FrontAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('front.auth.login');
    }

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::where('mobile', $request->mobile)->first();

        if (!$user) {
            return back()->with('error', 'No user found with this mobile number');
        }

        if ($user->status != 1) {
            return back()->with('error', 'Your account is blocked. Please contact admin.');
        }

        $otp = random_int(1000, 9999);
        $user->update(['otp' => $otp]);

        // Here you would normally send the OTP via SMS
        // For testing, we'll just show it on screen
        return redirect()->route('front.login')->with([
            'otp_sent' => true,
            'mobile' => $user->mobile,
            'otp' => $otp, // remove in production
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'mobile' => 'required|numeric',
            'otp' => 'required|numeric',
        ]);

        $user = User::where('mobile', $request->mobile)
                    ->where('otp', $request->otp)
                    ->first();

        if (!$user) {
            return back()->with('error', 'Invalid OTP or mobile number');
        }

        Auth::guard('front_user')->login($user);

        $user->update(['otp' => null]);

        return redirect()->route('front.dashboard')->with('success', 'Logged in successfully!');
        
    }

    public function logout(Request $request)
    {
         Auth::guard('front_user')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('front.login')->with('success', 'Logged out successfully.');
    }

    public function dashboard()
    {
        return view('front.dashboard');
    }
}
