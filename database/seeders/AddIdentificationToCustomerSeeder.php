<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Identification;
use Illuminate\Database\Seeder;

class AddIdentificationToCustomerSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Customer::each(function (Customer $customer) {
            $customer->identifications()->saveMany(
                Identification::factory(5)->make()
            );
        });
    }
}
