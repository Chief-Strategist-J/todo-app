<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class CreateBulkPomodorosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'status' => 'nullable|string|in:pending,in_progress,completed',
            'todo_id' => 'required|integer|exists:todos,id',
            'user_id' => 'required|integer|exists:users,id',
            'number_of_pomodoros' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'duration.required' => 'The duration field is required.',
            'duration.integer' => 'The duration must be an integer.',
            'duration.min' => 'The duration must be at least 1 minute.',
            'status.in' => 'The status must be one of the following: pending, in_progress, completed.',
            'todo_id.required' => 'The todo_id field is required.',
            'todo_id.integer' => 'The todo_id must be an integer.',
            'todo_id.exists' => 'The selected todo_id is invalid.',
            'user_id.required' => 'The user_id field is required.',
            'user_id.integer' => 'The user_id must be an integer.',
            'user_id.exists' => 'The selected user_id is invalid.',
            'number_of_pomodoros.integer' => 'The number_of_pomodoros must be an integer.',
            'number_of_pomodoros.min' => 'The number_of_pomodoros must be at least 1.',
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
