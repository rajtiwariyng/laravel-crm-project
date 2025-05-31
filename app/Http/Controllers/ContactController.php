<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\CustomField;
use App\Models\ContactCustomFieldValue;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB; // For transactions
use Illuminate\Support\Facades\Log; // For logging errors

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load filterable custom fields for the filter UI
        $filterableCustomFields = CustomField::where("is_filterable", true)->orderBy("label")->get();
        return view("contacts.index", compact("filterableCustomFields"));
    }

    /**
     * Fetch contacts based on filters via AJAX.
     */
    public function fetchContacts(Request $request)
    {
        $query = Contact::query()->where("status", "active")->with("customFieldValues.customField");

        // Standard Filters
        if ($request->filled("name")) {
            $query->where("name", "like", "%" . $request->input("name") . "%");
        }
        if ($request->filled("email")) {
            $query->where("email", "like", "%" . $request->input("email") . "%");
        }
        if ($request->filled("gender")) {
            $query->where("gender", $request->input("gender"));
        }

        // Custom Field Filters
        if ($request->filled("custom_filters") && is_array($request->input("custom_filters"))) {
            foreach ($request->input("custom_filters") as $fieldId => $value) {
                if (!empty($value)) {
                    $query->whereHas("customFieldValues", function ($q) use ($fieldId, $value) {
                        $q->where("custom_field_id", $fieldId)
                            ->where("value", "like", "%" . $value . "%"); // Simple LIKE search for demo
                    });
                }
            }
        }

        $contacts = $query->orderBy("name")->paginate(15); // Paginate results

        // Return JSON response suitable for frontend rendering
        return response()->json([
            "success" => true,
            "contacts" => $contacts->items(),
            "pagination" => [
                "currentPage" => $contacts->currentPage(),
                "lastPage" => $contacts->lastPage(),
                "perPage" => $contacts->perPage(),
                "total" => $contacts->total(),
                "nextPageUrl" => $contacts->nextPageUrl(),
                "prevPageUrl" => $contacts->previousPageUrl(),
            ]
        ]);
    }


    /**
     * Show the form for creating a new resource.
     * (Often handled by modal, but can return data needed for form)
     */
    public function create()
    {
        $customFields = CustomField::orderBy("label")->get();
        // If using a dedicated create view:
        // return view("contacts.create", compact("customFields"));
        // If providing data for a modal:
        return response()->json(["success" => true, "customFields" => $customFields]);
    }

    /**
     * Store a newly created resource in storage (AJAX).
     */
    public function store(StoreContactRequest $request)
    {
        $validatedData = $request->validated();
        $contactData = collect($validatedData)->except(["profile_image", "additional_file", "custom_fields"])->toArray();

        DB::beginTransaction();
        try {
            // Handle File Uploads
            if ($request->hasFile("profile_image")) {
                $contactData["profile_image_path"] = $request->file("profile_image")->store("profile_images", "public");
            }
            if ($request->hasFile("additional_file")) {
                $contactData["additional_file_path"] = $request->file("additional_file")->store("additional_files", "public");
            }

            // Create Contact
            $contact = Contact::create($contactData);

            // Handle Custom Fields
            if (!empty($validatedData["custom_fields"])) {
                foreach ($validatedData["custom_fields"] as $fieldId => $value) {
                    if (!is_null($value) && $value !== "") { // Only save non-empty values
                        ContactCustomFieldValue::create([
                            "contact_id" => $contact->id,
                            "custom_field_id" => $fieldId,
                            "value" => $value,
                        ]);
                    }
                }
            }

            DB::commit();
            $contact->load("customFieldValues.customField"); // Load relations for response
            return response()->json(["success" => true, "message" => "Contact created successfully.", "contact" => $contact]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error creating contact: " . $e->getMessage());
            // Clean up uploaded files if transaction fails
            if (!empty($contactData["profile_image_path"])) Storage::disk("public")->delete($contactData["profile_image_path"]);
            if (!empty($contactData["additional_file_path"])) Storage::disk("public")->delete($contactData["additional_file_path"]);

            return response()->json(["success" => false, "message" => "Failed to create contact. " . $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource (AJAX/Modal).
     */
    public function edit(Contact $contact)
    {
        $contact->load("customFieldValues.customField"); // Eager load values and field definitions
        $allCustomFields = CustomField::orderBy("label")->get(); // Get all possible custom fields

        // Prepare custom fields data for the form
        $contactCustomFields = $contact->customFieldValues->keyBy("custom_field_id");

        return response()->json([
            "success" => true,
            "contact" => $contact,
            "contactCustomFields" => $contactCustomFields, // Values the contact currently has
            "allCustomFields" => $allCustomFields // All available fields for form building
        ]);
    }

    /**
     * Update the specified resource in storage (AJAX).
     */
    public function update(UpdateContactRequest $request, Contact $contact)
    {
        $validatedData = $request->validated();
        $contactData = collect($validatedData)->except(["profile_image", "additional_file", "custom_fields"])->toArray();

        DB::beginTransaction();
        try {
            // Handle File Uploads (Update/Replace)
            if ($request->hasFile("profile_image")) {
                // Delete old file if exists
                if ($contact->profile_image_path) {
                    Storage::disk("public")->delete($contact->profile_image_path);
                }
                $contactData["profile_image_path"] = $request->file("profile_image")->store("profile_images", "public");
            }
            if ($request->hasFile("additional_file")) {
                if ($contact->additional_file_path) {
                    Storage::disk("public")->delete($contact->additional_file_path);
                }
                $contactData["additional_file_path"] = $request->file("additional_file")->store("additional_files", "public");
            }

            // Update Contact
            $contact->update($contactData);

            // Handle Custom Fields (Update/Create/Delete)
            $submittedCustomFields = $validatedData["custom_fields"] ?? [];
            $existingCustomFieldIds = $contact->customFieldValues->pluck("custom_field_id")->toArray();
            $submittedCustomFieldIds = array_keys($submittedCustomFields);

            // Update or Create submitted values
            foreach ($submittedCustomFields as $fieldId => $value) {
                if (!is_null($value) && $value !== "") {
                    ContactCustomFieldValue::updateOrCreate(
                        ["contact_id" => $contact->id, "custom_field_id" => $fieldId],
                        ["value" => $value]
                    );
                } else {
                    // If submitted value is empty, delete existing entry
                    if (in_array($fieldId, $existingCustomFieldIds)) {
                        $contact->customFieldValues()->where("custom_field_id", $fieldId)->delete();
                    }
                }
            }

            // Delete fields that were previously set but not included in this submission (and not empty)
            $fieldsToDelete = array_diff($existingCustomFieldIds, $submittedCustomFieldIds);
            if (!empty($fieldsToDelete)) {
                $contact->customFieldValues()->whereIn("custom_field_id", $fieldsToDelete)->delete();
            }

            DB::commit();
            $contact->load("customFieldValues.customField"); // Reload relations
            return response()->json(["success" => true, "message" => "Contact updated successfully.", "contact" => $contact]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating contact {$contact->id}: " . $e->getMessage());
            // Note: File cleanup on update failure is more complex, skipping for brevity
            return response()->json(["success" => false, "message" => "Failed to update contact. " . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage (Logically delete - AJAX).
     */
    public function destroy(Contact $contact)
    {
        try {
            // Check if contact is already merged - prevent deletion?
            if ($contact->status === "merged") {
                return response()->json(["success" => false, "message" => "Cannot delete a contact that has been merged."], 400);
            }

            $contact->status = "inactive"; // Or use SoftDeletes trait
            $contact->save();

            return response()->json(["success" => true, "message" => "Contact deactivated successfully."]);
        } catch (\Exception $e) {
            Log::error("Error deactivating contact {$contact->id}: " . $e->getMessage());
            return response()->json(["success" => false, "message" => "Failed to deactivate contact."], 500);
        }
    }
}
