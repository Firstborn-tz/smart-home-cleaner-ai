<?php

namespace App\Services\Verification;

use App\Models\Booking;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class VerificationCodeService
{
    private int $codeExpiryMinutes = 30;
    private int $maxAttempts = 3;
    private int $maxRegenerations = 5;

    /**
     * Generate a new 6-digit verification code
     */
    public function generateCode(Booking $booking): string
    {
        // Check generation limits
        $count = VerificationCode::where('booking_id', $booking->id)->count();
        
        if ($count >= $this->maxRegenerations) {
            throw new \RuntimeException('Maximum code regeneration limit reached');
        }

        // Invalidate existing unused codes
        VerificationCode::where('booking_id', $booking->id)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        // Generate secure 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $codeHash = Hash::make($code);

        // Save hashed code
        VerificationCode::create([
            'booking_id' => $booking->id,
            'code_hash' => $codeHash,
            'expires_at' => now()->addMinutes($this->codeExpiryMinutes),
            'is_used' => false,
            'generation_count' => $count + 1,
            'attempt_count' => 0,
            'delivery_method' => 'in-app',
        ]);

        // Update booking
        $booking->update([
            'verification_code_hash' => $codeHash,
        ]);

        Log::info('Verification code generated', [
            'booking_id' => $booking->id,
            'generation' => $count + 1,
        ]);

        return $code;
    }

    /**
     * Verify the 6-digit code
     */
    public function verifyCode(Booking $booking, string $code): bool
    {
        $verificationCode = VerificationCode::where('booking_id', $booking->id)
            ->where('is_used', false)
            ->latest()
            ->first();

        if (!$verificationCode) {
            Log::warning('No valid verification code found', ['booking_id' => $booking->id]);
            return false;
        }

        // Check expiration
        if ($verificationCode->expires_at->isPast()) {
            Log::warning('Verification code expired', ['booking_id' => $booking->id]);
            return false;
        }

        // Check attempt limit
        if ($verificationCode->attempt_count >= $this->maxAttempts) {
            Log::warning('Max verification attempts exceeded', ['booking_id' => $booking->id]);
            return false;
        }

        // Increment attempts
        $verificationCode->increment('attempt_count', 1, [
            'last_attempt_at' => now(),
        ]);

        // Verify hash
        if (!Hash::check($code, $verificationCode->code_hash)) {
            return false;
        }

        // Mark as used
        $verificationCode->update([
            'is_used' => true,
            'verified_at' => now(),
        ]);

        return true;
    }

    /**
     * Get remaining attempts
     */
    public function getRemainingAttempts(Booking $booking): int
    {
        $code = VerificationCode::where('booking_id', $booking->id)
            ->where('is_used', false)
            ->latest()
            ->first();

        if (!$code) return 0;

        return max(0, $this->maxAttempts - $code->attempt_count);
    }

    /**
     * Check if code can be regenerated
     */
    public function canRegenerate(Booking $booking): bool
    {
        $count = VerificationCode::where('booking_id', $booking->id)->count();
        return $count < $this->maxRegenerations;
    }
}