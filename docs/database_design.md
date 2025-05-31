## Database Design for Laravel CRM

This document outlines the proposed database schema for the CRM features, focusing on extensibility for custom fields and tracking merged contacts.

### 1. Contacts Table (`contacts`)

This table stores the core information for each contact.

- `id` (Primary Key, BigInt, Unsigned, Auto Increment)
- `name` (String)
- `email` (String, Unique - consider making it nullable or handling multiple emails differently if required, though the spec implies one primary email)
- `phone` (String, Nullable)
- `gender` (Enum/String - e.g., 'male', 'female', 'other', Nullable)
- `profile_image_path` (String, Nullable) - Stores the path to the uploaded profile image.
- `additional_file_path` (String, Nullable) - Stores the path to the uploaded additional file.
- `merged_into_contact_id` (Foreign Key, BigInt, Unsigned, Nullable) - References `contacts.id`. If not NULL, this contact has been merged into the specified master contact.
- `status` (Enum/String - e.g., 'active', 'inactive', 'merged') - Default 'active'. Used to logically delete or mark merged contacts.
- `created_at` (Timestamp)
- `updated_at` (Timestamp)

**Considerations:**
*   **Multiple Emails/Phones:** The spec mentions adding secondary emails/phones during merge. This basic schema assumes one primary. To handle multiple, separate `contact_emails` and `contact_phones` tables (One-to-Many) would be better.
*   **Indexing:** Add indexes to `email`, `name`, and `merged_into_contact_id` for performance.

### 2. Custom Fields Definition Table (`custom_fields`)

This table defines the available custom fields that administrators can create.

- `id` (Primary Key, BigInt, Unsigned, Auto Increment)
- `name` (String, Unique) - The machine-readable name (e.g., 'birthday', 'company_name').
- `label` (String) - The human-readable label (e.g., 'Birthday', 'Company Name').
- `type` (Enum/String) - The data type (e.g., 'text', 'date', 'number', 'textarea'). This helps in rendering the correct input field and validation.
- `is_filterable` (Boolean, Default: false) - Whether this field can be used in filtering.
- `created_at` (Timestamp)
- `updated_at` (Timestamp)

### 3. Custom Field Values Table (`contact_custom_field_values`)

This table stores the actual values of custom fields for each contact (Entity-Attribute-Value approach).

- `id` (Primary Key, BigInt, Unsigned, Auto Increment)
- `contact_id` (Foreign Key, BigInt, Unsigned) - References `contacts.id`.
- `custom_field_id` (Foreign Key, BigInt, Unsigned) - References `custom_fields.id`.
- `value` (Text) - Stores the value of the custom field. Data type consistency should be handled in the application layer based on `custom_fields.type`.
- `created_at` (Timestamp)
- `updated_at` (Timestamp)

**Constraints:** Add a unique constraint on (`contact_id`, `custom_field_id`) to ensure a contact has only one value per custom field.
**Indexing:** Add indexes on `contact_id` and `custom_field_id`.

**Alternative (JSON Column):**
Instead of `custom_fields` and `contact_custom_field_values` tables, a `custom_attributes` JSON column could be added to the `contacts` table. 
*   **Pros:** Simpler schema initially, potentially easier to retrieve all data for one contact.
*   **Cons:** Harder to query/filter across contacts based on specific custom field values (though modern databases have improved JSON querying), potential for unstructured data, managing field definitions (like type, label) needs to be handled separately (maybe another table or config).
*   The EAV approach is generally preferred for structured querying and filtering requirements as specified in the task.

### 4. Merge History Table (`merge_history`) (Optional but Recommended)

To provide a clear audit trail of merges.

- `id` (Primary Key, BigInt, Unsigned, Auto Increment)
- `master_contact_id` (Foreign Key, BigInt, Unsigned) - References `contacts.id`.
- `merged_contact_id` (Foreign Key, BigInt, Unsigned) - References `contacts.id` (the contact that was merged *into* the master).
- `merged_data_snapshot` (JSON/Text, Nullable) - A snapshot of the data from `merged_contact_id` before the merge, for historical reference.
- `merge_details` (JSON/Text, Nullable) - Notes on the merge, e.g., which fields were overwritten/appended.
- `merged_by_user_id` (Foreign Key, Nullable) - References the user who performed the merge.
- `created_at` (Timestamp)

### Relationships Summary:

- `Contact` hasMany `ContactCustomFieldValue`
- `CustomField` hasMany `ContactCustomFieldValue`
- `ContactCustomFieldValue` belongsTo `Contact`
- `ContactCustomFieldValue` belongsTo `CustomField`
- `Contact` (merged) belongsTo `Contact` (master) via `merged_into_contact_id`
- `MergeHistory` belongsTo `Contact` (master)
- `MergeHistory` belongsTo `Contact` (merged)

