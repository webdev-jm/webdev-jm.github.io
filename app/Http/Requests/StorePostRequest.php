<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'uuid'              => ['required', 'uuid', 'unique:posts,uuid'],
            'title'             => ['required', 'string', 'max:255'],
            'body'              => ['nullable', 'string'],
            'client_created_at' => ['required', 'integer'],
        ];
    }
}
