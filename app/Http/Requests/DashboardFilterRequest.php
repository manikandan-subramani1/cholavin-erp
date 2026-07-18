<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DashboardFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('dashboard.view') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['preset' => $this->input('preset', 'this_month')]);
    }

    public function rules(): array
    {
        return [
            'preset' => ['required', Rule::in([
                'today', 'yesterday', 'this_week', 'this_month', 'last_month',
                'this_quarter', 'financial_year', 'custom',
            ])],
            'date_from' => ['nullable', 'required_if:preset,custom', 'date'],
            'date_to' => ['nullable', 'required_if:preset,custom', 'date', 'after_or_equal:date_from'],
        ];
    }
}
