<?php

namespace App\Http\Requests\NotificationTemplates;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotificationTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route('id') ? 'update' : 'create';

        return $this->user()?->can('notification-templates.'.$action) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'channel' => ['required', Rule::in([
                0 => 'WhatsApp',
                1 => 'SMS',
                2 => 'Email',
                3 => 'In App',
            ])],
            'trigger_event' => ['required', 'string', 'max:190'],
            'subject' => ['nullable', 'string', 'max:190'],
            'message_body' => ['required', 'string', 'max:2000'],
            'status' => ['required', Rule::in([
                0 => 'Active',
                1 => 'Inactive',
            ])],
        ];
    }
}
