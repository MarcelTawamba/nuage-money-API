<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StartButtonAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
    }
}
