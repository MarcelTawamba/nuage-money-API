<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PSPAdminUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates admin users for all Payment Service Providers
     */
    public function run(): void
    {
        // StartButton Admin
        User::updateOrCreate(
            ['email' => 'admin_startbutton@nuage.money'],
            [
                'name' => 'StartButton Admin',
                'is_admin' => true,
                'service_provider' => 'StartButton',
                'password' => Hash::make('admin_startButton'),
                'country_code' => 'cm',
                'phone_number' => '+237680355391',
            ]
        );

        // Fincra Admin
        User::updateOrCreate(
            ['email' => 'admin_fincra@nuage.money'],
            [
                'name' => 'Fincra Admin',
                'is_admin' => true,
                'service_provider' => 'Fincra',
                'password' => Hash::make('admin_fincra'),
                'country_code' => 'ng',
                'phone_number' => '+237680355391',
            ]
        );

        // VALR Admin
        User::updateOrCreate(
            ['email' => 'admin_valr@nuage.money'],
            [
                'name' => 'VALR Admin',
                'is_admin' => true,
                'service_provider' => 'VALR',
                'password' => Hash::make('admin_valr'),
                'country_code' => 'za',
                'phone_number' => '+237680355391',
            ]
        );

        // Bridge Admin
        User::updateOrCreate(
            ['email' => 'admin_bridge@nuage.money'],
            [
                'name' => 'Bridge Admin',
                'is_admin' => true,
                'service_provider' => 'Bridge',
                'password' => Hash::make('admin_bridge'),
                'country_code' => 'us',
                'phone_number' => '+237680355391',
            ]
        );
    }
}
