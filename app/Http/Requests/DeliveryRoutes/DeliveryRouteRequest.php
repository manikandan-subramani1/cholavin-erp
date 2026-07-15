<?php

namespace App\Http\Requests\DeliveryRoutes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryRouteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route('id') ? 'update' : 'create';

        return $this->user()?->can('delivery-routes.'.$action) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'code' => ['required', 'string', 'max:190'],
            'start_point' => ['required', 'string', 'max:190'],
            'end_point' => ['required', 'string', 'max:190'],
            'estimated_km' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in([
                0 => 'Active',
                1 => 'Inactive',
            ])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
