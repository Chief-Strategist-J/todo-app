<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTodoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'data' => $validator->errors()->first()
        ], 422));
    }

    public function messages(): array
    {
        return [
            'todo_id.required' => 'todo id is required',
        ];
    }

    public function rules(): array
    {
        return [
            'todo_id' => 'required',
        ];
    }
}
