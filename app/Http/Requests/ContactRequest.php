<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'phone' => ['nullable', 'required_without:email', 'string', 'regex:/^[+0-9\s\-()]{7,20}$/'],
            'email' => ['nullable', 'required_without:phone', 'email', 'max:120'],
            'subject' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'min:5', 'max:2000'],
            'website' => ['prohibited'],
        ];
    }
}
