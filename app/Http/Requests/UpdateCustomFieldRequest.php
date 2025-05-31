<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomFieldRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Add authorization logic here if needed
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $customFieldId = $this->route("custom_field")->id; // Get the ID from the route model binding

        return [
            // Name can be updated, but must remain unique, ignoring the current field itself
            "name" => [
                "required",
                "string",
                Rule::unique("custom_fields", "name")->ignore($customFieldId),
                "regex:/^[a-z0-9_]+$/",
                "max:255",
            ],
            "label" => "required|string|max:255",
            // Type might be restricted from changing if values exist, depending on requirements.
            // For now, allow changing but ensure it's a valid type.
            "type" => "required|string|in:text,date,number,textarea,email,phone",
            "is_filterable" => "nullable|boolean",
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
            "name.regex" => "The name must only contain lowercase letters, numbers, and underscores.",
            "name.unique" => "This technical name is already in use.",
            "type.in" => "Please select a valid field type.",
        ];
    }
}
