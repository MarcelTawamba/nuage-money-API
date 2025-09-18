<?php

namespace App\Services;

use App\Classes\FincraPaymentHelper;
use Illuminate\Support\Facades\Log;

class RehiveOfframpService
{
    protected $fincraPaymentHelper;

    public function __construct(FincraPaymentHelper $fincraPaymentHelper)
    {
        $this->fincraPaymentHelper = $fincraPaymentHelper;
    }

    public function processTransaction(array $data)
    {
        Log::info('Processing Rehive transaction:', $data);

        // For now, we'll just call the Fincra payment helper.
        // In the future, we can add logic to select the correct payment provider.
        return $this->fincraPaymentHelper->initiatePayout($data);
    }
}
