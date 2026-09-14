<?php

namespace App\Exceptions;

use App\Exceptions\BaseException;

class LotteryException extends BaseException
{
    public static function unauthenticated(): self
    {
        throw new self(
            'User is not authenticated.',
            401,
            'LOTTERY_001',
        );
    }

    public static function lotteryNotActive(): self
    {
        throw new self(
            'This lottery is not currently active.',
            409,
            'LOTTERY_002',
        );
    }

    public static function registrationNotStarted(): self
    {
        throw new self(
            'Registration for this lottery has not started yet.',
            409,
            'LOTTERY_003',
        );
    }

    public static function registrationEnded(): self
    {
        throw new self(
            'The registration period for this lottery has ended.',
            409,
            'LOTTERY_004',
        );
    }

    public static function alreadyRegistered(): self
    {
        throw new self(
            'You have already registered for this lottery.',
            409,
            'LOTTERY_005',
        );
    }

    public static function capacityReached(): self
    {
        throw new self(
            'This lottery has reached its maximum capacity.',
            409,
            'LOTTERY_006',
        );
    }

    public static function registrationFailed(): self
    {
        throw new self(
            'Failed to register for the lottery.',
            500,
            'LOTTERY_007',
        );
    }

    public static function alreadyDrawn(): self
    {
        throw new self(
            'This lottery has already been drawn.',
            409,
            'LOTTERY_008'
        );
    }

    public static function invalidDrawStatus(): self
    {
        throw new self(
            'Lottery cannot be drawn while it is in draft status.',
            409,
            'LOTTERY_009'
        );
    }

    public static function drawNotAvailableYet(): self
    {
        throw new self(
            'The lottery end time has not been reached yet.',
            409,
            'LOTTERY_010'
        );
    }

    public static function winnersRequired(): self
    {
        throw new self(
            'Winner list is required.',
            422,
            'LOTTERY_011'
        );
    }

    public static function tooManyWinners(): self
    {
        throw new self(
            'The number of winners exceeds the allowed winner count.',
            422,
            'LOTTERY_012'
        );
    }
}