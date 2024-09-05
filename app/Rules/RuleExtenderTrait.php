<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Arr;
use Illuminate\Validation\InvokableValidationRule;

trait RuleExtenderTrait
{
    public static function extends(string $attribute, mixed $value, Validator $validator): bool
    {
        $rule = InvokableValidationRule::make(app(get_class()))->setValidator($validator);

        $result = $rule->passes($attribute, $value);

        if (! $result) {
            $validator->setCustomMessages([
                $attribute => Arr::first($rule->message()),
            ]);
        }

        return $result;
    }
}
