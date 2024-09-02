<?php

namespace App\Actions\Fortify;

use App\Models\Customer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): Customer
    {
        $validate = Validator::make($input, [
            'name' => [
                'required',
                'string',
                'max:50',
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:100',
                Rule::unique(Customer::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        $customer = Customer::make($validate);

        $customer->fill([
            'timezone' => array_search(
                config('app.timezone'),
                timezone_identifiers_list()
            ),
        ]);

        $customer->forceFill([
            'password' => $validate['password'],
        ]);

        $customer->save();

        return $customer;
    }
}
