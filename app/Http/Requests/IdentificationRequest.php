<?php

namespace App\Http\Requests;

use App\Enums\CustomerIdentification;
use App\Models\Identification;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class IdentificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return fortify_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $id = request()->route('identification');
        $time_zone = fortify_user()->timezone_preview;

        return [
            'default' => [
                'required',
                'boolean',
                Rule::when(
                    $id
                        ? fortify_user()->identifications()->findOrFail($id)->default
                        : ! fortify_user()->identifications()->whereDefault(true)->exists(),
                    'accepted'
                ),
                function ($attribute, $value, $fail) use ($id) {
                    if ($id) {
                        return;
                    }

                    if (fortify_user()->identifications()->count() > 4) {
                        $fail(trans('validation.custom.max.entity', [
                            'attribute' => trans('Identification'),
                        ]));
                    }
                },
            ],
            'type' => [
                'required',
                'integer',
                Rule::in(CustomerIdentification::keys()),
            ],
            'number' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $strlen = strlen($value);

                    switch ((int) request('type')) {
                        case CustomerIdentification::IDENTITY_CARD:
                            if (! in_array($strlen, [9, 12])) {
                                $fail(trans('validation.custom.size.strings', [
                                    'size1' => 9,
                                    'size2' => 12,
                                ]));
                            }
                            break;
                        case CustomerIdentification::CITIZEN_IDENTIFICATION_CARD:
                            if ($strlen !== 12) {
                                $fail(trans('validation.size.string', [
                                    'size' => 12,
                                ]));
                            }
                            break;
                    }
                },
                'max:100',
                Rule::unique(Identification::class)->ignore($id),
            ],
            'issued_name' => [
                'required',
                'string',
                'max:255',
            ],
            'issuance_date' => [
                'required',
                'date',
            ],
            'expiry_date' => [
                'required',
                'date',
                'after:issuance_date',
                'after:'.Carbon::now($time_zone),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'default.accepted' => trans('validation.custom.default.accepted', [
                'attribute' => trans('Identification'),
            ]),
        ];
    }
}
