<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;


final class PaymentMethod extends Enum
{
    const TOUPESU_MOBILE = "toupesu_mobile";
    const FINCRA = "fincra";
    const START_BUTTON = "start_button";
    const START_BUTTON_BANK = "start_button_bank";
    const START_BUTTON_MOBILE = "start_button_mobile";
    const START_BUTTON_CARD = "start_button_card";
    const START_BUTTON_BANK_TRANSFER = "start_button_bank_transfer";
    const START_BUTTON_USSD = "start_button_ussd";
    const START_BUTTON_PAYATTITUDE = "start_button_payattitude";
    const START_BUTTON_EFT = "start_button_eft";
    const START_BUTTON_QR = "start_button_qr";
    const ADMIN_DEPOSIT = "admin_deposit";
}
