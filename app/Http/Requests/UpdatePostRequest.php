<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
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
            'original'        => ['required', 'array'],
            'original.title'  => ['nullable', 'string', 'max:255'],
            'original.body'   => ['nullable', 'string'],
            'updated'         => ['required', 'array'],
            'updated.title'   => ['nullable', 'string', 'max:255'],
            'updated.body'    => ['nullable', 'string'],
            'client_updated_at' => ['required', 'integer'],
        ];
    }
}
