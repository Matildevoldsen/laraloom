<?php

namespace App\Http\Requests;

use App\Models\Post;
use App\PostKind;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $post instanceof Post && $this->user()?->can('update', $post) === true;
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
            'title' => ['nullable', 'string', 'max:180', 'required_unless:kind,note'],
            'body' => ['nullable', 'string', 'max:1500', 'required_without:url'],
            'url' => ['nullable', 'url:http,https', 'max:2048'],
            'tags' => ['nullable', 'string', 'max:240'],
        ];
    }
}
