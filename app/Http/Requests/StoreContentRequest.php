<?php

namespace App\Http\Requests;

use App\ContentRequestType;
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
            'type' => ['required', Rule::enum(ContentRequestType::class)],
            'content_url' => [
                Rule::requiredIf(fn (): bool => $this->requiresContentReference()),
                'nullable',
                'string',
                'max:2048',
            ],
            'requester_name' => ['required', 'string', 'max:100'],
            'requester_email' => ['required', 'email:strict', 'max:254'],
            'relationship' => ['required', 'string', 'max:120'],
            'details' => ['required', 'string', 'min:20', 'max:3000'],
        ];
    }

    private function requiresContentReference(): bool
    {
        $type = ContentRequestType::tryFrom($this->string('type')->toString());

        return in_array($type, [
            ContentRequestType::Removal,
            ContentRequestType::Correction,
            ContentRequestType::OptOut,
            ContentRequestType::IllegalContent,
            ContentRequestType::IntimateImage,
            ContentRequestType::ModerationAppeal,
        ], strict: true);
    }
}
