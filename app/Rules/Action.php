<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Translation\PotentiallyTranslatedString;

class Action implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $items = json_decode($value, true);

        $validator = Validator::make([
            'actions' => $items,
        ], [
            'actions' => [
                'nullable',
                'array',
                'max:30',
            ],
            'actions.*.title' => [
                'required',
                'string',
                'max:50',
            ],
            'actions.*.link' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        if ($validator->fails()) {
            $fail($validator->errors()->first());
        }
    }
}
