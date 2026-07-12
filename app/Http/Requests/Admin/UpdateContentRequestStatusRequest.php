<?php

namespace App\Http\Requests\Admin;

use App\ContentRequestStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentRequestStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ContentRequestStatus::class)],
            'resolution_notes' => [
                Rule::requiredIf(in_array($this->input('status'), [
                    ContentRequestStatus::Resolved->value,
                    ContentRequestStatus::Rejected->value,
                ], true)),
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}
