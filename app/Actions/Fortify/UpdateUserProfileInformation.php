<?php

namespace App\Actions\Fortify;

use App\Enums\CustomerGender;
use App\Models\Customer;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string>  $input
     */
    public function update(Customer $user, array $input): void
    {
        $timezone = timezone_identifiers_list()[$user->timezone];

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
                Rule::unique(Customer::class)->ignore($user->id),
            ],
            'phone_number' => [
                'nullable',
                'string',
                'phone:VN',
                Rule::unique(Customer::class)->ignore($user->id),
            ],
            'birthday' => [
                'nullable',
                'date',
                'before_or_equal:'.Carbon::now($timezone)->subYears(16),
                'after_or_equal:'.Carbon::now($timezone)->subYears(100),
            ],
            'gender' => [
                'nullable',
                'integer',
                Rule::in(CustomerGender::keys()),
            ],
            'timezone' => [
                'required',
                'integer',
                Rule::in(array_keys(timezone_identifiers_list())),
            ],
        ])->validate();

        $user->fill($validate);

        if ($user->isDirty('email') || $user->isDirty('phone_number')) {
            if ($user->isDirty('email') && $user instanceof MustVerifyEmail) {
                $user->forceFill(['email_verified_at' => null])->save();

                $user->sendEmailVerificationNotification();
            }

            if ($user->isDirty('phone_number')) {
                $user->forceFill(['phone_number_verified_at' => null])->save();
            }
        } else {
            $user->save();
        }
    }
}
