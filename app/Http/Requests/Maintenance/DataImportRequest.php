<?php

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DataImportRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('maintenance.import') ?? false; }
    public function rules(): array { return ['dataset' => ['required', Rule::in(['reference-masters', 'products', 'parties'])], 'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120']]; }
}
