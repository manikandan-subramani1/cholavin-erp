<?php

namespace App\Http\Requests\Access;

use Illuminate\Foundation\Http\FormRequest;

class SwitchFinancialYearContextRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('financial-years.switch') ?? false;
    }

    public function rules(): array
    {
        return [
            'financial_year_id' => ['required', 'integer', 'exists:reference_masters,id'],
        ];
    }
}
