<?php

namespace App\Http\Requests\Access;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserPermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'overrides' => ['nullable', 'array'],
            'overrides.*' => ['required', Rule::in(['inherit', 'allow', 'deny'])],
        ];
    }
}
