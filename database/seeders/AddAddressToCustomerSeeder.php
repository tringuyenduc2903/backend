<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class AddAddressToCustomerSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Customer::each(function (Customer $customer) {
            $customer->addresses()->saveMany(
                Address::factory(5)->make()
            );
        });
    }
}
