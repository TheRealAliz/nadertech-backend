<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsService
{
    public function sendOtp(string $mobile, string $code): array
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'x-api-key' => config('services.sms_ir.api_key'),
        ])->post('https://api.sms.ir/v1/send/verify', [
            'mobile' => $mobile,
            'templateId' => config('services.sms_ir.template_id'),
            'parameters' => [
                [
                    'name' => 'OTP',
                    'value' => $code,
                ],
            ],
        ]);

        if ($response->failed()) {
            throw new \Exception(
                'ارسال پیامک با خطا مواجه شد: ' . $response->body()
            );
        }

        $data = $response->json();

        if (($data['status'] ?? null) !== 1) {
            throw new \Exception(
                $data['message'] ?? 'ارسال پیامک ناموفق بود.'
            );
        }

        return $data;
    }
}
