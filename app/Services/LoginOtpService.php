<?php

namespace App\Services;

use App\Http\DTOs\Auth\OtpVerificationResult;
use App\Models\User;
use App\Models\LoginOtp;
use Illuminate\Support\Facades\Hash;

class LoginOtpService
{
    public function create(User $user, ?string $ipAddress = null, ?string $userAgent = null): array
    {
        $otp = LoginOtp::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($otp) {
            return [
                'already_sent' => true,
                'otp' => $otp
            ];
        }

        LoginOtp::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

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

        return [
            'already_sent' => false,
            'otp' => $otp,
        ];
    }

    public function resend(User $user, ?string $ipAddress = null, ?string $userAgent = null): array
    {
        $otp = LoginOtp::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($otp) {
            throw new \Exception('کد تأیید فعال قبلاً ارسال شده است. لطفاً منتظر بمانید تا منقضی شود.');
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

        return [
            'otp' => $otp,
        ];
    }

    public function verify(User $user, string $code): OtpVerificationResult
    {
        $otp = LoginOtp::where('user_id', $user->id)
            ->whereNull('used_at')
            ->latest()
            ->first();

        if (!$otp) {
            return OtpVerificationResult::notFound();
        }

        if ($otp->attempts >= 5) {
            return OtpVerificationResult::tooManyAttempts();
        }

        if (!Hash::check($code, $otp->code)) {
            $otp->increment('attempts');

            if ($otp->attempts >= 5) {
                $otp->update(['used_at' => now()]);
            }

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
