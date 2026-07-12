<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $administrator = $this->user();

        return $administrator instanceof User
            && $administrator->is_admin === true
            && $administrator->can('access-admin') === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'confirmation' => [
                'required',
                'string',
                Rule::in([$this->confirmationPhrase()]),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'confirmation.in' => 'The confirmation must exactly match the member username.',
        ];
    }

    public function confirmationPhrase(): string
    {
        $member = $this->route('user');

        abort_unless($member instanceof User, 404);

        return $member->username ?? $member->email;
    }
}
