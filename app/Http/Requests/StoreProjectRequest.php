<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
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
            'name.required' => 'A project name is required',
            'name.string' => 'Project name must be a string',
            'name.max' => 'Project name must not exceed 255 characters',
            'description.string' => 'Description must be a string',
            'status.string' => 'Status must be a string',
            'end_date.date' => 'End date must be a valid date',
            'is_public.boolean' => 'Is public must be a boolean value',
            'created_by.required' => 'Creator is required',
            'created_by.exists' => 'Creator must be a valid user',
            'updated_by.exists' => 'Updater must be a valid user',
        ];
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'end_date' => 'nullable|date',
            'is_public' => 'nullable|boolean',
            'created_by' => 'required|exists:users,id',
            'updated_by' => 'nullable|exists:users,id',
            'project_category_name' => 'nullable|string',
            'project_phase_name' => 'nullable|string',
            'project_status_name' => 'nullable|string',
            'project_priority_name' => 'nullable|string',
            'project_type_name' => 'nullable|string',
        ];
    }
}