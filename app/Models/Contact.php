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

    /**
     * Get the custom field values for the contact.
     */
    public function customFieldValues(): HasMany
    {
        return $this->hasMany(ContactCustomFieldValue::class);
    }

    /**
     * Get the master contact that this contact was merged into (if applicable).
     */
    public function masterContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'merged_into_contact_id');
    }

    /**
     * Get the contacts that were merged into this contact (if it's a master).
     */
    public function mergedContacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'merged_into_contact_id');
    }

    /**
     * Scope a query to only include active contacts.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Accessor to get all custom fields with values in a structured way.
     * Loads relation if not already loaded.
     */
    public function getCustomFieldsAttribute()
    {
        if (! $this->relationLoaded('customFieldValues')) {
            $this->load('customFieldValues.customField');
        }

        $customFields = [];
        foreach ($this->customFieldValues as $value) {
            if ($value->customField) { // Ensure custom field exists
                 $customFields[$value->customField->name] = [
                    'label' => $value->customField->label,
                    'value' => $value->value,
                    'type' => $value->customField->type,
                    'id' => $value->custom_field_id // Include ID for form binding
                ];
            }
        }
        return $customFields;
    }
}
