<?php

namespace App\Http\Requests\Enquiries;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEnquiryStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('enquiry')) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['contacted', 'not_contacted'])],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
