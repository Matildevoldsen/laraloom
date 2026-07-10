<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCommunityProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'alpha_dash:ascii',
                Rule::unique('users', 'username')->ignore($this->user()),
            ],
            'headline' => ['nullable', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:600'],
            'location' => ['nullable', 'string', 'max:100'],
            'website_url' => ['nullable', 'url:http,https', 'max:2048'],
            'github_username' => ['nullable', 'string', 'max:39', 'regex:/^[A-Za-z0-9](?:[A-Za-z0-9-]{0,37}[A-Za-z0-9])?$/'],
            'x_username' => ['nullable', 'string', 'max:15', 'regex:/^[A-Za-z0-9_]+$/'],
            'avatar_url' => ['nullable', 'url:https', 'max:2048'],
            'stack' => ['nullable', 'array', 'max:8'],
            'stack.*' => ['string', Rule::in(['Laravel', 'Livewire', 'Filament', 'Inertia', 'Vue', 'React', 'Alpine.js', 'Laravel AI'])],
            'is_available_for_work' => ['nullable', 'boolean'],
        ];
    }
}
