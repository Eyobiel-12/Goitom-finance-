<?php

namespace App\Services;

use App\Models\Otp;
use App\Models\OtpAttempt;
use App\Models\User;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class OtpService
{
    /**
     * Send OTP for login
     */
    public function sendLoginOtp(string $email, ?string $ipAddress = null): array
    {
        // Check if user exists
        $user = User::where('email', $email)->first();
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Geen account gevonden met dit e-mailadres.'
            ];
        }

        try {
            $otp = Otp::createOtp($email, 'login', $ipAddress);
            Mail::to($email)->send(new OtpMail($otp));

            Log::info('Login OTP sent', [
                'email' => $email,
                'ip_address' => $ipAddress,
                'otp_id' => $otp->id
            ]);

            OtpAttempt::create([
                'email' => $email,
                'type' => 'login',
                'ip_address' => $ipAddress,
                'user_agent' => request()->userAgent(),
                'success' => true,
                'reason' => 'sent',
            ]);

            return [
                'success' => true,
                'message' => 'Inlogcode verzonden naar uw e-mailadres.'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send login OTP', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            OtpAttempt::create([
                'email' => $email,
                'type' => 'login',
                'ip_address' => $ipAddress,
                'user_agent' => request()->userAgent(),
                'success' => false,
                'reason' => 'send_failed',
            ]);

            return [
                'success' => false,
                'message' => 'Er is een fout opgetreden bij het verzenden van de code. Probeer het opnieuw.'
            ];
        }
    }

    /**
     * Send OTP for registration
     */
    public function sendRegistrationOtp(string $email, ?string $ipAddress = null): array
    {
        // Check if user already exists
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            return [
                'success' => false,
                'message' => 'Er bestaat al een account met dit e-mailadres.'
            ];
        }

        try {
            $otp = Otp::createOtp($email, 'registration', $ipAddress);
            Mail::to($email)->send(new OtpMail($otp));

            Log::info('Registration OTP sent', [
                'email' => $email,
                'ip_address' => $ipAddress,
                'otp_id' => $otp->id
            ]);

            OtpAttempt::create([
                'email' => $email,
                'type' => 'registration',
                'ip_address' => $ipAddress,
                'user_agent' => request()->userAgent(),
                'success' => true,
                'reason' => 'sent',
            ]);

            return [
                'success' => true,
                'message' => 'Verificatiecode verzonden naar uw e-mailadres.'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send registration OTP', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            OtpAttempt::create([
                'email' => $email,
                'type' => 'registration',
                'ip_address' => $ipAddress,
                'user_agent' => request()->userAgent(),
                'success' => false,
                'reason' => 'send_failed',
            ]);

            return [
                'success' => false,
                'message' => 'Er is een fout opgetreden bij het verzenden van de code. Probeer het opnieuw.'
            ];
        }
    }

    /**
     * Verify OTP and login user
     */
    public function verifyLoginOtp(string $email, string $code): array
    {
        if (!Otp::verifyOtp($email, $code, 'login')) {
            OtpAttempt::create([
                'email' => $email,
                'type' => 'login',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'success' => false,
                'reason' => 'invalid',
            ]);
            return [
                'success' => false,
                'message' => 'Ongeldige of verlopen verificatiecode.'
            ];
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Gebruiker niet gevonden.'
            ];
        }

        // Log the successful OTP login
        Log::info('Successful OTP login', [
            'user_id' => $user->id,
            'email' => $email
        ]);

        OtpAttempt::create([
            'email' => $email,
            'type' => 'login',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'success' => true,
            'reason' => 'verified',
        ]);

        return [
            'success' => true,
            'user' => $user,
            'message' => 'Succesvol ingelogd!'
        ];
    }

    /**
     * Verify OTP for registration
     */
    public function verifyRegistrationOtp(string $email, string $code): array
    {
        if (!Otp::verifyOtp($email, $code, 'registration')) {
            return [
                'success' => false,
                'message' => 'Ongeldige of verlopen verificatiecode.'
            ];
        }

        return [
            'success' => true,
            'message' => 'E-mailadres geverifieerd! U kunt nu uw account aanmaken.'
        ];
    }

    /**
     * Clean up expired OTPs
     */
    public function cleanupExpiredOtps(): int
    {
        return Otp::where('expires_at', '<', now())->delete();
    }

    /**
     * Get remaining attempts for an email
     */
    public function getRemainingAttempts(string $email, string $type): int
    {
        $recentAttempts = Otp::where('email', $email)
            ->where('type', $type)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        return max(0, 5 - $recentAttempts); // Max 5 attempts per hour
    }
}
