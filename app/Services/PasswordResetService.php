<?php

namespace App\Services;

use App\Http\DTOs\Auth\PasswordResetVerificationResult;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PasswordResetService
{
    public function create(User $user, ?string $ipAddress = null, ?string $userAgent = null): PasswordResetCode
    {
        PasswordResetCode::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->delete();

        $plainCode = $this->makeCode();

        $resetCode = PasswordResetCode::create([
            'user_id' => $user->id,
            'code_hash' => Hash::make($plainCode),
            'expires_at' => now()->addMinutes(5),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        return $resetCode;
    }

    public function verify(User $user, string $code): PasswordResetVerificationResult
    {
        $resetCode = PasswordResetCode::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$resetCode) {
            return PasswordResetVerificationResult::notFound();
        }

        if ($resetCode->expires_at->isPast()) {
            return PasswordResetVerificationResult::expired();
        }

        if ($resetCode->attempts >= 5) {
            return PasswordResetVerificationResult::tooManyAttempts();
        }

        if (!Hash::check($code, $resetCode->code_hash)) {
            $resetCode->increment('attempts');
            $remaining = 5 - $resetCode->attempts;

            Log::warning('Invalid password reset code', [
                'user_id' => $user->id,
                'attempts_left' => $remaining,
            ]);

            return PasswordResetVerificationResult::invalidCode($remaining);
        }

        $resetToken = Str::random(80);

        $resetCode->update([
            'verified_at' => now(),
            'reset_token_hash' => hash('sha256', $resetToken),
        ]);

        return PasswordResetVerificationResult::success($resetToken);
    }

    public function resetPassword(User $user, string $resetToken, string $newPassword): array
    {
        $resetCode = PasswordResetCode::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->whereNotNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (
            !$resetCode ||
            !$resetCode->reset_token_hash ||
            !hash_equals($resetCode->reset_token_hash, hash('sha256', $resetToken))
        ) {
            return [
                'success' => false,
                'message' => 'توکن بازیابی نامعتبر یا منقضی شده است.',
            ];
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        $resetCode->update([
            'used_at' => now(),
        ]);

        PasswordResetCode::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->delete();

        return [
            'success' => true,
            'message' => 'رمز عبور با موفقیت تغییر کرد.',
        ];
    }

    private function makeCode(): string
    {
        return app()->environment('local', 'testing')
            ? '123456'
            : str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}