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


    static public function  getStatus(string $value): string
    {
        $value = strtolower($value);
        if($value == "verified" || $value == "successful" ){
            return PaymentStatus::SUCCESSFUL;
        }elseif($value == "failed" ){
            return PaymentStatus::FAILED;
        }else{
            return PaymentStatus::PENDING;
        }
    }
}
