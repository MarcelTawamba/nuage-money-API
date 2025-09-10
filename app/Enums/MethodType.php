<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class MethodType extends Enum
{
    const MOBILE= "mobile";
    const Card = "card";
    const PAYPAL = "paypal";
    const BANK_ACCOUNT = "bank_account";
    const ADMIN_DEPOSIT = "admin_deposit";
}
