<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class StartPomodoroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pomodoro_id' => 'required|integer|exists:pomodoros,id',
        ];
    }

    public function messages(): array
    {
        return [
            'pomodoro_id.required' => 'The pomodoro_id field is required.',
            'pomodoro_id.integer' => 'The pomodoro_id field must be an integer.',
            'pomodoro_id.exists' => 'The selected pomodoro_id is invalid.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        // Log the validation errors
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
