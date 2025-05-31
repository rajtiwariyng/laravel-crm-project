<?php

namespace App\Http\Requests;

use App\Models\CustomField;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Add authorization logic if needed
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:contacts,email', // Unique email
            'phone' => 'nullable|string|max:50',
            'gender' => ['nullable', 'string', Rule::in(['male', 'female', 'other'])],
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Example validation for image
            'additional_file' => 'nullable|file|mimes:pdf,doc,docx,txt|max:5120', // Example validation for file
            'custom_fields' => 'nullable|array', // Expect custom fields as an array
        ];

        // Dynamically add rules for submitted custom fields
        if ($this->input('custom_fields') && is_array($this->input('custom_fields'))) {
            $customFields = CustomField::whereIn('id', array_keys($this->input('custom_fields')))->get();

            foreach ($customFields as $field) {
                $fieldRules = ['nullable']; // All custom fields are nullable by default
                switch ($field->type) {
                    case 'text':
                    case 'textarea':
                        $fieldRules[] = 'string';
                        break;
                    case 'number':
                        $fieldRules[] = 'numeric';
                        break;
                    case 'date':
                        $fieldRules[] = 'date';
                        break;
                    case 'email':
                        $fieldRules[] = 'email';
                        break;
                    case 'phone':
                        // Add specific phone validation if needed
                        $fieldRules[] = 'string';
                        $fieldRules[] = 'max:50';
                        break;
                }
                // Use dot notation for array validation
                $rules['custom_fields.' . $field->id] = $fieldRules;
            }
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        $messages = [
            'gender.in' => 'Please select a valid gender.',
            'profile_image.max' => 'The profile image may not be greater than 2MB.',
            'additional_file.max' => 'The additional file may not be greater than 5MB.',
        ];

        // Add dynamic messages if needed
        if ($this->input('custom_fields') && is_array($this->input('custom_fields'))) {
             $customFields = CustomField::whereIn('id', array_keys($this->input('custom_fields')))->get();
             foreach ($customFields as $field) {
                 $messages['custom_fields.' . $field->id . '.string'] = 'The ' . $field->label . ' must be text.';
                 $messages['custom_fields.' . $field->id . '.numeric'] = 'The ' . $field->label . ' must be a number.';
                 $messages['custom_fields.' . $field->id . '.date'] = 'The ' . $field->label . ' must be a valid date.';
                 $messages['custom_fields.' . $field->id . '.email'] = 'The ' . $field->label . ' must be a valid email address.';
             }
        }

        return $messages;
    }
}
