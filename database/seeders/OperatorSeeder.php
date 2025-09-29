<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Operator;
use App\Models\CountryAvaillable;
use App\Models\WalletType;
use App\Enums\PayType;
use App\Enums\PaymentMethod;
use App\Enums\MethodType;

class OperatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $countryCurrencyMap = [
            'cmr' => 'XAF',
            'nga' => 'NGN',
            'gha' => 'GHS',
            'usa' => 'USD',
        ];

        foreach ($countryCurrencyMap as $countryCode => $currencyCode) {
            $country = CountryAvaillable::where('code', $countryCode)->first();
            $currency = WalletType::where('name', $currencyCode)->first();

            if ($country && $currency) {
                $data = [
                    'country_id' => $country->id,
                    'currency_id' => $currency->id,
                    'type' => PayType::PAY_IN,
                    'method_class' => PaymentMethod::ADMIN_DEPOSIT,
                    'method_type' => MethodType::ADMIN_DEPOSIT,
                    'method_name' => 'Default Operator',
                    'fee_type' => 'percentage',
                    'fees' => 0,
                    'operator_fee_type' => 'percentage',
                    'operator_fees' => 0,
                ];

                Operator::firstOrCreate(
                    [
                        'country_id' => $country->id,
                        'currency_id' => $currency->id,
                        'method_class' => PaymentMethod::ADMIN_DEPOSIT,
                        'type' => PayType::PAY_IN,
                    ],
                    $data
                );
            } else {
                $this->command->warn("Could not create operator for {$countryCode} with currency {$currencyCode}. Make sure the country and currency exist in their respective seeders.");
            }
        }
    }
}