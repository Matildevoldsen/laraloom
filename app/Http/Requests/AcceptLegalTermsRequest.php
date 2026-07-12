<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AcceptLegalTermsRequest extends FormRequest
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
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'terms_accepted' => ['required', 'accepted'],
            'age_confirmed' => ['required', 'accepted'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'terms_accepted.accepted' => 'You must agree to the Terms of Service to use Laraloom.',
            'age_confirmed.accepted' => 'You must confirm that you are at least 18 years old.',
        ];
    }
}
