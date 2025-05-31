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

    /**
     * Get the contact that owns the custom field value.
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get the custom field definition associated with the value.
     */
    public function customField(): BelongsTo
    {
        return $this->belongsTo(CustomField::class);
    }
}
