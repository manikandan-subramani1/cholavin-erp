<?php

namespace App\Http\Requests\Access;

use App\Models\Godown;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.update') ?? false;
    }

    public function rules(): array
    {
        $target = $this->route('user');

        return [
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($target)],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($target)],
            'mobile' => ['nullable', 'string', 'max:30', Rule::unique('users', 'mobile')->ignore($target)],
            'role_id' => ['required', Rule::exists('roles', 'id')->where('is_active', true)],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'shops' => ['nullable', 'array'],
            'shops.*' => ['integer', Rule::exists('shops', 'id')->where('is_active', true)],
            'godowns' => ['nullable', 'array'],
            'godowns.*' => ['integer', Rule::exists('godowns', 'id')->where('is_active', true)],
            'financial_years' => ['nullable', 'array'],
            'financial_years.*' => ['integer', Rule::exists('reference_masters', 'id')->where('type', 'financial_year')->where('is_active', true)],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $shopIds = collect($this->input('shops', []))->map(fn ($id) => (int) $id);
            $invalid = Godown::query()
                ->with('shops:id')
                ->whereIn('id', $this->input('godowns', []))
                ->get(['id', 'shop_id'])
                ->contains(fn (Godown $godown) => ! $shopIds->contains((int) $godown->shop_id)
                    && $godown->shops->pluck('id')->intersect($shopIds)->isEmpty());

            if ($invalid) {
                $validator->errors()->add('godowns', 'Every selected godown must belong to an assigned shop.');
            }
        }];
    }
}
