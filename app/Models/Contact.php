<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'contacts';

    protected $fillable = [
        'user_id',
        'document_type',
        'document_number',
        'full_name',
        'email',
        'phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'notes',
        'organization_id',
    ];

    protected $casts = [
        'user_id' => 'string',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<PropertyOccupant, $this> */
    public function occupants(): HasMany
    {
        return $this->hasMany(PropertyOccupant::class);
    }
}
