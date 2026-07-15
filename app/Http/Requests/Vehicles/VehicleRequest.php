<?php

namespace App\Http\Requests\Vehicles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route('id') ? 'update' : 'create';

        return $this->user()?->can('vehicles.'.$action) ?? false;
    }

    public function rules(): array
    {
        return [
            'vehicle_number' => ['required', 'string', 'max:190'],
            'vehicle_type' => ['required', Rule::in([
                0 => 'Mini Truck',
                1 => 'Truck',
                2 => 'Van',
                3 => 'Two Wheeler',
            ])],
            'capacity' => ['required', 'string', 'max:190'],
            'owner_name' => ['required', 'string', 'max:190'],
            'insurance_expiry' => ['required', 'date'],
            'status' => ['required', Rule::in([
                0 => 'Active',
                1 => 'Inactive',
                2 => 'Service',
            ])],
        ];
    }
}
