<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MergeHistory extends Model
{
    use HasFactory;

    protected $table = 'merge_history'; // Explicitly define table name if needed

    protected $fillable = [
        'master_contact_id',
        'merged_contact_id',
        'merged_data_snapshot',
        'merge_details',
        'merged_by_user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'merged_data_snapshot' => 'array', // Cast JSON column to array
        'merge_details' => 'array',        // Cast JSON column to array
    ];

    /**
     * Get the master contact associated with the merge history.
     */
    public function masterContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'master_contact_id');
    }

    /**
     * Get the contact that was merged (the secondary contact).
     */
    public function mergedContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'merged_contact_id');
    }

    /**
     * Get the user who performed the merge (optional).
     */
    // public function user(): BelongsTo
    // {
    //     // Assuming you have a User model
    //     return $this->belongsTo(User::class, 'merged_by_user_id');
    // }
}
