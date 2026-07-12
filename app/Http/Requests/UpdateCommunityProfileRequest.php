<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;

class UpdateCommunityProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('user')) === true;
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
            'avatar' => ['nullable', File::image()->max('5mb')],
            'stack' => ['nullable', 'array', 'max:8'],
            'stack.*' => ['string', Rule::in(['Laravel', 'Livewire', 'Filament', 'Inertia', 'Vue', 'React', 'Alpine.js', 'Laravel AI'])],
            'is_available_for_work' => ['nullable', 'boolean'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $user = $this->user();

            if ($user === null || $user->username_changed_at === null) {
                return;
            }

            $username = strtolower($this->string('username')->toString());

            if ($username === strtolower((string) $user->username)) {
                return;
            }

            $availableAt = $user->username_changed_at->copy()->addMonthNoOverflow();

            if ($availableAt->isFuture()) {
                $validator->errors()->add(
                    'username',
                    'You can change your username again on '.$availableAt->format('j F Y').'.',
                );
            }
        }];
    }
}
