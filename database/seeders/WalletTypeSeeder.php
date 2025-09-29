<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WalletType;

class WalletTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        WalletType::firstOrCreate(['name' => 'XAF']);
        WalletType::firstOrCreate(['name' => 'USD']);
        WalletType::firstOrCreate(['name' => 'EUR']);
    }
}
