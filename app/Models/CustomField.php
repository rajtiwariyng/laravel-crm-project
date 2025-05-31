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

    /**
     * Get the values associated with this custom field definition across all contacts.
     */
    public function values(): HasMany
    {
        return $this->hasMany(ContactCustomFieldValue::class);
    }
}
