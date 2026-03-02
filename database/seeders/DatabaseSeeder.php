<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(WalletTypeSeeder::class);
        $this->call(CountryAvaillableSeeder::class);
        $this->call(OperatorSeeder::class);
        $this->call(SystemLedgerSeeder::class); // Seeds system ledgers required for wallet transactions
        $this->call(PSPAdminUsersSeeder::class); // Seeds all PSP admin users
        $this->call(ApiScopesSeeder::class); // Seeds API scopes for API key authentication
        $this->call(RateLimitTierSeeder::class); // Seeds rate limit tiers for API keys
        $this->call(CryptoAssetsSeeder::class); // Seeds crypto assets from BlockRadar API

        // This seeder is from the lwwcas/laravel-countries package
        $this->call(\Lwwcas\LaravelCountries\Database\Seeders\LwwcasDatabaseSeeder::class);
    }
}
