<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SyncBatchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.client_id' => ['required', 'string', 'uuid'],
            'items.*.method' => ['required', 'in:POST,PUT,PATCH,DELETE'],
            'items.*.url' => ['required', 'string', 'max:500'],
            'items.*.payload' => ['required', 'array'],
            'items.*.client_timestamp' => ['required', 'integer'],
        ];
    }
}
