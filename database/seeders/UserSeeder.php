<?php

namespace Database\Seeders;

use App\Enums\BusinessType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {

        $user = new User();

        $user->name = "admin";
        $user->email = "admin@gmail.com";
        $user->is_admin = 1;
        $user->country_code = "cmr";
        $user->phone_number = "+237680355391";
        $user->password = Hash::make('admin');

        $user->save();


        $user1 = new User();

        $user1->name = "user";
        $user1->email = "user@gmail.com";
        $user1->is_admin = 0;
        $user1->country_code = "cmr";
        $user1->phone_number = "+237680355391";
        $user1->password = Hash::make('user');

        $user1->save();


    }
}
