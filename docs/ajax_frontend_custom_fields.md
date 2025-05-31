## AJAX, Frontend, and Custom Field Handling

This document details the frontend interactions, AJAX implementation, and dynamic handling of custom fields.

### 1. Frontend Framework/Library

- While not specified, using a simple JavaScript approach (Vanilla JS with Fetch API) or a lightweight library like Alpine.js or even Vue.js/React (if complexity warrants it) would be suitable.
- For this outline, we'll assume Vanilla JS or Alpine.js for simplicity, interacting directly with Blade templates initially.
- CSS framework like Bootstrap or Tailwind CSS for styling and layout (modals, forms, etc.).

### 2. AJAX Implementation Strategy

- **Requests:** Use the browser's `fetch` API or a library like Axios to send asynchronous requests to the defined Laravel routes (e.g., `/contacts/fetch`, `/contacts`, `/contacts/{id}`, `/contacts/merge/prepare`).
- **CSRF Protection:** Ensure Laravel's CSRF token is included in AJAX POST/PUT/DELETE requests (e.g., via a meta tag or passed with data).
- **Responses:** Laravel controllers will return JSON responses using `response()->json([...])`.
    - Success: `{ success: true, message: '...', data: {...} }`
    - Error: `{ success: false, message: '...', errors: {...} }` (Validation errors can be included in `errors`)
- **UI Updates:** JavaScript will handle the JSON responses:
    - Display success/error messages (e.g., using toast notifications or dedicated message areas).
    - Update the contact list table dynamically (add, remove, update rows) without page refresh.
    - Populate and clear forms (especially in modals).
    - Handle loading states during AJAX calls.

### 3. Contact List and Filtering (AJAX)

- **Initial Load:** The `ContactController@index` loads the main view with the table structure and filter inputs.
- **Fetching Data:** A JavaScript function (`fetchContacts`) is called on page load and whenever filter inputs change (with debouncing to avoid excessive requests).
- **Request:** Sends a GET request to `/contacts/fetch` with filter parameters (name, email, gender, custom field values) in the query string.
- **Backend:** `ContactController@fetchContacts` queries the `Contact` model, applying `where` clauses based on the request parameters. It joins with `contact_custom_field_values` and `custom_fields` if filtering by custom fields is active. It returns paginated JSON data.
- **Frontend:** JavaScript receives the JSON, clears the existing table body, and dynamically renders the new rows based on the received contact data. Updates pagination controls.

### 4. CRUD Operations (AJAX)

- **Create/Edit Forms:** Forms (likely in modals) are used for creating and editing contacts.
    - **Dynamic Custom Fields:** When rendering the form (either via `ContactController@create` or fetched via `ContactController@edit`), the controller fetches all active `CustomField` definitions.
    - The Blade view iterates over these definitions and renders the appropriate HTML input (`<input type="text">`, `<input type="date">`, `<input type="radio">`, `<textarea>`, etc.) based on the `custom_field->type`. Input names should be structured to be easily parsed on the backend (e.g., `custom_fields[field_id]` or `custom_fields[field_name]`).
- **Store/Update:**
    - **Request:** Form submission is intercepted by JavaScript. Data (including standard fields, file uploads, and custom field values) is collected (using `FormData` for file uploads) and sent via POST (store) or PUT (update) AJAX request to the respective controller action.
    - **Backend Validation:** The `ContactController` uses a Form Request class (e.g., `StoreContactRequest`, `UpdateContactRequest`) for validation. Validation rules for custom fields need to be dynamically generated based on the `type` defined in `custom_fields`.
    - **Backend Logic:** If validation passes, the controller saves/updates the `Contact` model. For custom fields, it iterates through the submitted custom field data, using `updateOrCreate` or similar logic on the `ContactCustomFieldValue` model associated with the contact.
    - **File Uploads:** Handle `profile_image` and `additional_file` uploads using Laravel's `Storage` facade, saving files to a designated disk (e.g., `public` or `s3`) and storing the path in the `contacts` table.
    - **Response:** Returns JSON success/error message.
- **Delete:**
    - **Request:** A click on a delete button triggers a JavaScript confirmation. If confirmed, a DELETE AJAX request is sent to `/contacts/{contact}`.
    - **Backend:** `ContactController@destroy` finds the contact, changes its `status` to 'inactive' or 'deleted' (or performs a soft delete if using that trait), and potentially updates `merged_into_contact_id` if applicable during a merge cleanup.
    - **Response:** Returns JSON success/error message. Frontend removes the row from the table.

### 5. UI/UX Considerations

- **Feedback:** Clear visual feedback for loading states, success messages, and validation errors is crucial.
- **Modals:** Use modals for create/edit forms and the merge confirmation process to avoid disrupting the main contact list view.
- **Dynamic Forms:** Ensure the dynamic rendering of custom fields is seamless and validation messages appear correctly next to the relevant fields.
- **Filtering:** Provide clear indicators of active filters.

