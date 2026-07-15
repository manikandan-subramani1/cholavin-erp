<?php

namespace App\Http\Requests\Drivers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route('id') ? 'update' : 'create';

        return $this->user()?->can('drivers.'.$action) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'mobile' => ['required', 'string', 'max:190'],
            'license_number' => ['required', 'string', 'max:190'],
            'license_expiry' => ['required', 'date'],
            'assigned_vehicle' => ['required', 'string', 'max:190'],
            'status' => ['required', Rule::in([
                0 => 'Active',
                1 => 'Inactive',
                2 => 'On Leave',
            ])],
        ];
    }
}
