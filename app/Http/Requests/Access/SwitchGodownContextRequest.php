<?php

namespace App\Http\Requests\Access;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SwitchGodownContextRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'godown_id' => [
                'nullable',
                'integer',
                Rule::exists('godowns', 'id')->where('is_active', true),
            ],
        ];
    }
}
