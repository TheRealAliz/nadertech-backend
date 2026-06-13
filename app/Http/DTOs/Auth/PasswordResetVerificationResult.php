<?php

namespace App\Http\DTOs\Auth;

class PasswordResetVerificationResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $errorKey = null,
        public readonly ?string $errorMessage = null,
        public readonly ?int $remainingAttempts = null,
        public readonly ?string $resetToken = null,
    ) {
    }

    public static function success(string $resetToken): self
    {
        return new self(true, null, null, null, $resetToken);
    }

    public static function notFound(): self
    {
        return new self(false, 'not_found', 'کد بازیابی یافت نشد.');
    }

    public static function expired(): self
    {
        return new self(false, 'expired', 'کد بازیابی منقضی شده است.');
    }

    public static function tooManyAttempts(): self
    {
        return new self(false, 'too_many_attempts', 'تعداد تلاش‌های ناموفق بیش از حد مجاز است.');
    }

    public static function invalidCode(int $remainingAttempts): self
    {
        return new self(
            false,
            'invalid_code',
            "کد وارد شده نامعتبر است. {$remainingAttempts} تلاش دیگر باقی مانده.",
            $remainingAttempts
        );
    }
}