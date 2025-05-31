<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactCustomFieldValue;
use App\Models\MergeHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class MergeController extends Controller
{
    /**
     * Prepare data for the merge confirmation modal (AJAX).
     */
    public function prepareMerge(Request $request)
    {
        $request->validate([
            "contact_ids" => "required|array|min:2|max:2",
            "contact_ids.*" => "required|integer|exists:contacts,id",
        ]);

        $contactId1 = $request->input("contact_ids")[0];
        $contactId2 = $request->input("contact_ids")[1];

        try {
            $contact1 = Contact::with("customFieldValues.customField")->findOrFail($contactId1);
            $contact2 = Contact::with("customFieldValues.customField")->findOrFail($contactId2);

            // Basic check: Ensure contacts are active and not already merged
            if ($contact1->status !== "active" || $contact2->status !== "active") {
                return response()->json(["success" => false, "message" => "One or both contacts are not active and cannot be merged."], 400);
            }

            // Prepare data structure for comparison in the modal
            $data = [
                "contact1" => $this->formatContactData($contact1),
                "contact2" => $this->formatContactData($contact2),
            ];

            return response()->json(["success" => true, "data" => $data]);
        } catch (\Exception $e) {
            Log::error("Error preparing merge for contacts {$contactId1}, {$contactId2}: " . $e->getMessage());
            return response()->json(["success" => false, "message" => "Failed to load contact data for merge preparation."], 500);
        }
    }

    /**
     * Execute the contact merge (AJAX).
     */
    public function executeMerge(Request $request)
    {
        $validated = $request->validate([
            "master_contact_id" => "required|integer|exists:contacts,id",
            "secondary_contact_id" => "required|integer|exists:contacts,id|different:master_contact_id",
            // Add validation for conflict resolution choices if implemented
            // e.g., "conflicts.custom_field_id" => "required|in:master,secondary"
        ]);

        $masterId = $validated["master_contact_id"];
        $secondaryId = $validated["secondary_contact_id"];

        DB::beginTransaction();
        try {
            $masterContact = Contact::with("customFieldValues.customField")->findOrFail($masterId);
            $secondaryContact = Contact::with("customFieldValues.customField")->findOrFail($secondaryId);

            // Double-check status before proceeding
            if ($masterContact->status !== "active" || $secondaryContact->status !== "active") {
                DB::rollBack();
                return response()->json(["success" => false, "message" => "One or both contacts are no longer active."], 400);
            }

            // --- Merge Logic --- 

            // 1. Snapshot secondary data (before changes)
            $secondarySnapshot = $this->formatContactData($secondaryContact);

            // 2. Merge Custom Fields
            $masterCustomValues = $masterContact->customFieldValues->keyBy("custom_field_id");
            foreach ($secondaryContact->customFieldValues as $secondaryValue) {
                // If master doesn't have this field, or if master's value is empty, add/update with secondary's value
                if (!isset($masterCustomValues[$secondaryValue->custom_field_id]) || empty($masterCustomValues[$secondaryValue->custom_field_id]->value)) {
                    ContactCustomFieldValue::updateOrCreate(
                        [
                            "contact_id" => $masterContact->id,
                            "custom_field_id" => $secondaryValue->custom_field_id,
                        ],
                        ["value" => $secondaryValue->value]
                    );
                } else {
                    // Conflict: Both have a value. Policy: Keep Master's (default).
                    // If a different policy (e.g., user choice, append) is needed, implement here based on request input.
                    // For now, we do nothing, master's value is kept.
                }
            }

            // 3. Merge Standard Fields (Example: Add secondary phone if master has none)
            if (empty($masterContact->phone) && !empty($secondaryContact->phone)) {
                $masterContact->phone = $secondaryContact->phone;
            }
            // Add similar logic for other fields like email if needed (e.g., store secondary email in a notes field or separate table)

            // 4. Update Master Contact (if changes were made)
            $masterContact->save(); // Save any changes to standard fields

            // 5. Update Secondary Contact Status
            $secondaryContact->status = "merged";
            $secondaryContact->merged_into_contact_id = $masterContact->id;
            $secondaryContact->save();

            // 6. Create Merge History Record
            MergeHistory::create([
                "master_contact_id" => $masterContact->id,
                "merged_contact_id" => $secondaryContact->id,
                "merged_data_snapshot" => $secondarySnapshot, // Store the pre-merge state
                "merge_details" => ["policy" => "Keep master value on conflict"], // Add details about choices made
                "merged_by_user_id" => auth()->id(), // Optional: Record user if auth is implemented
            ]);

            DB::commit();

            // Reload master contact with potentially updated values
            $masterContact->load("customFieldValues.customField");

            return response()->json([
                "success" => true,
                "message" => "Contacts merged successfully.",
                "masterContact" => $masterContact // Return updated master contact data
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(["success" => false, "message" => "Validation failed.", "errors" => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error executing merge for master {$masterId} and secondary {$secondaryId}: " . $e->getMessage());
            return response()->json(["success" => false, "message" => "An unexpected error occurred during the merge process. " . $e->getMessage()], 500);
        }
    }

    /**
     * Helper function to format contact data for comparison and snapshotting.
     */
    private function formatContactData(Contact $contact): array
    {
        $data = $contact->only(["id", "name", "email", "phone", "gender", "profile_image_path", "additional_file_path", "status", "created_at", "updated_at"]);
        $data["custom_fields"] = $contact->customFieldValues->mapWithKeys(function ($value) {
            return [$value->customField->name => [
                "label" => $value->customField->label,
                "value" => $value->value,
                "id" => $value->custom_field_id
            ]];
        })->toArray();
        return $data;
    }
}
