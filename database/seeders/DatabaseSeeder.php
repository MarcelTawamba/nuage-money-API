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

        // This seeder is from the lwwcas/laravel-countries package
        $this->call(\Lwwcas\LaravelCountries\Database\Seeders\LwwcasDatabaseSeeder::class);
    }
}
