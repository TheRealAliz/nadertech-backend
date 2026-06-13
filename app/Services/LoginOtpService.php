<?php

namespace App\Services;

use App\Models\User;
use App\Models\LoginOtp;
use Illuminate\Support\Facades\Hash;

class LoginOtpService
{
    public function create(User $user, ?string $ipAddress = null, ?string $userAgent = null): array
    {
        // OTPهای قبلی استفاده‌نشده برای این کاربر را غیرفعال می‌کنیم
        LoginOtp::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update([
                'used_at' => now(),
            ]);

        $plainCode = (string) random_int(100000, 999999);

        $otp = LoginOtp::create([
            'user_id' => $user->id,
            'mobile' => $user->mobile,
            'code' => Hash::make($plainCode),
            'expires_at' => now()->addMinutes(3),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        return [
            'otp' => $otp,
            'plain_code' => $plainCode,
        ];
    }

    public function verify(User $user, string $code): bool
    {
        $otp = LoginOtp::where('user_id', $user->id)
            ->whereNull('used_at')
            ->latest()
            ->first();

        if (! $otp) {
            return false;
        }

        if ($otp->expires_at->isPast()) {
            return false;
        }

        if ($otp->attempts >= 5) {
            return false;
        }

        if (! Hash::check($code, $otp->code)) {
            $otp->increment('attempts');
            return false;
        }

        $otp->update([
            'used_at' => now(),
        ]);

        return true;
    }
}
