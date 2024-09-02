<?php

namespace App\Actions\Fortify;

use App\Models\Customer;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     */
    public function reset(Customer $user, array $input): void
    {
        $validate = Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $user
            ->forceFill($validate)
            ->save();
    }
}
