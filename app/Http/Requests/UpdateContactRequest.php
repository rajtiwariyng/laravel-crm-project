<?php

namespace App\Http\Requests;

use App\Models\CustomField;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactRequest extends FormRequest
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
        $contactId = $this->route("contact")->id; // Get contact ID from route model binding

        $rules = [
            "name" => "required|string|max:255",
            // Email must be unique, ignoring the current contact
            "email" => [
                "nullable",
                "email",
                "max:255",
                Rule::unique("contacts", "email")->ignore($contactId),
            ],
            "phone" => "nullable|string|max:50",
            "gender" => ["nullable", "string", Rule::in(["male", "female", "other"])],
            // Allow updating files, but make them nullable
            "profile_image" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048",
            "additional_file" => "nullable|file|mimes:pdf,doc,docx,txt|max:5120",
            "custom_fields" => "nullable|array",
        ];

        // Dynamically add rules for submitted custom fields
        if ($this->input("custom_fields") && is_array($this->input("custom_fields"))) {
            $customFields = CustomField::whereIn("id", array_keys($this->input("custom_fields")))->get();

            foreach ($customFields as $field) {
                $fieldRules = ["nullable"];
                switch ($field->type) {
                    case "text":
                    case "textarea":
                        $fieldRules[] = "string";
                        break;
                    case "number":
                        $fieldRules[] = "numeric";
                        break;
                    case "date":
                        $fieldRules[] = "date";
                        break;
                    case "email":
                        $fieldRules[] = "email";
                        break;
                    case "phone":
                        $fieldRules[] = "string";
                        $fieldRules[] = "max:50";
                        break;
                }
                $rules["custom_fields." . $field->id] = $fieldRules;
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
            "gender.in" => "Please select a valid gender.",
            "profile_image.max" => "The profile image may not be greater than 2MB.",
            "additional_file.max" => "The additional file may not be greater than 5MB.",
        ];

        // Add dynamic messages if needed
        if ($this->input("custom_fields") && is_array($this->input("custom_fields"))) {
             $customFields = CustomField::whereIn("id", array_keys($this->input("custom_fields")))->get();
             foreach ($customFields as $field) {
                 $messages["custom_fields." . $field->id . ".string"] = "The " . $field->label . " must be text.";
                 $messages["custom_fields." . $field->id . ".numeric"] = "The " . $field->label . " must be a number.";
                 $messages["custom_fields." . $field->id . ".date"] = "The " . $field->label . " must be a valid date.";
                 $messages["custom_fields." . $field->id . ".email"] = "The " . $field->label . " must be a valid email address.";
             }
        }

        return $messages;
    }
}
