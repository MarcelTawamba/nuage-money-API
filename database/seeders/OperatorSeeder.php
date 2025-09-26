<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Operator;
use App\Models\CountryAvaillable;
use App\Models\WalletType;

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
            // Add other countries and their default currencies here. For example:
        ];

        foreach ($countryCurrencyMap as $countryCode => $currencyCode) {
            $country = CountryAvaillable::where('code', $countryCode)->first();
            $currency = WalletType::where('name', $currencyCode)->first();

            if ($country && $currency) {
                Operator::firstOrCreate(
                    ['country_id' => $country->id],
                    ['currency_id' => $currency->id, 'name' => 'Default Operator']
                );
            } else {
                $this->command->warn("Could not create operator for {$countryCode} with currency {$currencyCode}. Make sure the country and currency exist in their respective seeders.");
            }
        }
    }
}
