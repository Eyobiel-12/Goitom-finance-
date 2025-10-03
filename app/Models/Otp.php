<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class Otp extends Model
{
    protected $fillable = [
        'email',
        'code',
        'type',
        'expires_at',
        'used',
        'ip_address',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    /**
     * Generate a 6-digit OTP code
     */
    public static function generateCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new OTP for the given email and type
     */
    public static function createOtp(string $email, string $type, ?string $ipAddress = null): self
    {
        // Invalidate any existing unused OTPs for this email and type
        self::where('email', $email)
            ->where('type', $type)
            ->where('used', false)
            ->update(['used' => true]);

        return self::create([
            'email' => $email,
            'code' => self::generateCode(),
            'type' => $type,
            'expires_at' => now()->addMinutes(10), // OTP expires in 10 minutes
            'used' => false,
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Verify an OTP code
     */
    public static function verifyOtp(string $email, string $code, string $type): bool
    {
        $otp = self::where('email', $email)
            ->where('code', $code)
            ->where('type', $type)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($otp) {
            $otp->update(['used' => true]);
            return true;
        }

        return false;
    }

    /**
     * Check if OTP is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Scope for valid (unused and not expired) OTPs
     */
    public function scopeValid($query)
    {
        return $query->where('used', false)->where('expires_at', '>', now());
    }
}