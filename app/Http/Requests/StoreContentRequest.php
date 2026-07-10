<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['remove', 'correct', 'opt_out', 'rights'])],
            'content_url' => ['required', 'url:http,https', 'max:2048'],
            'requester_name' => ['required', 'string', 'max:100'],
            'requester_email' => ['required', 'email:strict', 'max:254'],
            'relationship' => ['required', 'string', 'max:120'],
            'details' => ['required', 'string', 'min:20', 'max:3000'],
        ];
    }
}
