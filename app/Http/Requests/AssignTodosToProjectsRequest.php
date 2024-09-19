<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class AssignTodosToProjectsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust authorization logic if needed
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'data' => $validator->errors()
        ], 422));
    }

    public function rules(): array
    {
        return [
            'assignments' => 'required|array',
            'assignments.*.project_id' => 'required|integer|exists:projects,id',
            'assignments.*.todo_id' => 'required|integer|exists:todos,id',
            'assignments.*.order' => 'required|integer',
            'assignments.*.added_by' => 'required|integer|exists:users,id',
            'assignments.*.is_critical_path' => 'required|boolean',
            'assignments.*.created_by' => 'required|integer|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'assignments.required' => 'Assignments data is required.',
            'assignments.array' => 'Assignments must be an array.',
            'assignments.*.project_id.required' => 'Project ID is required.',
            'assignments.*.project_id.integer' => 'Project ID must be an integer.',
            'assignments.*.project_id.exists' => 'Project ID must exist in the projects table.',
            'assignments.*.todo_id.required' => 'Todo ID is required.',
            'assignments.*.todo_id.integer' => 'Todo ID must be an integer.',
            'assignments.*.todo_id.exists' => 'Todo ID must exist in the todos table.',
            'assignments.*.order.required' => 'Order is required.',
            'assignments.*.order.integer' => 'Order must be an integer.',
            'assignments.*.added_by.required' => 'Added by user ID is required.',
            'assignments.*.added_by.integer' => 'Added by user ID must be an integer.',
            'assignments.*.added_by.exists' => 'Added by user ID must exist in the users table.',
            'assignments.*.is_critical_path.required' => 'Is critical path field is required.',
            'assignments.*.is_critical_path.boolean' => 'Is critical path must be a boolean value.',
            'assignments.*.created_by.required' => 'Created by user ID is required.',
            'assignments.*.created_by.integer' => 'Created by user ID must be an integer.',
            'assignments.*.created_by.exists' => 'Created by user ID must exist in the users table.',
        ];
    }
}
