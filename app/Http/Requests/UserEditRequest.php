<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Company;

class UserEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('user edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    try {
                        $decryptedId = decrypt($value); // Decrypt the company_id
    
                        if (!Company::where('id', $decryptedId)->exists()) {
                            $fail('The selected company is invalid.');
                        }
                    } catch (\Exception $e) {
                        $fail('Invalid company ID.');
                    }
                }
            ],
            'name' => [
                'required'
            ],
            'email' => [
                'required',
                Rule::unique((new User)->getTable())->ignore(decrypt($this->id))
            ],
            'role_ids' => [
                'required'
            ]
        ];
    }
}
