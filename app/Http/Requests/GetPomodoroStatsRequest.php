<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class GetPomodoroStatsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'The user_id field is required.',
            'user_id.integer' => 'The user_id must be an integer.',
            'user_id.exists' => 'The selected user_id is invalid.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        Log::error('Validation failed', [
            'errors' => $validator->errors(),
            'input' => $this->all(),
        ]);

        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors' => $validator->errors(),
            'code' => 422,
            'message' => 'Validation failed. Please check your input.',
        ], 422));
    }
}
