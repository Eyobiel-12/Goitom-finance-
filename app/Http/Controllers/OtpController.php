<?php

namespace App\Http\Controllers;

use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class OtpController extends Controller
{
    public function __construct(
        private OtpService $otpService
    ) {}

    /**
     * Send OTP for login
     */
    public function sendLoginOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = $request->email;
        $key = 'otp-login:' . $email;

        // Rate limiting: max 5 attempts per hour
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Te veel pogingen. Probeer het over {$seconds} seconden opnieuw.",
            ]);
        }

        $result = $this->otpService->sendLoginOtp($email, $request->ip());

        if ($result['success']) {
            RateLimiter::hit($key, 3600); // 1 hour
            return response()->json($result);
        }

        RateLimiter::hit($key, 3600);
        return response()->json($result, 422);
    }

    /**
     * Send OTP for registration
     */
    public function sendRegistrationOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = $request->email;
        $key = 'otp-registration:' . $email;

        // Rate limiting: max 5 attempts per hour
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Te veel pogingen. Probeer het over {$seconds} seconden opnieuw.",
            ]);
        }

        $result = $this->otpService->sendRegistrationOtp($email, $request->ip());

        if ($result['success']) {
            RateLimiter::hit($key, 3600); // 1 hour
            return response()->json($result);
        }

        RateLimiter::hit($key, 3600);
        return response()->json($result, 422);
    }

    /**
     * Verify OTP and login
     */
    public function verifyLoginOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $key = 'otp-verify-login:' . $request->email;

        // Rate limiting: max 10 attempts per hour
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'code' => "Te veel pogingen. Probeer het over {$seconds} seconden opnieuw.",
            ]);
        }

        $result = $this->otpService->verifyLoginOtp($request->email, $request->code);

        if ($result['success']) {
            Auth::login($result['user']);
            RateLimiter::clear($key);
            
            $request->session()->regenerate();
            
            return response()->json([
                'success' => true,
                'message' => 'Succesvol ingelogd!',
                'redirect' => route('dashboard', [], false)
            ]);
        }

        RateLimiter::hit($key, 3600);
        return response()->json($result, 422);
    }

    /**
     * Verify OTP for registration
     */
    public function verifyRegistrationOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $key = 'otp-verify-registration:' . $request->email;

        // Rate limiting: max 10 attempts per hour
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'code' => "Te veel pogingen. Probeer het over {$seconds} seconden opnieuw.",
            ]);
        }

        $result = $this->otpService->verifyRegistrationOtp($request->email, $request->code);

        if ($result['success']) {
            RateLimiter::clear($key);
            
            // Store verified email in session for registration
            $request->session()->put('verified_email', $request->email);
            
            return response()->json([
                'success' => true,
                'message' => 'E-mailadres geverifieerd! U kunt nu uw account aanmaken.',
                'redirect' => route('register', [], false)
            ]);
        }

        RateLimiter::hit($key, 3600);
        return response()->json($result, 422);
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'type' => 'required|in:login,registration',
        ]);

        $email = $request->email;
        $type = $request->type;
        $key = 'otp-resend:' . $email . ':' . $type;

        // Rate limiting: max 3 resends per hour
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Te veel pogingen. Probeer het over {$seconds} seconden opnieuw.",
            ]);
        }

        if ($type === 'login') {
            $result = $this->otpService->sendLoginOtp($email, $request->ip());
        } else {
            $result = $this->otpService->sendRegistrationOtp($email, $request->ip());
        }

        if ($result['success']) {
            RateLimiter::hit($key, 3600);
            return response()->json($result);
        }

        RateLimiter::hit($key, 3600);
        return response()->json($result, 422);
    }
}