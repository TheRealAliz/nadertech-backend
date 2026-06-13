<?php

namespace App\Services;

use App\Http\DTOs\Auth\OtpVerificationResult;
use App\Models\User;
use App\Models\LoginOtp;
use Illuminate\Support\Facades\Hash;

class LoginOtpService
{
    public function create(User $user, ?string $ipAddress = null, ?string $userAgent = null): LoginOtp
    {
        $otp = LoginOtp::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->first();

        if ($otp) {
            return $otp;
        }

        $plainCode = $this->makeCode();

        $otp = LoginOtp::create([
            'user_id' => $user->id,
            'mobile' => $user->mobile,
            'code' => Hash::make($plainCode),
            'expires_at' => now()->addMinutes(3),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        // use sms provider

        return $otp;
    }

    public function verify(User $user, string $code): OtpVerificationResult
    {
        $otp = LoginOtp::where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$otp) {
            return OtpVerificationResult::notFound();
        }

        if ($otp->expires_at->isPast()) {
            return OtpVerificationResult::expired();
        }

        if ($otp->attempts >= 5) {
            return OtpVerificationResult::tooManyAttempts();
        }

        if (!Hash::check($code, $otp->code)) {
            return OtpVerificationResult::invalidCode();
        }

        $otp->update(['used_at' => now()]);
        return OtpVerificationResult::success();
    }

    public function makeCode()
    {
        return app()->environment('local', 'testing')
            ? '123456'
            : str_pad(random_int(0, 999_999), 6, '0', STR_PAD_LEFT);
    }
}
