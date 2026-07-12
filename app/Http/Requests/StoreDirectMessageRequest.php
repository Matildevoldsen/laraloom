<?php

namespace App\Http\Requests;

use App\Models\DirectConversation;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreDirectMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $conversation = $this->route('conversation');
        $user = $this->user();

        return $user instanceof User
            && $conversation instanceof DirectConversation
            && Gate::forUser($user)->allows('send', $conversation);
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:4000'],
            'client_id' => ['required', 'uuid'],
        ];
    }

    /** @return array{body: string, client_id: string} */
    public function messageData(): array
    {
        return [
            'body' => $this->string('body')->trim()->toString(),
            'client_id' => $this->string('client_id')->toString(),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['body' => $this->string('body')->trim()->toString()]);
    }
}
