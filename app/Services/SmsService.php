<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    public function sendLoginOtp(string $mobile, string $code): void
    {
        Log::info('Login OTP', [
            'mobile' => $mobile,
            'code' => $code,
        ]);

        // اینجا بعداً سرویس پیامکی واقعی را صدا می‌زنید.
    }
}

