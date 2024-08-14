<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;


class BulkCreateTagsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tags' => 'required|array',
            'tags.*.todo_id' => 'required|integer|exists:todos,id', // Ensure todo_id is present and valid
            'tags.*.created_by' => 'required|integer|exists:users,id', // Ensure created_by is present and valid
            'tags.*.name' => 'required|string|max:255', // Ensure name is present, string, and max length
            'tags.*.color' => 'required|string|max:50', // Ensure color is present, string, and max length
        ];
    }

    public function messages(): array
    {
        return [
            'tags.required' => 'The tags field is required.',
            'tags.array' => 'The tags field must be an array.',
            'tags.*.todo_id.required' => 'The todo_id field is required for each tag.',
            'tags.*.todo_id.integer' => 'The todo_id field must be an integer.',
            'tags.*.todo_id.exists' => 'The selected todo_id is invalid.',
            'tags.*.created_by.required' => 'The created_by field is required for each tag.',
            'tags.*.created_by.integer' => 'The created_by field must be an integer.',
            'tags.*.created_by.exists' => 'The selected created_by is invalid.',
            'tags.*.name.required' => 'The name field is required for each tag.',
            'tags.*.name.string' => 'The name field must be a string.',
            'tags.*.name.max' => 'The name field may not be greater than 255 characters.',
            'tags.*.color.required' => 'The color field is required for each tag.',
            'tags.*.color.string' => 'The color field must be a string.',
            'tags.*.color.max' => 'The color field may not be greater than 50 characters.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        // Log the validation errors
        Log::error('Validation failed', [
            'errors' => $validator->errors(),
            'input' => $this->all(), // Log the input data for better context
        ]);

        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors' => $validator->errors(),
            'code' => 422,
            'message' => 'Validation failed. Please check your input.',
        ], 422));
    }

}