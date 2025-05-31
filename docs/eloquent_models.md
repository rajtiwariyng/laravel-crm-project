## Laravel Eloquent Models

This document outlines the Eloquent models corresponding to the database schema.

### 1. Contact Model (`app/Models/Contact.php`)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'profile_image_path',
        'additional_file_path',
        'status',
        'merged_into_contact_id',
    ];

    // Relationship: A contact has many custom field values
    public function customFieldValues(): HasMany
    {
        return $this->hasMany(ContactCustomFieldValue::class);
    }

    // Relationship: A merged contact belongs to a master contact
    public function masterContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'merged_into_contact_id');
    }

    // Relationship: A master contact can have many contacts merged into it
    public function mergedContacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'merged_into_contact_id');
    }

    // Scope to easily query active contacts
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Accessor to get all custom fields with values in a structured way (optional)
    public function getCustomFieldsAttribute()
    {
        $customFields = [];
        foreach ($this->customFieldValues as $value) {
            $customFields[$value->customField->name] = [
                'label' => $value->customField->label,
                'value' => $value->value,
                'type' => $value->customField->type,
            ];
        }
        return $customFields;
    }
}
```

### 2. CustomField Model (`app/Models/CustomField.php`)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomField extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
        'type',
        'is_filterable',
    ];

    // Relationship: A custom field definition can have many values across contacts
    public function values(): HasMany
    {
        return $this->hasMany(ContactCustomFieldValue::class);
    }
}
```

### 3. ContactCustomFieldValue Model (`app/Models/ContactCustomFieldValue.php`)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactCustomFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'custom_field_id',
        'value',
    ];

    // Relationship: A value belongs to a specific contact
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    // Relationship: A value belongs to a specific custom field definition
    public function customField(): BelongsTo
    {
        return $this->belongsTo(CustomField::class);
    }
}
```

### 4. MergeHistory Model (`app/Models/MergeHistory.php`) (Optional)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MergeHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'master_contact_id',
        'merged_contact_id',
        'merged_data_snapshot',
        'merge_details',
        'merged_by_user_id',
    ];

    protected $casts = [
        'merged_data_snapshot' => 'array', // Cast JSON to array
        'merge_details' => 'array',        // Cast JSON to array
    ];

    // Relationship: History belongs to the master contact
    public function masterContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'master_contact_id');
    }

    // Relationship: History belongs to the merged contact
    public function mergedContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'merged_contact_id');
    }

    // Relationship: History belongs to the user who performed the merge (if using Auth)
    // public function user(): BelongsTo
    // {
    //     return $this->belongsTo(User::class, 'merged_by_user_id');
    // }
}
```

These models establish the necessary relationships and provide a foundation for interacting with the database tables.
