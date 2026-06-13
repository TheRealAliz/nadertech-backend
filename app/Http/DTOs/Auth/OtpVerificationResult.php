<?php

namespace App\Http\DTOs\Auth;

class OtpVerificationResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $errorKey,
        public readonly string $errorMessage,
        public readonly ?int $remainingAttempts = null,
    ) {
    }

    public static function success(): self
    {
        return new self(true, '', '');
    }

    public static function notFound(): self
    {
        return new self(false, 'code', 'کد تأییدی یافت نشد.');
    }

    public static function expired(): self
    {
        return new self(false, 'code', 'کد تأیید منقضی شده است.');
    }

    public static function tooManyAttempts(): self
    {
        return new self(false, 'code', 'تعداد تلاش‌های ناموفق بیش از حد مجاز است.');
    }

    public static function invalidCode(): self
    {
        return new self(false, 'code', "کد تأیید نامعتبر است.");
    }

    public function toResponse(): array
    {
        if ($this->success) {
            return ['success' => true];
        }

        return [
            'success' => false,
            'error_key' => $this->errorKey,
            'message' => $this->errorMessage,
            'remaining_attempts' => $this->remainingAttempts,
        ];
    }
}