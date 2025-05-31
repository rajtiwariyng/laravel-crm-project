## Controllers and Routes

This document outlines the primary controllers and associated web routes for the Laravel CRM application.

### 1. Custom Field Management (Admin Feature)

**Controller:** `app/Http/Controllers/Admin/CustomFieldController.php`

- `index()`: Display a list of existing custom fields.
- `create()`: Show the form to create a new custom field definition.
- `store()`: Validate and save the new custom field definition.
- `edit(CustomField $customField)`: Show the form to edit an existing custom field.
- `update(Request $request, CustomField $customField)`: Validate and update the custom field definition.
- `destroy(CustomField $customField)`: Delete a custom field definition (consider implications if values exist).

**Routes:** `routes/web.php` (potentially grouped under an admin middleware)

```php
use App\Http\Controllers\Admin\CustomFieldController;

Route::middleware([/* 'auth', 'isAdmin' */])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('custom-fields', CustomFieldController::class);
});
```

### 2. Contact Management

**Controller:** `app/Http/Controllers/ContactController.php`

- `index()`: Display the list of contacts (paginated), potentially with filtering UI. This method will also fetch filterable `CustomField` definitions.
- `fetchContacts(Request $request)`: **(AJAX)** Fetch contacts based on filters (name, email, gender, custom fields). Returns JSON.
- `create()`: Show the form to create a new contact. Fetch available `CustomField` definitions to build the form dynamically.
- `store(Request $request)`: **(AJAX)** Validate and save the new contact and its custom field values. Returns JSON response (success/error).
- `show(Contact $contact)`: Display details of a single contact (optional, could be handled via modal).
- `edit(Contact $contact)`: **(AJAX/Modal)** Fetch contact data including custom fields to populate an edit form (likely in a modal). Returns JSON.
- `update(Request $request, Contact $contact)`: **(AJAX)** Validate and update the contact and its custom field values. Returns JSON response.
- `destroy(Contact $contact)`: **(AJAX)** Mark the contact as inactive/deleted (soft delete or status change). Returns JSON response.

**Routes:** `routes/web.php`

```php
use App\Http\Controllers\ContactController;

Route::get('/', [ContactController::class, 'index'])->name('contacts.index');
Route::get('/contacts/fetch', [ContactController::class, 'fetchContacts'])->name('contacts.fetch'); // AJAX route for listing/filtering
Route::get('/contacts/create', [ContactController::class, 'create'])->name('contacts.create'); // Might not be needed if using modal
Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store'); // AJAX store
Route::get('/contacts/{contact}/edit', [ContactController::class, 'edit'])->name('contacts.edit'); // AJAX fetch for edit modal
Route::put('/contacts/{contact}', [ContactController::class, 'update'])->name('contacts.update'); // AJAX update
Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy'); // AJAX delete
// Route::get('/contacts/{contact}', [ContactController::class, 'show'])->name('contacts.show'); // Optional show route
```

### 3. Contact Merging

**Controller:** `app/Http/Controllers/MergeController.php` (or could be part of `ContactController`)

- `prepareMerge(Request $request)`: **(AJAX)** Takes two contact IDs. Fetches data for both contacts, highlighting potential conflicts, especially in custom fields. Returns JSON data to populate the merge confirmation modal.
- `executeMerge(Request $request)`: **(AJAX)** Takes the master contact ID, secondary contact ID, and potentially user choices for conflict resolution. Performs the merge logic (update master, update secondary status, save history). Returns JSON response.

**Routes:** `routes/web.php`

```php
use App\Http\Controllers\MergeController;

Route::post('/contacts/merge/prepare', [MergeController::class, 'prepareMerge'])->name('contacts.merge.prepare'); // AJAX
Route::post('/contacts/merge/execute', [MergeController::class, 'executeMerge'])->name('contacts.merge.execute'); // AJAX
```

**Middleware:** Apply appropriate middleware (e.g., `auth`) to routes as needed.

**API Routes:** If a separate API is needed, similar routes would be defined in `routes/api.php` using Sanctum or Passport for authentication.

