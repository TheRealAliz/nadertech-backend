<?php

namespace App\Exceptions;

use Exception;

class BaseException extends Exception {
    protected string $errorCode;

    public function __construct(string $message, int $code, ?string $errorCode = null) {
        parent::__construct($message, $code);
        $this->errorCode = $errorCode ? $errorCode : $code;
    }

    public function getErrorCode(): string {
        return $this->errorCode;
    }
}
