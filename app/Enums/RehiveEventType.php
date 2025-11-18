<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class RehiveEventType extends Enum
{
    const WITHDRAW_MANUAL = 'withdraw_manual';
    const CONVERSION = 'conversion';
}
