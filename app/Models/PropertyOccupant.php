<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PropertyOccupantFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyOccupant extends Model
{
    /** @use HasFactory<PropertyOccupantFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'property_occupants';

    protected $fillable = [
        'property_id',
        'contact_id',
        'occupant_type_id',
        'is_primary',
        'move_in_date',
        'move_out_date',
        'is_active',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'move_in_date' => 'date',
        'move_out_date' => 'date',
    ];

    /** @return BelongsTo<Contact, $this> */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /** @return BelongsTo<OccupantType, $this> */
    public function occupantType(): BelongsTo
    {
        return $this->belongsTo(OccupantType::class);
    }
}
