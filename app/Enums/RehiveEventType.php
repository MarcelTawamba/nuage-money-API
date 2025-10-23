<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class RehiveEventType extends Enum
{
    const INITIATE_NGN_PAYOUT = 'initiate.NGN.payout';
    const INITIATE_GHS_PAYOUT = 'initiate.GHS.payout';
    const INITIATE_ZAR_PAYOUT = 'initiate.ZAR.payout';
    const INITIATE_KES_PAYOUT = 'initiate.KES.payout';
    const INITIATE_UGX_PAYOUT = 'initiate.UGX.payout';
    const INITIATE_RWF_PAYOUT = 'initiate.RWF.payout';
    const INITIATE_XOF_PAYOUT = 'initiate.XOF.payout';
    const INITIATE_XAF_PAYOUT = 'initiate.XAF.payout';

    const TRANSFER_NGN = 'transfer.NGN';
    const TRANSFER_GHS = 'transfer.GHS';
    const TRANSFER_ZAR = 'transfer.ZAR';
    const TRANSFER_KES = 'transfer.KES';
    const TRANSFER_UGX = 'transfer.UGX';
    const TRANSFER_RWF = 'transfer.RWF';
    const TRANSFER_XOF = 'transfer.XOF';
    const TRANSFER_XAF = 'transfer.XAF';
}
