<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class PaymentStatus extends Enum
{
    const FAILED =   "FAILED";
    const CREATED =   "CREATED";
    const SUCCESSFUL = "SUCCESSFUL";
    const PENDING = "PENDING";
    const INITIATED = "INITIATED";
    const REVERSED = "REVERSED";
    const PROCESSED = "PROCESSED";
    const DECLINED = "DECLINED";
    const VERIFIED = "VERIFIED";


    static public function  getStatus(string $value): string
    {
        $value = strtolower($value);
        switch ($value) {
            case 'successful':
            case 'verified':
            case 'completed':
            case 'complete':
                return self::SUCCESSFUL;
            case 'failed':
                return self::FAILED;
            case 'pending':
            case 'processing':
                return self::PENDING;
            case 'initiated':
                return self::INITIATED;
            case 'reversed':
                return self::REVERSED;
            case 'processed':
                return self::PROCESSED;
            case 'declined':
                return self::DECLINED;
            default:
                return self::PENDING;
        }
    }
}
