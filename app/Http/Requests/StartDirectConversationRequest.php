<?php

namespace App\Http\Requests;

use App\Models\DirectConversation;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StartDirectConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $recipient = $this->route('recipient');
        $sender = $this->user();

        return $sender instanceof User
            && $recipient instanceof User
            && Gate::forUser($sender)->allows('create', [DirectConversation::class, $recipient]);
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [];
    }
}
