<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class ThermalReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('sales.view') ?? false;
    }

    public function rules(): array
    {
        return [
            'paper_width' => ['nullable', 'integer', 'in:58,80'],
        ];
    }

    public function paperWidth(): int
    {
        return (int) ($this->validated('paper_width') ?? 80);
    }
}
