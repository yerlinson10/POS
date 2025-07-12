<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::create([
            'first_name' => 'Walk-in',
            'last_name' => 'Customer',
            'email' => 'walk-in-customer@'.env('APP_DOMAIN'),
            'phone' => 'N/A',
            'address' => 'N/A',
        ]);
        Customer::factory(15)->create();
    }
}
