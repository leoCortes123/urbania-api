<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PropertyStatusLogFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyStatusLog extends Model
{
    /** @use HasFactory<PropertyStatusLogFactory> */
    use HasFactory, HasUuids;

    protected $table = 'property_status_log';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'id',
        'property_id',
        'from_status_id',
        'to_status_id',
        'changed_by_user_id',
        'reason',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    /**
     * @return BelongsTo<PropertyStatus, $this>
     */
    public function fromStatus(): BelongsTo
    {
        return $this->belongsTo(PropertyStatus::class, 'from_status_id');
    }

    /**
     * @return BelongsTo<PropertyStatus, $this>
     */
    public function toStatus(): BelongsTo
    {
        return $this->belongsTo(PropertyStatus::class, 'to_status_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
