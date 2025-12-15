<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RateLimitTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiers = [
            [
                'name' => 'free',
                'display_name' => 'Free Tier',
                'description' => 'Perfect for testing and small projects',
                'requests_per_minute' => 10,
                'requests_per_day' => 1000,
                'requests_per_month' => 10000,
                'monthly_price' => 0.00,
                'currency' => 'USD',
                'features' => json_encode([
                    'Basic API access',
                    'Email support',
                    '24-hour response time',
                    'Community forum access'
                ]),
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'basic',
                'display_name' => 'Basic Plan',
                'description' => 'For growing businesses and applications',
                'requests_per_minute' => 60,
                'requests_per_day' => 10000,
                'requests_per_month' => 250000,
                'monthly_price' => 49.99,
                'currency' => 'USD',
                'features' => json_encode([
                    'Full API access',
                    'Priority email support',
                    '12-hour response time',
                    'Basic analytics dashboard',
                    'Webhook notifications'
                ]),
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'premium',
                'display_name' => 'Premium Plan',
                'description' => 'For high-volume applications and businesses',
                'requests_per_minute' => 300,
                'requests_per_day' => 50000,
                'requests_per_month' => 1000000,
                'monthly_price' => 199.99,
                'currency' => 'USD',
                'features' => json_encode([
                    'Full API access',
                    'Priority support (email & phone)',
                    '4-hour response time',
                    'Advanced analytics',
                    'Custom webhooks',
                    'Rate limit flexibility',
                    'Dedicated account manager'
                ]),
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'enterprise',
                'display_name' => 'Enterprise',
                'description' => 'Custom solutions for large organizations',
                'requests_per_minute' => null, // Unlimited
                'requests_per_day' => null,
                'requests_per_month' => null,
                'monthly_price' => 999.99,
                'currency' => 'USD',
                'features' => json_encode([
                    'Unlimited API access',
                    '24/7 phone & email support',
                    '1-hour response time',
                    'Custom analytics dashboard',
                    'Custom integrations',
                    'SLA guarantee (99.9% uptime)',
                    'Dedicated infrastructure',
                    'White-label options',
                    'Custom contract terms'
                ]),
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('rate_limit_tiers')->insert($tiers);
    }
}
