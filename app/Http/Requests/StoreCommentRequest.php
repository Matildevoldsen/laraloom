<?php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $this->user() !== null
            && $post instanceof Post
            && $post->published_at?->isPast() === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $post = $this->route('post');
        $postId = $post instanceof Post ? $post->getKey() : 0;

        return [
            'body' => ['required', 'string', 'max:1000'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('comments', 'id')->where('post_id', $postId),
            ],
        ];
    }

    /** @return array{body: string, parent_id?: int} */
    public function commentData(): array
    {
        $body = $this->string('body')->toString();

        if (! $this->filled('parent_id')) {
            return ['body' => $body];
        }

        return ['body' => $body, 'parent_id' => $this->integer('parent_id')];
    }
}
