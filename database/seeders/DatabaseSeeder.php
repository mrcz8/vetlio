<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $eur = Currency::firstOrCreate(['code' => 'EUR'], [
            'name' => 'Euro',
            'symbol' => '€',
            'minor_unit' => 2,
        ]);

        $en = Language::firstOrCreate(['iso_639_1' => 'en'], [
            'iso_639_2' => 'eng',
            'name_en' => 'English',
            'name_native' => 'English',
        ]);

        Country::firstOrCreate(['iso2' => 'HR'], [
            'iso3' => 'HRV',
            'name_en' => 'Croatia',
            'name_native' => 'Hrvatska',
            'currency_id' => $eur->id,
            'default_language_id' => $en->id,
            'phone_code' => '385',
        ]);

        Admin::create([
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'name' => 'admin',
            'password' => bcrypt('admin'),
            'email' => 'admin@admin.com',
        ]);

        $this->call(SpeciesAndBreeds::class);
        $this->call(DemoOrganisationSeeder::class);
    }
}
