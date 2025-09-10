<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;


final class PaymentMethod extends Enum
{
    const TOUPESU_MOBILE = "toupesu_mobile";
    const FINCRA = "fincra";
    const START_BUTTON_BANK = "start_button_bank";
    const START_BUTTON_MOBILE = "start_button_mobile";
    const ADMIN_DEPOSIT = "admin_deposit";
}
