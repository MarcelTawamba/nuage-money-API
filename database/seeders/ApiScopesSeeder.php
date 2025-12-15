<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApiScopesSeeder extends Seeder
{
    public function run(): void
    {
        $scopes = [
            // Currencies
            ['name' => 'currencies:read', 'description' => 'Read currency information', 'resource' => 'currencies', 'action' => 'read'],
            
            // Countries
            ['name' => 'countries:read', 'description' => 'Read available countries', 'resource' => 'countries', 'action' => 'read'],
            
            // Fees
            ['name' => 'fees:read', 'description' => 'Read fee information', 'resource' => 'fees', 'action' => 'read'],
            
            // Payments
            ['name' => 'payments:read', 'description' => 'Read payment and transaction data', 'resource' => 'payments', 'action' => 'read'],
            ['name' => 'payments:write', 'description' => 'Create payments and transactions', 'resource' => 'payments', 'action' => 'write'],
            
            // Wallets
            ['name' => 'wallets:read', 'description' => 'Read wallet balances and information', 'resource' => 'wallets', 'action' => 'read'],
            ['name' => 'wallets:write', 'description' => 'Perform wallet operations', 'resource' => 'wallets', 'action' => 'write'],
            
            // Webhooks
            ['name' => 'webhooks:receive', 'description' => 'Receive webhook notifications', 'resource' => 'webhooks', 'action' => 'receive'],
            
            // Full access (admin)
            ['name' => '*', 'description' => 'Full access to all resources', 'resource' => '*', 'action' => '*'],
        ];

        foreach ($scopes as $scope) {
            DB::table('api_scopes')->updateOrInsert(
                ['name' => $scope['name']],
                array_merge($scope, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
