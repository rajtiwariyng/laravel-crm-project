<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomFieldRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Add authorization logic here if needed (e.g., check if user is admin)
        return true; // Assuming authorized for now
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:custom_fields,name|regex:/^[a-z0-9_]+$/|max:255', // Machine-readable, unique
            'label' => 'required|string|max:255', // Human-readable
            'type' => 'required|string|in:text,date,number,textarea,email,phone', // Allowed types
            'is_filterable' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'The name must only contain lowercase letters, numbers, and underscores.',
            'name.unique' => 'This technical name is already in use.',
            'type.in' => 'Please select a valid field type.',
        ];
    }
}
