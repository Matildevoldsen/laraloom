<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class CompleteOnboardingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();

        return [
            'username' => [
                Rule::excludeIf($user instanceof User && $user->onboarding_completed_at !== null),
                Rule::requiredIf($user instanceof User && $user->onboarding_completed_at === null),
                'nullable',
                'string',
                'min:3',
                'max:30',
                'alpha_dash:ascii',
                Rule::unique(User::class, 'username')->ignore($user),
            ],
            'terms_accepted' => ['required', 'accepted'],
            'age_confirmed' => ['required', 'accepted'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'username.required' => 'Choose a username before continuing.',
            'username.unique' => 'That username is already in use.',
            'terms_accepted.accepted' => 'You must agree to the Terms of Service to continue.',
            'age_confirmed.accepted' => 'You must confirm that you meet the minimum age requirement.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->exists('username')) {
            $this->merge([
                'username' => Str::of($this->input('username'))->trim()->lower()->toString(),
            ]);
        }
    }
}
