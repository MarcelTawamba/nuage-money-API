<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class PayType extends Enum
{
    const PAY_IN = "pay_in";
    const PAY_OUT = "pay_out";

}
