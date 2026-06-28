<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'properties';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'id',
        'condominium_id',
        'tower_id',
        'property_type_id',
        'property_status_id',
        'floor',
        'unit_number',
        'area_m2',
        'coefficient',
        'bedrooms',
        'bathrooms',
        'has_parking',
        'parking_lot',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'floor' => 'integer',
            'area_m2' => 'decimal:2',
            'coefficient' => 'decimal:6',
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'has_parking' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Condominium, $this>
     */
    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class, 'condominium_id');
    }

    /**
     * @return BelongsTo<Tower, $this>
     */
    public function tower(): BelongsTo
    {
        return $this->belongsTo(Tower::class, 'tower_id');
    }

    /**
     * @return BelongsTo<PropertyType, $this>
     */
    public function propertyType(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class, 'property_type_id');
    }

    /**
     * @return BelongsTo<PropertyStatus, $this>
     */
    public function propertyStatus(): BelongsTo
    {
        return $this->belongsTo(PropertyStatus::class, 'property_status_id');
    }

    /**
     * @return HasMany<PropertyStatusLog, $this>
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(PropertyStatusLog::class, 'property_id');
    }

    /**
     * @return HasMany<PropertyDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(PropertyDocument::class, 'property_id');
    }
}
