<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Translation\PotentiallyTranslatedString;

class Image implements ValidationRule
{
    use RuleExtenderTrait;

    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $validator = Validator::make([
            'image' => $value,
        ], [
            'image' => preg_match('/data:([a-zA-Z0-9]+\/[a-zA-Z0-9-.+]+).base64,/', $value)
                ? [
                    'base64image',
                    'base64mimes:jpeg,png',
                    'base64mimetypes:image/jpeg,image/png',
                    'base64max:2048',
                ] : [
                    'nullable',
                    'string',
                    'max:255',
                ],
        ]);

        if ($validator->fails()) {
            $fail($validator->errors()->first());
        }
    }
}
