<?php

namespace App\Http\Requests;

use App\PostKind;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StorePostRequest extends FormRequest
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
            'kind' => ['required', Rule::enum(PostKind::class)],
            'title' => ['nullable', 'string', 'max:180'],
            'body' => ['nullable', 'string', 'max:1500', 'required_without_all:url,attachments'],
            'url' => ['nullable', 'url:http,https', 'max:2048'],
            'tags' => ['nullable', 'string', 'max:240'],
            'attachments' => ['nullable', 'array', 'max:4'],
            'attachments.*' => [
                'file',
                File::types(['jpg', 'jpeg', 'png', 'webp', 'gif', 'heic', 'heif', 'mp4', 'mov', 'webm'])
                    ->max('100mb'),
            ],
        ];
    }
}
