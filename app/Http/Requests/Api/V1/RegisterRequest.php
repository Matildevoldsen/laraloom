<?php

namespace App\Http\Requests\Api\V1;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...$this->profileRules(),
            'username' => ['required', 'string', 'min:3', 'max:30', 'alpha_dash:ascii', Rule::unique(User::class)],
            'password' => $this->passwordRules(),
            'password_confirmation' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:120'],
        ];
    }
}
