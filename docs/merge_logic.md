## Contact Merging Logic

This document details the proposed logic for the contact merging feature, handled primarily by the `MergeController`.

### 1. Initiation

- **UI:** The contact list view should provide a mechanism to select two contacts for merging (e.g., checkboxes and a "Merge Selected" button, or a "Merge" option next to each contact that prompts for the second contact).
- **Trigger:** User action triggers a JavaScript function.
- **Request:** JavaScript sends an AJAX POST request to `contacts.merge.prepare` (`/contacts/merge/prepare`) with the IDs of the two selected contacts.

### 2. Preparation and Confirmation (`MergeController@prepareMerge`)

- **Backend:**
    - Fetches both `Contact` models (`contact1`, `contact2`) with their associated `customFieldValues` and `customField` definitions.
    - Prepares data for the confirmation modal, including standard fields and custom fields for both contacts.
    - Identifies potential data conflicts (e.g., different values for the same custom field).
- **Response:** Returns JSON data containing:
    - Full data for both contacts.
    - A clear indication of which fields match and which conflict.
    - Data structured to allow the user to select one contact as the "master" record.
- **Frontend (Confirmation Modal):**
    - Displays the data for both contacts side-by-side.
    - Highlights conflicting fields.
    - Requires the user to explicitly select which contact will be the **master** (the one that remains active).
    - **Conflict Resolution (Custom Fields):** For custom fields where both contacts have a value but they differ, the UI should present a choice (based on the defined policy):
        - **Policy 1 (Default - Keep Master):** Automatically select the master contact's value. Display the secondary value for informational purposes.
        - **Policy 2 (Allow Choice):** Present radio buttons or similar controls allowing the user to choose which value to keep for each conflicting custom field.
        - **Policy 3 (Append - Use with caution):** For text-based fields, potentially offer to append values (e.g., "Value A / Value B"). This needs careful consideration regarding data type and length.
        *(The default policy should be clearly stated, e.g., "Master's value will be kept by default for conflicting fields.")*
    - Includes a "Confirm Merge" button and a "Cancel" button.

### 3. Execution (`MergeController@executeMerge`)

- **Trigger:** User clicks "Confirm Merge" in the modal.
- **Request:** JavaScript sends an AJAX POST request to `contacts.merge.execute` (`/contacts/merge/execute`) including:
    - `master_contact_id`
    - `secondary_contact_id`
    - (If Policy 2 is used) User choices for resolving custom field conflicts.
- **Backend Logic (within a Database Transaction):**
    1.  **Fetch Models:** Retrieve the master and secondary `Contact` models again to ensure fresh data.
    2.  **Prepare Data:** Create an array or object (`merged_data`) to hold the final data for the master contact.
    3.  **Standard Fields:** Iterate through standard fields (name, phone, etc.). Generally, keep the master's data. For fields like email/phone, if the secondary contact has a value and the master doesn't, *or* if a multi-value strategy (separate tables) is implemented, add the secondary contact's value(s).
    4.  **Custom Fields:**
        - Fetch all `ContactCustomFieldValue` records for both contacts, keyed by `custom_field_id`.
        - Iterate through the secondary contact's custom field values:
            - If the master contact does *not* have a value for this `custom_field_id`, create a new `ContactCustomFieldValue` for the master contact with the secondary's value.
            - If the master contact *does* have a value:
                - **Conflict:** Apply the chosen resolution policy (keep master's, use user's choice, append). Update the master's `ContactCustomFieldValue` if necessary.
    5.  **File Handling:** Decide on a policy for `profile_image_path` and `additional_file_path`. Keep master's? Keep both (rename secondary's file)? This needs clarification.
A simple approach is to keep the master's files.
    6.  **Update Master Contact:** Save the master `Contact` model with any updated standard field data. The custom field values are saved via their own model (`updateOrCreate` is useful here).
    7.  **Update Secondary Contact:**
        - Set `status` to 'merged'.
        - Set `merged_into_contact_id` to the `master_contact_id`.
        - Save the secondary `Contact` model.
    8.  **Create Merge History (Optional but Recommended):**
        - Create a snapshot of the secondary contact's data *before* the merge.
        - Record details of the merge (e.g., which fields were merged/overwritten, conflict resolutions).
        - Create a `MergeHistory` record with `master_contact_id`, `merged_contact_id`, the snapshot, details, and `merged_by_user_id` (if auth is used).
    9.  **Commit Transaction.**
- **Response:** Return JSON success message `{ success: true, message: 'Contacts merged successfully.' }` or error details `{ success: false, message: 'Merge failed.', errors: {...} }`.
- **Frontend:**
    - Display success/error message.
    - On success, remove the secondary contact's row from the main list (as it's now 'merged').
    - Update the master contact's row if any visible data changed.

### 4. Data Integrity and Extensibility

- **No Data Loss:** By marking the secondary contact as 'merged' and linking it to the master (`merged_into_contact_id`), and optionally storing a snapshot in `merge_history`, no data is permanently deleted from the database itself.
- **Tracking:** The `status` and `merged_into_contact_id` fields allow easy identification and exclusion of merged contacts from standard views/queries.
- **Extensibility:** The logic iterates through defined custom fields, making it automatically adaptable when new custom fields are added via the admin interface.

### 5. Note on Multiple Emails/Phones

The PDF mentions merging emails/phones. The described EAV approach for custom fields *could* be adapted for this, but a more standard relational approach using separate `contact_emails` and `contact_phones` tables (One-to-Many with `contacts`) is generally cleaner for these specific, common multi-value fields. The merge logic would then involve adding unique email/phone entries from the secondary contact to the master contact's related records.
