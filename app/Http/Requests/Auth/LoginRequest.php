<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'login' => ['required', 'string', 'max:190'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];

        if (config('erp_auth.captcha.enabled')) {
            $rules['captcha'] = ['required', 'integer', 'min:0', 'max:99'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        if (! config('erp_auth.captcha.enabled')) {
            return;
        }

        $validator->after(function (Validator $validator): void {
            if ((string) $this->input('captcha') !== (string) $this->session()->get('auth_captcha_answer')) {
                $validator->errors()->add('captcha', 'The security answer is incorrect.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'login.required' => 'Enter your username, email address, or mobile number.',
            'password.required' => 'Enter your password.',
            'captcha.required' => 'Answer the security question.',
        ];
    }
}
