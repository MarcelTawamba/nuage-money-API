<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CountryAvaillable;

class CountryAvaillableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        CountryAvaillable::firstOrCreate(['code' => 'cmr', 'name' => 'Cameroon']);
        CountryAvaillable::firstOrCreate(['code' => 'usa', 'name' => 'United States of America']);
        CountryAvaillable::firstOrCreate(['code' => 'fra', 'name' => 'France']);
        CountryAvaillable::firstOrCreate(['code' => 'ger', 'name' => 'Germany']);
        CountryAvaillable::firstOrCreate(['code' => 'esp', 'name' => 'Spain']);
        CountryAvaillable::firstOrCreate(['code' => 'ita', 'name' => 'Italy']);
        CountryAvaillable::firstOrCreate(['code' => 'can', 'name' => 'Canada']);
        CountryAvaillable::firstOrCreate(['code' => 'mex', 'name' => 'Mexico']);
        CountryAvaillable::firstOrCreate(['code' => 'arg', 'name' => 'Argentina']);
        CountryAvaillable::firstOrCreate(['code' => 'bra', 'name' => 'Brazil']);
        CountryAvaillable::firstOrCreate(['code' => 'rus', 'name' => 'Russia']);
        CountryAvaillable::firstOrCreate(['code' => 'ind', 'name' => 'India']);
        CountryAvaillable::firstOrCreate(['code' => 'jpn', 'name' => 'Japan']);
        CountryAvaillable::firstOrCreate(['code' => 'chn', 'name' => 'China']);
        CountryAvaillable::firstOrCreate(['code' => 'kor', 'name' => 'South Korea']);
        CountryAvaillable::firstOrCreate(['code' => 'gbr', 'name' => 'United Kingdom']);
        CountryAvaillable::firstOrCreate(['code' => 'aut', 'name' => 'Austria']);
        CountryAvaillable::firstOrCreate(['code' => 'bel', 'name' => 'Belgium']);
        CountryAvaillable::firstOrCreate(['code' => 'bul', 'name' => 'Bulgaria']);
        CountryAvaillable::firstOrCreate(['code' => 'cze', 'name' => 'Czech Republic']);
        CountryAvaillable::firstOrCreate(['code' => 'dnk', 'name' => 'Denmark']);
        CountryAvaillable::firstOrCreate(['code' => 'est', 'name' => 'Estonia']);
        CountryAvaillable::firstOrCreate(['code' => 'fin', 'name' => 'Finland']);
        CountryAvaillable::firstOrCreate(['code' => 'gha', 'name' => 'Ghana']);
        CountryAvaillable::firstOrCreate(['code' => 'grc', 'name' => 'Greece']);
        CountryAvaillable::firstOrCreate(['code' => 'hrv', 'name' => 'Croatia']);
        CountryAvaillable::firstOrCreate(['code' => 'hun', 'name' => 'Hungary']);
        CountryAvaillable::firstOrCreate(['code' => 'irl', 'name' => 'Ireland']);
        CountryAvaillable::firstOrCreate(['code' => 'isl', 'name' => 'Iceland']);
        CountryAvaillable::firstOrCreate(['code' => 'nga', 'name' => 'Nigeria']);
        CountryAvaillable::firstOrCreate(['code' => 'isr', 'name' => 'Israel']);
        CountryAvaillable::firstOrCreate(['code' => 'por', 'name' => 'Portugal']);
        CountryAvaillable::firstOrCreate(['code' => 'rom', 'name' => 'Romania']);
        CountryAvaillable::firstOrCreate(['code' => 'swe', 'name' => 'Sweden']);
        CountryAvaillable::firstOrCreate(['code' => 'tur', 'name' => 'Turkey']);
        CountryAvaillable::firstOrCreate(['code' => 'ukr', 'name' => 'Ukraine']);
        CountryAvaillable::firstOrCreate(['code' => 'ven', 'name' => 'Venezuela']);
        CountryAvaillable::firstOrCreate(['code' => 'zaf', 'name' => 'South Africa']);
        // Add other available countries here
    }
}
