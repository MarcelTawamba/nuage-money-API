<?php

namespace App\Console\Commands;

use App\Jobs\GetStartButtonBanksToBDJob;
use Illuminate\Console\Command;

class SyncStartButtonBanks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'banks:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch jobs to sync bank and mobile money lists from StartButton Africa for all supported currencies.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting bank list synchronization...');

        // Currencies that support both bank and mobile money types without a specific country code
        $standardCurrencies = ['NGN', 'GHS', 'ZAR', 'KES', 'UGX', 'RWF', 'TZS', 'ZMW'];
        $types = ['bank', 'mobile_money'];

        foreach ($standardCurrencies as $currency) {
            foreach ($types as $type) {
                GetStartButtonBanksToBDJob::dispatch($currency, $type);
                $this->line(" - Dispatched job for {$currency} ({$type})");
            }
        }

        // XOF currency requires a country code
        $xofCountries = ['BJ', 'CI', 'TG', 'SN', 'ML', 'BF', 'CM'];
        foreach ($xofCountries as $countryCode) {
            // Assuming XOF is primarily for mobile money, but dispatching for both just in case.
            foreach ($types as $type) {
                GetStartButtonBanksToBDJob::dispatch('XOF', $type, $countryCode);
                $this->line(" - Dispatched job for XOF ({$type}) in country {$countryCode}");
            }
        }

        $this->info('All bank list synchronization jobs have been dispatched.');
        return 0;
    }
}