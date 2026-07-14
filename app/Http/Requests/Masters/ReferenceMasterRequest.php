<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReferenceMasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route('master') ? 'update' : 'create';

        return $this->user()?->can($this->route('module').'.'.$action) ?? false;
    }

    public function rules(): array
    {
        $module = config('erp_modules.reference.'.$this->route('module'));
        abort_unless($module, 404);
        $master = $this->route('master');

        return [
            'name' => ['required', 'string', 'min:2', 'max:190'],
            'code' => ['required', 'string', 'max:80', Rule::unique('reference_masters')->where('type', $module['type'])->ignore($master)],
            'parent_id' => [Rule::requiredIf(isset($module['parent_type'])), 'nullable', Rule::exists('reference_masters', 'id')->where('type', $module['parent_type'] ?? '__none__')],
            'description' => ['nullable', 'string', 'max:2000'],
            'percentage' => [Rule::requiredIf($module['percentage'] ?? false), 'nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
