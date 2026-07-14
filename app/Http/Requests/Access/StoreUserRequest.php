<?php

namespace App\Http\Requests\Access;

use App\Models\Godown;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'mobile' => ['nullable', 'string', 'max:30', 'unique:users,mobile'],
            'role_id' => ['required', Rule::exists('roles', 'id')->where('is_active', true)],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'shops' => ['nullable', 'array'],
            'shops.*' => ['integer', Rule::exists('shops', 'id')->where('is_active', true)],
            'godowns' => ['nullable', 'array'],
            'godowns.*' => ['integer', Rule::exists('godowns', 'id')->where('is_active', true)],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $shopIds = collect($this->input('shops', []))->map(fn ($id) => (int) $id);
            $invalid = Godown::query()
                ->whereIn('id', $this->input('godowns', []))
                ->whereNotIn('shop_id', $shopIds)
                ->exists();

            if ($invalid) {
                $validator->errors()->add('godowns', 'Every selected godown must belong to an assigned shop.');
            }
        }];
    }
}
