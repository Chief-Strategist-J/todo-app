<?php

namespace App\Http\Requests;

use Exception;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator) : void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'data' => $validator->errors()
        ], 422));
    }

    public function messages(): array
    {
        return [
            'email.required' => 'A email is required',
            'password.required' => 'A password is required',
            'is_sign_up.required' => 'a is_sign_up field is required',
        ];
    }

    public function rules(): array
    {
        return [
            'password' => 'required|string',
            'is_sign_up' => 'required|nullable|boolean',
            'email' => [
                'required',
                'string',
                'email',
            ],
        ];
    }
}
