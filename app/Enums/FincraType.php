<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class FincraType extends Enum
{
    const CARD = "card";
    const PAY_ATTITUDE = "pay_attitude";
    const MOBILE_MONEY = "mobile_money";
}
