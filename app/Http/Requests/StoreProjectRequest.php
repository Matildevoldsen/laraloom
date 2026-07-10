<?php

namespace App\Http\Requests;

use App\ProjectKind;
use App\Rules\LaravelCloudUrl;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
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
            'kind' => ['required', Rule::enum(ProjectKind::class)],
            'name' => ['required', 'string', 'max:100'],
            'tagline' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:3000'],
            'url' => ['required', 'url:http,https', 'max:2048'],
            'repository_url' => ['nullable', 'url:http,https', 'max:2048'],
            'laravel_cloud_url' => ['nullable', 'url:https', 'max:2048', new LaravelCloudUrl],
            'logo_url' => ['nullable', 'url:http,https', 'max:2048'],
            'screenshot_url' => ['nullable', 'url:http,https', 'max:2048'],
            'tags' => ['nullable', 'string', 'max:240'],
            'is_open_source' => ['nullable', 'boolean'],
        ];
    }
}
