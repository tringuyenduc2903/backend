<?php

namespace App\Http\Requests\Admin;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'reply' => [
                'nullable',
                'array',
            ],
            'reply.*.content' => [
                'required',
                'string',
                'max:255',
            ],
            'reply.*.images' => [
                function ($attribute, $value, $fail) {
                    $items = json_decode($value, true);

                    $validator = Validator::make([
                        'images' => $items,
                    ], [
                        'images' => [
                            'array',
                            'max:5',
                        ],
                        'images.*' => [
                            'required',
                            'string',
                            function ($attribute, $value, $fail) {
                                if (str_contains($value, CRUD::get('dropzone.temporary_folder'))) {
                                    if (! Storage::disk(CRUD::get('dropzone.temporary_disk'))->exists($value)) {
                                        $fail(trans('validation.image'));
                                    }
                                } elseif (! Storage::disk('review')->exists($value)) {
                                    $fail(trans('validation.image'));
                                }
                            },
                        ],
                    ]);

                    if ($validator->fails()) {
                        $fail($validator->errors()->first());
                    }
                },
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $reply = $this->input('reply');

        foreach ($reply as &$item) {
            if (is_null($item['images'])) {
                $item['images'] = json_encode([]);
            }
        }

        $this->merge(['reply' => $reply]);
    }
}
