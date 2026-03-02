<?php

namespace Database\Seeders;

use App\Models\SystemLedger;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemLedgerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create system ledger for tracking system-side wallet balances
        SystemLedger::updateOrCreate(
            ['name' => 'system'],
            ['description' => 'Main system ledger for tracking incoming funds']
        );

        // Create system fee ledger for tracking collected fees
        SystemLedger::updateOrCreate(
            ['name' => 'system fee'],
            ['description' => 'System fee ledger for tracking transaction fees']
        );
    }
}
